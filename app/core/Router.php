<?php
/**
 * Router Class
 * Handles URL routing and controller dispatching
 */
class Router {
    private $routes = [];
    private $basePath = '/attendance-system';
    
    /**
     * Load routes from configuration file
     */
    public function loadRoutes() {
        $routes = require_once __DIR__ . '/../../config/routes.php';
        $this->routes = $routes;
    }
    
    /**
     * Handle the current request
     */
    public function handleRequest() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $routePath = str_replace($this->basePath, '', $uri);
        
        // Default to '/' if empty path
        if (empty($routePath) || $routePath == '/') {
            $routePath = '/';
            
            // If this is the root path, redirect to login
            if (!isset($this->routes[$routePath])) {
                $this->redirect('/login');
                return;
            }
        }
        
        // Check if route exists
        if (isset($this->routes[$routePath])) {
            $route = $this->routes[$routePath];
            
            // Check authentication requirement
            if (isset($route['auth']) && $route['auth'] && !Session::get('user_id')) {
                $this->redirect('/login');
                return;
            }
            
            // Check admin requirement
            if (isset($route['admin']) && $route['admin']) {
                if (!Session::get('admin_id') || Session::get('role') !== 'admin') {
                    $this->redirect('/admin-login');
                    return;
                }
            }
            
            // Dispatch to controller
            $this->dispatchController($route['controller'], $route['action']);
            return;
        }
        
        // 404 Handling
        $this->notFound();
    }
    
    /**
     * Dispatch to controller
     */
    private function dispatchController($controllerName, $actionName) {
        $controllerFile = __DIR__ . '/../controller/' . $controllerName . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            
            $controller = new $controllerName();
            
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            } else {
                $this->notFound("Action '$actionName' not found in controller '$controllerName'");
            }
        } else {
            $this->notFound("Controller file not found: $controllerFile");
        }
    }
    
    /**
     * Redirect to a path
     */
    public function redirect($path) {
        header("Location: {$this->basePath}{$path}");
        exit();
    }
    
    /**
     * Handle 404 Not Found
     */
    private function notFound($message = 'Page not found') {
        http_response_code(404);
        
        // Check if 404 view exists
        $errorView = __DIR__ . '/../view/errors/404.php';
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo "<h1>404 Not Found</h1>";
            echo "<p>$message</p>";
        }
        exit();
    }
}