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
       

        /* NAVIGATION STYLES */
        .main-nav {
            background-color: #f8f9fa;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* TABLE STYLES */
        table {
            border-collapse: collapse;
            width: 90vw;
            page-break-after: auto;
            margin: 0 auto;
        }

        th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }

        .headerlogo {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
        }

        .ocd-logo img, .bp-logo img {
            width: 90px;
            margin: 20px;
            max-height: 80px;
        }

        .ocd-text {
            margin: 0;
            padding: 0;
            text-align: center;
            line-height: 1.1;
        }

        .ocd-text h1, .ocd-text h2, 
        .ocd-text h4, .ocd-text h5 {
            margin: 0;
            padding: 0;
        }

        /* SIGNATURE AREA STYLES */
        .signature-box {
            width: 30%;
            text-align: center;
            margin: 20px auto 0;
        }

        .signature-box p {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .people-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .signature-person {
            min-width: 150px;
            padding: 10px;
            border-top: 1px solid #000;
            margin: 2px 0;
        }

        /* FORM STYLES */
        .form-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .form-container.active-form {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-btn {
            padding: 8px 15px;
            margin-right: 10px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-btn:hover {
            background-color: #45a049;
        }
        
        .dtr-print-section {
            display: none;
        }
        
        /* PRINT STYLES */
        @media print {
            .no-print {
                display: none !important;
            }
            .form-container {
                display: none !important;
            }
            body {
                padding: 0;
                font-size: 7pt;
            }
            @page {
                size: A4 landscape;
                margin: 0.3cm;
            }
            .dtr-print-section {
                display: block !important;
                page-break-after: always;
                padding: 5px;
            }
            .month-container {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                page-break-inside: avoid;
            }
            .month-table {
                width: 48%;
                flex-shrink: 0;
            }
            .month-table h4 {
                font-size: 8pt;
                margin: 5px 0;
                text-align: center;
            }
            .compact-dtr-table {
                font-size: 6pt;
                width: 100%;
                border-collapse: collapse;
                margin: 2px 0;
            }
            .compact-dtr-table th,
            .compact-dtr-table td {
                padding: 1px;
                border: 0.5px solid #ddd;
                text-align: center;
                line-height: 1.1;
                font-size: 6pt;
            }
            .compact-dtr-table th {
                background-color: #f5f5f5;
                font-weight: bold;
            }
            .compact-dtr-table td {
                height: 12px;
            }
        }
    </style>
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
           <!-- Verified By Form -->
    <div id="verifiedForm" class="form-container">
        <h3>Add Reviewed By</h3>
        <div class="form-group">
            <label for="verifiedName">Name:</label>
            <input type="text" id="verifiedName" placeholder="Enter name">
        </div>
        <div class="form-group">
            <label for="verifiedPosition">Position:</label>
            <input type="text" id="verifiedPosition" placeholder="Enter position">
        </div>
        <button class="form-btn" onclick="updateVerifiedPerson()">Update</button>
        <button class="form-btn" onclick="hideForms()">Close</button>
    </div>
    <!-- Cover Page (only shows when printing) -->
    <div class="cover-page">
  
         <!-- Header Table -->
    <div>
        <table>
            <tr>
                <th colspan="11" style="border:none">
                    <div class="headerlogo">
                        <div class="ocd-logo">
                            <img src="\trial\img\ocd.png" alt="OCD Logo">
                        </div>
                        <div class="ocd-text">
                            <h4>Republic of the Philippines</h4>
                            <h4>Department of National Defense</h4>
                            <h1>OFFICE OF CIVIL DEFENSE</h1>
                            <h2>CORDILLERA ADMINISTRATIVE REGION</h2>
                            <h5>NO. 55 First Road, Quazon HILL PROPER, BAGUIO CITY, 2600</h5>
                        </div>
                        <div class="bp-logo">
                            <img src="\trial\img\bp.png" alt="BP Logo">
                        </div>
                    </div>
                </th>
            </tr>     
        </table>
    </div>

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

    <!-- DTR Table (Print Only) -->
    <div class="dtr-print-section">
        <h3>Daily Time Record</h3>
        <?php
        // Fetch all attendance records
        $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY time_in ASC");
        $stmt->execute([$view_user_id]);
        $attendance_records = $stmt->fetchAll();
        
        if (!empty($attendance_records)) {
            // Get date range from first to last attendance
            $firstAttendance = new DateTime(date('Y-m-d', strtotime($attendance_records[0]['time_in'])));
            $lastAttendance = new DateTime(date('Y-m-d', strtotime(end($attendance_records)['time_in'])));
            
            // Group records by date
            $daily_records = [];
            foreach ($attendance_records as $record) {
                $date = date('Y-m-d', strtotime($record['time_in']));
                $time = date('H:i:s', strtotime($record['time_in']));
                $session = (date('H', strtotime($time)) < 12) ? 'morning' : 'afternoon';
                
                if (!isset($daily_records[$date])) {
                    $daily_records[$date] = [
                        'morning' => ['in' => null, 'out' => null],
                        'afternoon' => ['in' => null, 'out' => null],
                        'total_hours' => 0
                    ];
                }
                
                if ($session == 'morning') {
                    $daily_records[$date]['morning']['in'] = $record['time_in'];
                    $daily_records[$date]['morning']['out'] = $record['time_out'] ?? null;
                } else {
                    $daily_records[$date]['afternoon']['in'] = $record['time_in'];
                    $daily_records[$date]['afternoon']['out'] = $record['time_out'] ?? null;
                }
                
                if ($record['time_out']) {
                    $timeIn = new DateTime($record['time_in']);
                    $timeOut = new DateTime($record['time_out']);
                    $interval = $timeIn->diff($timeOut);
                    $daily_records[$date]['total_hours'] += $interval->h + ($interval->i / 60);
                }
            }
            
            // Generate monthly tables - two months per page
            $currentMonth = $firstAttendance->format('Y-m');
            $endMonth = $lastAttendance->format('Y-m');
            $monthCount = 0;
            
            $formatTime = function($time) {
                return $time ? date('H:i', strtotime($time)) : '--';
            };
            
            while ($currentMonth <= $endMonth) {
                if ($monthCount % 2 === 0) {
                    echo '<div class="month-container">';
                }
                
                $monthStart = new DateTime($currentMonth . '-01');
                $monthEnd = new DateTime($monthStart->format('Y-m-t'));
                
                echo '<div class="month-table">';
                echo '<h4>' . $monthStart->format('F Y') . '</h4>';
                echo '<table class="compact-dtr-table">';
                echo '<thead><tr><th>Date</th><th>AM In</th><th>AM Out</th><th>PM In</th><th>PM Out</th><th>Hrs</th></tr></thead>';
                echo '<tbody>';
                
                // Generate all days in month
                $period = new DatePeriod($monthStart, new DateInterval('P1D'), $monthEnd->modify('+1 day'));
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $dayData = $daily_records[$dateStr] ?? null;
                    
                    echo '<tr>';
                    echo '<td>' . $date->format('j') . '</td>';
                    
                    if ($dayData) {
                        echo '<td>' . $formatTime($dayData['morning']['in']) . '</td>';
                        echo '<td>' . $formatTime($dayData['morning']['out']) . '</td>';
                        echo '<td>' . $formatTime($dayData['afternoon']['in']) . '</td>';
                        echo '<td>' . $formatTime($dayData['afternoon']['out']) . '</td>';
                        echo '<td>' . number_format($dayData['total_hours'], 1) . '</td>';
                    } else {
                        echo '<td colspan="5" style="color:#999;font-size:5pt;">No Record</td>';
                    }
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
                echo '</div>';
                
                $monthCount++;
                
                if ($monthCount % 2 === 0 || $currentMonth === $endMonth) {
                    echo '</div>';
                }
                
                // Move to next month
                $monthStart = new DateTime($currentMonth . '-01');
                $monthStart->modify('+1 month');
                $currentMonth = $monthStart->format('Y-m');
            }
        } else {
            echo '<p>No attendance records found.</p>';
        }
        ?>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="print-controls">
             <button class="print-btn" onclick="showForm('verifiedForm')">Reviewed By</button>
            <button class="print-btn" onclick="window.print()">Print Journals</button>
        </div>
        

        
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
                 <!-- Verified By Display Area -->
    <div class="signature-box">
        <p>Reviewed by:</p>
        <div class="people-container" id="verifiedDisplay">
            <div class="person-item">
                <div class="signature-person">[No one Reviewed yet]</div>
            </div>
        </div>
    </div>
        <a href="javascript:window.close();" class="back-link">← Back to Journal</a>
    </div>
    
    <script>
        // Automatically trigger print dialog if print parameter exists
        if (window.location.search.includes('print=true')) {
            window.print();
        }
    </script>
       <script>
        // Stores verified person data
        let verifiedPerson = { name: '', position: '' };

        function showForm(formId) {
            hideForms();
            document.getElementById(formId).classList.add('active-form');
        }

        function hideForms() {
            document.querySelectorAll('.form-container').forEach(form => {
                form.classList.remove('active-form');
            });
        }

        function updateVerifiedPerson() {
            const name = document.getElementById('verifiedName').value;
            const position = document.getElementById('verifiedPosition').value;
            if (name && position) {
                verifiedPerson = { name, position };
                updateVerifiedDisplay();
                document.getElementById('verifiedName').value = '';
                document.getElementById('verifiedPosition').value = '';
                hideForms();
            } else {
                alert('Please enter both name and position');
            }
        }

        function updateVerifiedDisplay() {
            const display = document.getElementById('verifiedDisplay');
            
            display.innerHTML = verifiedPerson.name 
                ? `<div class="person-item">
                     <div class="signature-person">
                         ${verifiedPerson.name}<br><hr>${verifiedPerson.position}
                     </div>
                   </div>`
                : `<div class="person-item">
                     <div class="signature-person">[No one Reviewed yet]</div>
                   </div>`;
        }

        // Initialize display on page load
        document.addEventListener('DOMContentLoaded', updateVerifiedDisplay);
    </script>
</body>
</html>