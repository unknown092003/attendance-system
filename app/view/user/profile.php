<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Management System | Profile</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main profile styles -->
    <link rel="stylesheet" href="/attendance-system/assets/css/profile.css">
    <!-- Additional styles for edit functionality -->
    <style>
        /* EDIT MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.active {
            display: flex;
            opacity: 1;
        }
        
        
        .modal.active .modal-content {
            transform: translateY(0);
        }
        
        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            margin: 0;
            font-size: 1.25rem;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        
        /* FORM STYLES */
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        /* INLINE EDIT STYLES */
        .edit-form {
            display: none;
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .edit-form.active {
            display: block;
        }
        
        .edit-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        /* FIELD-SPECIFIC STYLES */
        .detail-value {
            position: relative;
        }
        
        .edit-detail {
            position: absolute;
            right: 0;
            top: 0;
            background: none;
            border: none;
            color: var(--accent);
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .detail-value:hover .edit-detail {
            opacity: 1;
        }

        /* Status indicator colors */
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-indicator.active {
            background-color: #28a745;
        }
        
        .status-indicator.inactive {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <?php
    // Database connection with error handling
    $host = 'localhost';
    $db   = 'attendance_system';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        // Start session (if not already started)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in (replace with your actual session check)
        if (!isset($_SESSION['user_id'])) {
            die("Please login to view this page");
        }
        
        // Get user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            die("User not found");
        }

    } catch (\PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    // Helper for initials
    function get_initials($name) {
        $words = explode(' ', $name);
        $ini = '';
        foreach ($words as $w) {
            if ($w) $ini .= strtoupper($w[0]);
        }
        return substr($ini, 0, 2);
    }

    // Format date for display
    function format_date($dateString) {
        if (empty($dateString)) return 'Not set';
        return date('F j, Y', strtotime($dateString));
    }
    ?>
    <!-- Main container for the profile page -->
    <div class="container">
        <!-- Profile card containing all user information -->
        <article class="card profile-card">
            <!-- Profile header section with avatar and basic info -->
            <header class="profile-header">
                <!-- User avatar with edit button -->
                <div class="profile-avatar">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="assets\img\ocd.png" alt="Avatar" style="width:100%;height:100%;border-radius:50%;">
                    <?php else: ?>
                        <?= get_initials($user['full_name']) ?>
                    <?php endif; ?>
                    <div class="profile-avatar-edit" title="Edit profile picture">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                
                <!-- User name with edit button -->
                <h1 class="profile-title">
                    <?= htmlspecialchars($user['full_name']) ?>
                    <button class="btn-icon" title="Edit name">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                </h1>
                
                <!-- User role/subtitle -->
                <p class="profile-subtitle"><?= htmlspecialchars($user['role']) ?></p>
                
                <!-- Status indicator -->
                <div class="profile-status">
                    <span class="status-indicator <?= $user['status'] === 'active' ? 'active' : 'inactive' ?>"></span>
                    <span><?= ucfirst($user['status'] ?: 'active') ?> Internship</span>
                </div>
            </header>
            
            <!-- Main profile details section with two columns -->
            <section class="profile-details">
                <!-- Left column - Student information -->
                <div class="details-column">
                    <!-- School information group -->
                    <div class="detail-group">
                        <span class="detail-label">
                            Student Information
                            <button class="btn-icon btn-sm" title="Edit school info">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </span>
                        <span class="detail-value" id="profile-school">
                            <?= htmlspecialchars($user['university'] ?: 'Not set') ?>
                            <small><?= htmlspecialchars($user['college'] ?: 'Not set') ?></small>
                            <button class="edit-detail" title="Edit school">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                            <!-- Edit form for school information -->
                            <div class="edit-form" id="school-form">
                                <form action="/attendance-system/user/update_profile" method="post">
                                    <input type="hidden" name="field" value="university">
                                    <input type="text" name="university" class="form-control" 
                                           value="<?= htmlspecialchars($user['university']) ?>" required>
                                    <input type="text" name="college" class="form-control" 
                                           value="<?= htmlspecialchars($user['college']) ?>" required>
                                    <div class="edit-actions">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="button" class="btn btn-outline cancel-edit">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </span>
                    </div>
                    
                    <!-- Student ID group -->
                    <div class="detail-group">
                        <span class="detail-label">
                            Student ID
                            <button class="btn-icon btn-sm" title="Edit ID">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </span>
                        <span class="detail-value" id="profile-id">
                            <?= htmlspecialchars($user['student_number'] ?: 'Not set') ?>
                            <button class="edit-detail" title="Edit ID">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                            <!-- Edit form for student ID -->
                            <div class="edit-form" id="student-id-form">
                                <form action="/attendance-system/user/update_profile" method="post">
                                    <input type="hidden" name="field" value="student_number">
                                    <input type="text" name="student_number" class="form-control" 
                                           value="<?= htmlspecialchars($user['student_number']) ?>" required>
                                    <div class="edit-actions">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="button" class="btn btn-outline cancel-edit">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </span>
                    </div>
                    
                    <!-- Program information group -->
                    <div class="detail-group">
                        <span class="detail-label">
                            Program
                            <button class="btn-icon btn-sm" title="Edit program">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </span>
                        <span class="detail-value">
                            <?= htmlspecialchars($user['program'] ?: 'Not set') ?>
                            <small>Year <?= htmlspecialchars($user['year_level'] ?: 'Not set') ?></small>
                            <button class="edit-detail" title="Edit program">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                            <!-- Edit form for program information -->
                            <div class="edit-form" id="program-form">
                                <form action="/attendance-system/user/update_profile" method="post">
                                    <input type="hidden" name="field" value="program">
                                    <input type="text" name="program" class="form-control" 
                                           value="<?= htmlspecialchars($user['program']) ?>" required>
                                    <input type="text" name="year_level" class="form-control" 
                                           value="<?= htmlspecialchars($user['year_level']) ?>">
                                    <div class="edit-actions">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="button" class="btn btn-outline cancel-edit">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </span>
                    </div>
                </div>
                
                <!-- Right column - Contact and internship info -->
                <div class="details-column">
                    <!-- Contact information group -->
                    <div class="detail-group">
                        <span class="detail-label">
                            Contact Details
                            <button class="btn-icon btn-sm" title="Edit contact">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </span>
                        <span class="detail-value" id="profile-contact">
                            <?= htmlspecialchars($user['phone'] ?: 'Not set') ?>
                            <small><?= htmlspecialchars($user['email'] ?: 'Not set') ?></small>
                            <button class="edit-detail" title="Edit contact">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                            <!-- Edit form for contact information -->
                            <div class="edit-form" id="contact-form">
                                <form action="/attendance-system/user/update_profile" method="post">
                                    <input type="hidden" name="field" value="contact">
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?= htmlspecialchars($user['phone']) ?>" required>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                    <div class="edit-actions">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="button" class="btn btn-outline cancel-edit">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </span>
                    </div>
                    
                    <!-- Internship period group -->
                    <div class="detail-group">
                        <span class="detail-label">
                            Internship Period
                            <button class="btn-icon btn-sm" title="Edit dates">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </span>
                        <span class="detail-value" id="profile-dates">
                            <?php
                            $start = $user['internship_start'] ? format_date($user['internship_start']) : 'Not set';
                            $end = $user['internship_end'] ? format_date($user['internship_end']) : 'Not set';
                            echo "$start to $end";
                            ?>
                            <small>
                                <?php
                                if ($user['internship_start'] && $user['internship_end']) {
                                    $startDate = new DateTime($user['internship_start']);
                                    $endDate = new DateTime($user['internship_end']);
                                    $interval = $startDate->diff($endDate);
                                    $weeks = floor($interval->days / 7);
                                    echo "($weeks weeks, " . htmlspecialchars($user['required_hours'] ?: 0) . " required hours)";
                                } else {
                                    echo "(Duration not set)";
                                }
                                ?>
                            </small>
                            <button class="edit-detail" title="Edit dates">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                            <!-- Edit form for internship dates -->
                            <div class="edit-form" id="internship-form">
                                <form action="/attendance-system/user/update_profile" method="post">
                                    <input type="hidden" name="field" value="internship">
                                    <div class="form-group">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="internship_start" class="form-control" 
                                               value="<?= htmlspecialchars($user['internship_start']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="internship_end" class="form-control" 
                                               value="<?= htmlspecialchars($user['internship_end']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Required Hours</label>
                                        <input type="number" name="required_hours" class="form-control" 
                                               value="<?= htmlspecialchars($user['required_hours']) ?>">
                                    </div>
                                    <div class="edit-actions">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="button" class="btn btn-outline cancel-edit">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </span>
                    </div>
                    
                    <!-- Supervisor information group -->
                    <div class="detail-group">
                        <span class="detail-label">
                            Supervisor
                            <button class="btn-icon btn-sm" title="Edit supervisor">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </span>
                        <span class="detail-value" id="profile-supervisor">
                            <?php
                            $supervisor_notes = json_decode($user['supervisor_notes'] ?? '', true);
                            echo htmlspecialchars($supervisor_notes['name'] ?? 'Not assigned');
                            ?>
                            <small><?= htmlspecialchars($supervisor_notes['email'] ?? '') ?></small>
                            <button class="edit-detail" title="Edit supervisor">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                            <!-- Edit form for supervisor information -->
                            <div class="edit-form" id="supervisor-form">
                                <form action="/attendance-system/user/update_profile" method="post">
                                    <input type="hidden" name="field" value="supervisor">
                                    <input type="text" name="supervisor_name" class="form-control" 
                                           value="<?= htmlspecialchars($supervisor_notes['name'] ?? '') ?>" required>
                                    <input type="email" name="supervisor_email" class="form-control" 
                                           value="<?= htmlspecialchars($supervisor_notes['email'] ?? '') ?>" required>
                                    <div class="edit-actions">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="button" class="btn btn-outline cancel-edit">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </span>
                    </div>
                </div>
            </section>
            
            <!-- Tab navigation for different profile sections -->
            <nav class="tab-nav" role="tablist" data-active-tab="dtr">
                <button class="tab-button active" id="dtr-tab" role="tab" aria-selected="true" aria-controls="dtr-panel" data-tab-target="dtr">
                    <i class="fas fa-clock"></i> DTR
                </button>
                <button class="tab-button" id="journal-tab" role="tab" aria-selected="false" aria-controls="journal-panel" data-tab-target="journal">
                    <i class="fas fa-book"></i> Journal
                </button>
                <button class="tab-button" id="tasks-tab" role="tab" aria-selected="false" aria-controls="tasks-panel" data-tab-target="tasks">
                    <i class="fas fa-tasks"></i> Tasks
                </button>
                <button class="tab-button" id="evaluation-tab" role="tab" aria-selected="false" aria-controls="evaluation-panel" data-tab-target="evaluation">
                    <i class="fas fa-star"></i> Evaluation
                </button>
            </nav>
            
            <!-- Tab content panels -->
            <section class="tab-content">
                <!-- Daily Time Record Panel -->
                <div id="dtr-panel" role="tabpanel" aria-labelledby="dtr-tab" class="tab-panel">
                    <!-- Panel content... -->
                </div>
                
                <!-- Journal Panel -->
                <div id="journal-panel" role="tabpanel" aria-labelledby="journal-tab" hidden class="tab-panel">
                    <!-- Panel content... -->
                </div>
                
                <!-- Tasks Panel -->
                <div id="tasks-panel" role="tabpanel" aria-labelledby="tasks-tab" hidden class="tab-panel">
                    <!-- Panel content... -->
                </div>
                
                <!-- Evaluation Panel -->
                <div id="evaluation-panel" role="tabpanel" aria-labelledby="evaluation-tab" hidden class="tab-panel">
                    <!-- Panel content... -->
                </div>
            </section>
        </article>
    </div>

    <!-- Modal for editing profile information -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Profile Information</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form action="/attendance-system/user/update_profile" method="post">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Main DOM content loaded event
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabButtons = document.querySelectorAll('[role="tab"]');
            const tabPanels = document.querySelectorAll('[role="tabpanel"]');
            const tabNav = document.querySelector('.tab-nav');
            
            // Tab switching logic
            document.querySelector('[role="tablist"]').addEventListener('click', function(e) {
                const tab = e.target.closest('[role="tab"]');
                if (!tab) return;
                
                e.preventDefault();
                switchTab(tab);
            });
            
            // Keyboard navigation for tabs
            document.querySelector('[role="tablist"]').addEventListener('keydown', function(e) {
                const activeTab = document.querySelector('[role="tab"][aria-selected="true"]');
                
                if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                    const direction = e.key === 'ArrowRight' ? 1 : -1;
                    const tabs = Array.from(tabButtons);
                    const currentIndex = tabs.indexOf(activeTab);
                    let nextIndex = currentIndex + direction;
                    
                    if (nextIndex < 0) nextIndex = tabs.length - 1;
                    if (nextIndex >= tabs.length) nextIndex = 0;
                    
                    switchTab(tabs[nextIndex]);
                    tabs[nextIndex].focus();
                }
                
                if (e.key === 'Home') {
                    switchTab(tabButtons[0]);
                    tabButtons[0].focus();
                    e.preventDefault();
                }
                
                if (e.key === 'End') {
                    switchTab(tabButtons[tabButtons.length - 1]);
                    tabButtons[tabButtons.length - 1].focus();
                    e.preventDefault();
                }
            });
            
            // Function to switch between tabs
            function switchTab(newTab) {
                const controls = newTab.getAttribute('aria-controls');
                const panel = document.getElementById(controls);
                const tabTarget = newTab.getAttribute('data-tab-target');
                
                tabNav.setAttribute('data-active-tab', tabTarget);
                
                // Update tab states
                tabButtons.forEach(tab => {
                    tab.setAttribute('aria-selected', 'false');
                    tab.classList.remove('active');
                });
                
                // Hide all panels with animation
                tabPanels.forEach(p => {
                    if (!p.hidden) {
                        p.style.opacity = '0';
                        p.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            p.hidden = true;
                        }, 300);
                    }
                });
                
                // Activate new tab
                newTab.setAttribute('aria-selected', 'true');
                newTab.classList.add('active');
                
                // Show new panel with animation
                setTimeout(() => {
                    panel.hidden = false;
                    setTimeout(() => {
                        panel.style.opacity = '1';
                        panel.style.transform = 'translateY(0)';
                    }, 10);
                }, 300);
            }
            
            // Initialize first tab
            const firstTab = document.querySelector('[role="tab"][aria-selected="true"]');
            if (firstTab) {
                firstTab.classList.add('active');
                const firstPanel = document.getElementById(firstTab.getAttribute('aria-controls'));
                if (firstPanel) {
                    firstPanel.hidden = false;
                }
            }
            
            // Edit functionality for profile details
            document.querySelectorAll('.edit-detail, .btn-icon[title*="Edit"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Check if this is a specific field edit or general modal
                    const parentValue = this.closest('.detail-value');
                    if (parentValue) {
                        const formId = parentValue.id.replace('profile-', '') + '-form';
                        const editForm = document.getElementById(formId);
                        
                        // Toggle the specific edit form
                        if (editForm) {
                            editForm.classList.toggle('active');
                            return;
                        }
                    }
                    
                    // Otherwise show the general modal
                    document.getElementById('editModal').classList.add('active');
                });
            });
            
            // Cancel buttons in edit forms
            document.querySelectorAll('.cancel-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.edit-form').classList.remove('active');
                });
            });
            
            // Modal close functionality
            const modalClose = document.querySelector('.modal-close');
            const editModal = document.getElementById('editModal');
            
            modalClose.addEventListener('click', function() {
                editModal.classList.remove('active');
            });
            
            editModal.addEventListener('click', function(e) {
                if (e.target === editModal) {
                    editModal.classList.remove('active');
                }
            });
            
            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && editModal.classList.contains('active')) {
                    editModal.classList.remove('active');
                }
            });
            
            // Form submission handling with AJAX
        document.querySelectorAll('.edit-form form, #editModal form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const field = formData.get('field');
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update the displayed values based on which form was submitted
                        switch(field) {
                            case 'university':
                                document.getElementById('profile-school').innerHTML = 
                                    `${formData.get('university')}<small>${formData.get('college')}</small>
                                    <button class="edit-detail" title="Edit school">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div class="edit-form" id="school-form">
                                        <!-- Keep the edit form structure -->
                                        ${document.getElementById('school-form').innerHTML}
                                    </div>`;
                                break;
                                
                            case 'student_number':
                                document.getElementById('profile-id').innerHTML = 
                                    `${formData.get('student_number')}
                                    <button class="edit-detail" title="Edit ID">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div class="edit-form" id="student-id-form">
                                        ${document.getElementById('student-id-form').innerHTML}
                                    </div>`;
                                break;
                                
                            case 'program':
                                document.querySelector('#profile-program .detail-value').innerHTML = 
                                    `${formData.get('program')}<small>Year ${formData.get('year_level')}</small>
                                    <button class="edit-detail" title="Edit program">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div class="edit-form" id="program-form">
                                        ${document.getElementById('program-form').innerHTML}
                                    </div>`;
                                break;
                                
                            case 'contact':
                                document.getElementById('profile-contact').innerHTML = 
                                    `${formData.get('phone')}<small>${formData.get('email')}</small>
                                    <button class="edit-detail" title="Edit contact">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div class="edit-form" id="contact-form">
                                        ${document.getElementById('contact-form').innerHTML}
                                    </div>`;
                                break;
                                
                            case 'internship':
                                const startDate = new Date(formData.get('internship_start')).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                                const endDate = new Date(formData.get('internship_end')).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                                document.getElementById('profile-dates').innerHTML = 
                                    `${startDate} to ${endDate}
                                    <small>(${formData.get('required_hours')} required hours)</small>
                                    <button class="edit-detail" title="Edit dates">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div class="edit-form" id="internship-form">
                                        ${document.getElementById('internship-form').innerHTML}
                                    </div>`;
                                break;
                                
                            case 'supervisor':
                                document.getElementById('profile-supervisor').innerHTML = 
                                    `${formData.get('supervisor_name')}<small>${formData.get('supervisor_email')}</small>
                                    <button class="edit-detail" title="Edit supervisor">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div class="edit-form" id="supervisor-form">
                                        ${document.getElementById('supervisor-form').innerHTML}
                                    </div>`;
                                break;
                                
                            default:
                                // For modal form updates (full name, email, phone)
                                document.querySelector('.profile-title').innerHTML = 
                                    `${formData.get('full_name')} 
                                    <button class="btn-icon" title="Edit name">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>`;
                                document.getElementById('profile-contact').innerHTML = 
                                    `${formData.get('phone')}<small>${formData.get('email')}</small>
                                    <button class="edit-detail" title="Edit contact">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div class="edit-form" id="contact-form">
                                        ${document.getElementById('contact-form').innerHTML}
                                    </div>`;
                                break;
                        }
                                                  // Close the form/modal
                        const editForm = this.closest('.edit-form');
                        if (editForm) {
                            editForm.classList.remove('active');
                        } else {
                            document.getElementById('editModal').classList.remove('active');
                        }
                        
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the profile');
                });
            });
        });
    });

    </script>
</body>
</html>