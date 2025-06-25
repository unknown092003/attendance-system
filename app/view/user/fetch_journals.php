<?php
// fetch_journals.php
require_once '../../config.php'; // Adjust path as needed

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date-desc';
$order = 'DESC';

switch ($sort) {
    case 'date-asc':
        $order = 'ASC';
        break;
    case 'date-desc':
    default:
        $order = 'DESC';
        break;
}

$stmt = $pdo->prepare("SELECT * FROM daily_journals WHERE user_id = ? ORDER BY date $order");
$stmt->execute([$_SESSION['user_id']]);
$journals = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($journals);