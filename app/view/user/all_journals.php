<?php
// Database connection with error handling
$host = 'localhost';
$db   = 'attendance_system';
$user_db = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user_db, $pass, $options);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $isUser = isset($_SESSION['user_id']);
    $isAdmin = isset($_SESSION['admin_id']) && ($_SESSION['role'] ?? '') === 'admin';
    
    // Determine which user journals to show
    $view_user_id = $_SESSION['user_id'] ?? null;
    if ($isAdmin && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $view_user_id = (int)$_GET['user_id'];
    }
    if (!$isUser && !$isAdmin) {
        die("Please login to view this page");
    }
    if (!$view_user_id) {
        die("Invalid user request");
    }
    // Get user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$view_user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        die("User not found");
    }
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch journal entries for the user
$stmt = $pdo->prepare("SELECT * FROM daily_journals WHERE user_id = ? ORDER BY date ASC");
$stmt->execute([$view_user_id]);
$journals = $stmt->fetchAll();

// Determine the first date with an entry, if any
$firstDate = !empty($journals) ? new DateTime($journals[0]['date']) : null;

// Calculate total completed hours from attendance table
$total_hours = 0;
$stmt = $pdo->prepare("SELECT time_in, time_out FROM attendance WHERE user_id = ? AND time_in IS NOT NULL AND time_out IS NOT NULL");
$stmt->execute([$view_user_id]);
$attendance_records = $stmt->fetchAll();
foreach ($attendance_records as $rec) {
    $in = new DateTime($rec['time_in']);
    $out = new DateTime($rec['time_out']);
    $interval = $in->diff($out);
    $hours = $interval->h + ($interval->i / 60);
    $total_hours += $hours;
}
$total_days = $total_hours > 0 ? floor($total_hours / 8) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Journal - <?= htmlspecialchars($user['full_name']) ?></title>
    <style>
        /* Base styles */
        body {
            background-color: #001938;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: white;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* Cover page styles - only shown when printing */
        .cover-page {
            display: none;
            text-align: center;
            page-break-after: always;
        }
        @media print {
            .cover-page {
                display: block;
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .cover-title {
                font-size: 24pt;
                margin-bottom: 2cm;
            }
            .intern-info {
                width: 100%;
                margin-bottom: 2cm;
            }
            .intern-info table {
                width: 100%;
                border-collapse: collapse;
            }
            .intern-info td {
                padding: 10px;
                border: 1px solid #ddd;
            }
            .intern-info td:first-child {
                font-weight: bold;
                width: 30%;
                background-color: #f5f5f5;
            }
            .signature-line {
                margin-top: 3cm;
                width: 60%;
                border-top: 1px solid #000;
                text-align: center;
                padding-top: 5px;
            }
        }
        
        /* Journal content styles */
        h1 {
            color: orange;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .journal-entry {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            page-break-inside: avoid;
        }
        .entry-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .day-number {
            font-weight: bold;
            color: #3498db;
        }
        .entry-date {
            color: #7f8c8d;
        }
        .entry-content {
            white-space: pre-line;
            margin-bottom: 8px;
        }
        .entry-meta {
            font-size: 0.9em;
            color: #95a5a6;
        }
        .no-entries {
            text-align: center;
            color: #7f8c8d;
            margin-top: 40px;
            font-style: italic;
        }
        
        /* Print controls */
        .print-controls {
            margin-bottom: 20px;
            text-align: right;
        }
        .print-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #2980b9;
        }
        
        /* Print-specific styles */
        @media print {
            body {
                padding: 0;
                font-size: 12pt;
                background: white;
                color: black;
            }
            .print-controls {
                display: none;
            }
            .container {
                max-width: 100%;
            }
            h1 {
                text-align: center;
                border: none;
                margin-bottom: 30px;
                color: black;
            }
            .journal-entry {
                border-bottom: 1px solid #ddd;
                margin-bottom: 20px;
                padding-bottom: 15px;
            }
            .back-link {
                display: none;
            }
            @page {
                margin: 2cm;
                @top-center {
                    content: "Internship Journal";
                    font-size: 12pt;
                }
                @bottom-center {
                    content: counter(page);
                    font-size: 10pt;
                }
            }
            @page:first {
                @top-center {
                    content: none;
                }
            }
        }
    </style>
</head>
<body>
    <!-- Cover Page (only shows when printing) -->
    <div class="cover-page">
        <h1 class="cover-title">INTERNSHIP JOURNAL</h1>
        
        <div class="intern-info">
            <table>
                <tr>
                    <td>Intern Name:</td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                </tr>
                <tr>
                    <td>Required Hours:</td>
                    <td><?= htmlspecialchars($user['required_hours'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td>Start Date:</td>
                    <td><?= !empty($journals) ? htmlspecialchars($journals[0]['date']) : 'N/A' ?></td>
                </tr>
                <tr>
                    <td>End Date:</td>
                    <td><?= !empty($journals) ? htmlspecialchars(end($journals)['date']) : 'N/A' ?></td>
                </tr>
                <tr>
                    <td>Total Completed Hours:</td>
                    <td><?= number_format($total_hours, 2) ?></td>
                </tr>
                <tr>
                    <td>Total Days (from hours):</td>
                    <td><?= htmlspecialchars($total_days) ?></td>
                </tr>
            </table>
        </div>
        
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="print-controls">
            <button class="print-btn" onclick="window.print()">Print Journals</button>
        </div>
        
        <h1>Journal Entries - <?= htmlspecialchars($user['full_name']) ?></h1>
        
        <?php if (empty($journals)): ?>
            <div class="no-entries">
                <p>No journal entries found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($journals as $journal): ?>
                <?php
                // Calculate day number based on first entry
                $currentDate = new DateTime($journal['date']);
                $dayNumber = $firstDate ? $firstDate->diff($currentDate)->days + 1 : 1;
                ?>
                
                <div class="journal-entry">
                    <div class="entry-header">
                        <span class="day-number">Day <?= htmlspecialchars($dayNumber) ?></span>
                        <span class="entry-date"><?= htmlspecialchars($journal['date']) ?></span>
                    </div>
                    
                    <div class="entry-content">
                        <?= nl2br(htmlspecialchars($journal['journal_text'])) ?>
                    </div>
                    
                    <div class="entry-meta">
                        Recorded on: <?= htmlspecialchars(date('M j, Y g:i A', strtotime($journal['created_at']))) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <a href="javascript:window.close();" class="back-link">← Back to Journal</a>
    </div>
    
    <script>
        // Automatically trigger print dialog if print parameter exists
        if (window.location.search.includes('print=true')) {
            window.print();
        }
    </script>
</body>
</html>