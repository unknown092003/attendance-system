<?php
// Start session at the very top before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

    // Allow access for logged in users or admins
    $isUser = isset($_SESSION['user_id']);
    $isAdmin = isset($_SESSION['admin_id']) && ($_SESSION['role'] ?? '') === 'admin';
    
    if (!$isUser && !$isAdmin) {
        die("Please login to view this page");
    }

    // Determine which user profile to show
    $view_user_id = $_SESSION['user_id'] ?? null;
    
    // If admin viewing another profile, get user ID from request
    if (($_SESSION['role'] ?? '') === 'admin' && isset($_GET['student_id'])) {
        $view_user_id = (int)$_GET['student_id'];
    }
    
    // Validate user ID exists
    if (!$view_user_id) {
        die("Invalid user request");
    }
    
    // Fetch user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$view_user_id]);
    $logged_in_user = $stmt->fetch();
    if (!$logged_in_user) {
        die("User not found");
    }
    // If admin and ?user_id= is set, allow viewing other profiles
    if ($logged_in_user['role'] === 'admin' && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $view_user_id = (int)$_GET['user_id'];
    }
    // Get the profile user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$view_user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        die("User not found");
    }
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to get the correct user id for queries
function get_profile_user_id($user) {
    if (isset($user['user_id'])) return $user['user_id'];
    if (isset($user['id'])) return $user['id'];
    return $_SESSION['user_id'] ?? null;
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
        
        .modal-content {
            border-radius: 8px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
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
            /* position: sticky; */
            top: 0;
            /* background: white; */
            z-index: 1;
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
            /* position: sticky; */
            bottom: 0;
            /* background: white; */
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
        
        .form-text {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }
        
        input[type="file"].form-control {
            padding: 0.5rem;
        }
        
        /* PROFILE EDIT BUTTON */
        .profile-edit-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }
        
        .profile-edit-btn:hover {
            background: var(--accent-dark);
        }
        
        /* TWO-COLUMN FORM LAYOUT */
        .form-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
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
        
        .attendance-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        .attendance-table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
        }

        @media (max-width: 600px) {
            .attendance-table-wrapper {
                overflow-x: auto;
            }
            .attendance-table {
                min-width: 600px;
            }
        }

        /* Responsive: horizontal scroll for attendance table on small screens */
        @media (max-width: 600px) {
            .attendance-table {
                display: block;
                width: 100vw;
                overflow-x: auto;
                white-space: nowrap;
            }
            .attendance-table thead, .attendance-table tbody, .attendance-table tr {
                display: table;
                width: 100%;
                table-layout: fixed;
            }
        }
    </style>
</head>
<body>
   <!-- Journal Edit Modal -->
   <div class="modal" id="editJournalModal">
       <div class="modal-content">
           <div class="modal-header">
               <h3 class="modal-title" id="editJournalTitle">Edit Journal Entry</h3>
               <button class="modal-close" id="closeJournalModal">&times;</button>
           </div>
           <div class="modal-body">
               <form id="editJournalForm">
                   <input type="hidden" id="editJournalId" name="journal_id">
                   <div class="form-group">
                       <label for="editJournalText" class="form-label">Journal Entry</label>
                       <textarea id="editJournalText" name="journal_text" class="form-control" rows="10"></textarea>
                   </div>
               </form>
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-secondary" id="cancelJournalEdit">Cancel</button>
               <button type="button" class="btn btn-primary" id="saveJournalEdit">Save Changes</button>
           </div>
       </div>
   </div>
        <!-- Main container for the profile page -->
        <div class="container">
            <!-- Profile card containing all user information -->
            <article class="card profile-card">
                <!-- Profile header section with avatar and basic info -->
                <header class="profile-header">
                    <!-- Profile edit button at top right -->
                    <button class="profile-edit-btn" id="profileEditBtn">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>

                    <!-- User avatar -->
                    <div class="profile-avatar">
                        <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg'): ?>
                            <img src="/attendance-system/assets/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                            <?= get_initials($user['full_name']) ?>
                        <?php endif; ?>
                    </div>
                        
                    <!-- User name -->
                    <h1 class="profile-title">
                        <?= htmlspecialchars($user['full_name']) ?>
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
            <span class="detail-label">School</span>
            <span class="detail-value" id="profile-school">
                <?= htmlspecialchars($user['university'] ?: 'Not set') ?>
            </span>
        </div>
        
        <!-- Course information group -->
        <div class="detail-group">
            <span class="detail-label">Course</span>
            <span class="detail-value" id="profile-course">
                <?= htmlspecialchars($user['program'] ?: 'Not set') ?>
                <small>Year Level: <?= htmlspecialchars($user['college'] ?: 'Not set') ?></small>
            </span>
        </div>
        
        <!-- address information group -->
        <div class="detail-group">
            <span class="detail-label">Address</span>
            <span class="detail-value" id="profile-address">
                <?= htmlspecialchars($user['address'] ?: 'Not set') ?>
            </span>
        </div>
    </div>
    
    <!-- Right column - Contact and internship info -->
    <div class="details-column">
        <!-- Contact information group -->
        <div class="detail-group">
            <span class="detail-label">Contact Details</span>
            <span class="detail-value" id="profile-contact">
                <?= htmlspecialchars($user['phone'] ?: 'Not set') ?>
                <small><?= htmlspecialchars($user['email'] ?: 'Not set') ?></small>
            </span>
        </div>
        
        <!-- Internship period group -->
        <div class="detail-group">
            <span class="detail-label">Internship Period</span>
            <span class="detail-value" id="profile-dates">
                <?php
                // Get first attendance date
                $firstAttendanceStmt = $pdo->prepare("SELECT MIN(time_in) as first_day FROM attendance WHERE user_id = ?");
                $firstAttendanceStmt->execute([get_profile_user_id($user)]);
                $firstAttendance = $firstAttendanceStmt->fetchColumn();
                $start = $firstAttendance ? format_date($firstAttendance) : 'Not set';
                
                // Calculate total completed hours and find completion date
                $hoursStmt = $pdo->prepare("
                    SELECT 
                        SUM(TIMESTAMPDIFF(SECOND, time_in, time_out) / 3600) as total_hours,
                        time_out,
                        DATE(time_out) as completion_date
                    FROM attendance 
                    WHERE user_id = ? AND time_out IS NOT NULL
                    ORDER BY time_out ASC
                ");
                $hoursStmt->execute([get_profile_user_id($user)]);
                $attendanceRecords = $hoursStmt->fetchAll();
                
                $totalHours = 0;
                $completionDate = null;
                $requiredHours = (float)($user['required_hours'] ?: 0);
                
                // Find the exact date when required hours were completed
                foreach ($attendanceRecords as $record) {
                    $recordHours = (float)$record['total_hours'];
                    if ($totalHours < $requiredHours && ($totalHours + $recordHours) >= $requiredHours) {
                        $completionDate = $record['completion_date'];
                        break;
                    }
                    $totalHours += $recordHours;
                }
                
                // Get total hours for display
                $totalHoursStmt = $pdo->prepare("
                    SELECT SUM(TIMESTAMPDIFF(SECOND, time_in, time_out) / 3600) as total_hours
                    FROM attendance 
                    WHERE user_id = ? AND time_out IS NOT NULL
                ");
                $totalHoursStmt->execute([get_profile_user_id($user)]);
                $totalHoursResult = $totalHoursStmt->fetch();
                $totalCompletedHours = (float)($totalHoursResult['total_hours'] ?: 0);
                
                $end = ($totalCompletedHours >= $requiredHours && $completionDate) ? format_date($completionDate) : 'Ongoing';
                echo "$start to $end";
                ?>
                <small>
                    <?php
                    if ($completionDate && $firstAttendance) {
                        $startDate = new DateTime($firstAttendance);
                        $endDate = new DateTime($completionDate);
                        $interval = $startDate->diff($endDate);
                        $weeks = $interval->days > 0 ? floor($interval->days / 7) : 0;
                        echo "($weeks weeks, " . number_format($requiredHours, 0) . " required hours completed)";
                    } elseif ($requiredHours > 0) {
                        $remaining = max(0, $requiredHours - $totalCompletedHours);
                        echo "(" . number_format($totalCompletedHours, 1) . "/" . number_format($requiredHours, 0) . " hours, " . number_format($remaining, 1) . " remaining)";
                    }
                    ?>
                </small>
            </span>
        </div>
                
        <!-- Supervisor information group -->
        <div class="detail-group">
            <span class="detail-label">Supervisor</span>
            <span class="detail-value" id="profile-supervisor">
                <?= htmlspecialchars($user['supervisor'] ?: 'Not assigned') ?>
            </span>
        </div>
        
        <!-- MOA status group -->
        <div class="detail-group">
            <span class="detail-label">MOA Status</span>
            <span class="detail-value" id="profile-moa">
                <?php 
                $moaStatus = $user['moa'] ?? 0;
                echo $moaStatus ? 'Completed' : 'Pending';
                ?>
                <small><?= $moaStatus ? '✓ MOA has been signed' : '⚠ MOA needs to be completed' ?></small>
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
                                
                </nav>
                                
                <!-- Tab content panels -->
                <section class="tab-content">
                    <!-- Daily Time Record Panel -->
                                <!-- Replace the DTR panel with this code -->
                                <div id="dtr-panel" role="tabpanel" aria-labelledby="dtr-tab" class="tab-panel">
                                    <!-- Month navigation for DTR -->
                                    <div class="dtr-month-nav" style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin: 2rem 0 1rem 0;">
                                        <?php
                                        // Get all months with attendance for this user
                                        $stmt = $pdo->prepare("SELECT DISTINCT DATE_FORMAT(time_in, '%Y-%m') as month FROM attendance WHERE user_id = ? ORDER BY month DESC");
                                        $stmt->execute([get_profile_user_id($user)]);
                                        $months = $stmt->fetchAll(PDO::FETCH_COLUMN);

                                        // Get selected month from query param, default to current month if not set or invalid
                                        $selectedMonth = isset($_GET['month']) && in_array($_GET['month'], $months) ? $_GET['month'] : date('Y-m');

                                        // Update firstDay and lastDay based on selected month
                                        $firstDay = $selectedMonth . '-01';
                                        $lastDay = date('Y-m-t', strtotime($firstDay));

                                        // Helper to format month for display
                                        function format_month($ym) {
                                            return date('F Y', strtotime($ym . '-01'));
                                        }
                                    
                                        // Render month navigation buttons
                                        foreach ($months as $month) {
                                            $isActive = ($month === $selectedMonth);
                                            $queryParams = $_GET;
                                            $queryParams['month'] = $month;
                                            $url = '?' . http_build_query($queryParams);
                                            echo '<a href="' . htmlspecialchars($url) . '" class="dtr-month-btn' . ($isActive ? ' active' : '') . '" style="padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; background: ' . ($isActive ? 'var(--accent, #007bff)' : '#f0f0f0') . '; color: ' . ($isActive ? '#fff' : '#333') . '; font-weight: ' . ($isActive ? 'bold' : 'normal') . '; transition: background 0.2s;">' . format_month($month) . '</a>';
                                        }
                                        ?>
                                    </div>
                                    <div class="month-year">
                                        Month: <span id="month"><?= format_month($selectedMonth) ?></span>
                                    </div>
                                    
                                    <div class="attendance-table-wrapper">
                                        <table class="attendance-table">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">Date</th>
                                                    <th colspan="2">Morning</th>
                                                    <th colspan="2">Afternoon</th>
                                                    <th rowspan="2">Total Hours</th>
                                                </tr>
                                                <tr>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Debug: Check if we're getting the correct user_id
                                                // echo "User ID: " . $_SESSION['user_id'];

                                                // Fetch all attendance records for the selected month
                                                $stmt = $pdo->prepare("SELECT * FROM attendance 
                                                                      WHERE user_id = ? 
                                                                      AND DATE(time_in) BETWEEN ? AND ?
                                                                      ORDER BY time_in ASC");
                                                $stmt->execute([get_profile_user_id($user), $firstDay, $lastDay]);
                                                $records = $stmt->fetchAll();

                                                // Debug: Output the raw records we fetched
                                                // echo "<pre>"; print_r($records); echo "</pre>";

                                                // Group records by date and session type
                                                $days = [];
                                                foreach ($records as $record) {
                                                    $date = date('Y-m-d', strtotime($record['time_in']));
                                                    $time = date('H:i:s', strtotime($record['time_in']));

                                                    // Determine if it's morning or afternoon session
                                                    $session = (date('H', strtotime($time)) < 12 ? 'morning' : 'afternoon');

                                                    if (!isset($days[$date])) {
                                                        $days[$date] = [
                                                            'morning' => ['in' => null, 'out' => null],
                                                            'afternoon' => ['in' => null, 'out' => null],
                                                            'total_hours' => 0
                                                        ];
                                                    }

                                                    if ($session == 'morning') {
                                                        $days[$date]['morning']['in'] = $record['time_in'];
                                                        $days[$date]['morning']['out'] = $record['time_out'] ?? null;
                                                    } else {
                                                        $days[$date]['afternoon']['in'] = $record['time_in'];
                                                        $days[$date]['afternoon']['out'] = $record['time_out'] ?? null;
                                                    }

                                                    // Calculate hours if time_out exists
                                                    if ($record['time_out']) {
                                                        $timeIn = new DateTime($record['time_in']);
                                                        $timeOut = new DateTime($record['time_out']);
                                                        $interval = $timeIn->diff($timeOut);
                                                        $hours = $interval->h + ($interval->i / 60);
                                                        $days[$date]['total_hours'] += $hours;
                                                    }
                                                }

                                                // Get all days in month - FIXED: Include the end date by adding 1 day
                                                $start = new DateTime($firstDay);
                                                $end = new DateTime($lastDay);
                                                $end->modify('+1 day'); // This ensures we include the last day
                                                $interval = new DateInterval('P1D');
                                                $period = new DatePeriod($start, $interval, $end);

                                                foreach ($period as $dateObj):
                                                    $date = $dateObj->format('Y-m-d');
                                                    $dayNum = $dateObj->format('j');
                                                    $dayOfWeek = $dateObj->format('D');
                                                    $dayData = $days[$date] ?? [
                                                        'morning' => ['in' => null, 'out' => null],
                                                        'afternoon' => ['in' => null, 'out' => null],
                                                        'total_hours' => 0
                                                    ];

                                                    // Format times
                                                    $formatTime = function($time) {
                                                        return $time ? date('h:i A', strtotime($time)) : '--:-- --';
                                                    };
                                                ?>
                                                <tr>
                                                    <td><?= $dayNum ?> (<?= $dayOfWeek ?>)</td>
                                                    <td><?= $formatTime($dayData['morning']['in']) ?></td>
                                                    <td><?= $formatTime($dayData['morning']['out']) ?></td>
                                                    <td><?= $formatTime($dayData['afternoon']['in']) ?></td>
                                                    <td><?= $formatTime($dayData['afternoon']['out']) ?></td>
                                                    <td><?= number_format($dayData['total_hours'], 2) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                               <?php
                                // --- Journal Data Fetching Logic ---
                                // Get the first journal date for this user
                                $stmt = $pdo->prepare("SELECT MIN(date) as first_date FROM daily_journals WHERE user_id = ?");
                                $stmt->execute([get_profile_user_id($user)]);
                                $firstJournal = $stmt->fetch();
                                $firstDayDate = $firstJournal && $firstJournal['first_date'] ? $firstJournal['first_date'] : null;
                                            
                                // Get all journals for this user
                                $stmt = $pdo->prepare("SELECT * FROM daily_journals WHERE user_id = ? ORDER BY date ASC");
                                $stmt->execute([get_profile_user_id($user)]);
                                $allJournals = $stmt->fetchAll();
                                            
                                // Build a map: date => journal
                                $journalMap = [];
                                foreach ($allJournals as $j) {
                                    $journalMap[$j['date']] = $j;
                                }

                                // Calculate week parameters
                                $week = isset($_GET['week']) ? max(1, intval($_GET['week'])) : 1;
                                $daysPerWeek = 7;
                                $totalDays = !empty($allJournals) ? (strtotime(end($allJournals)['date']) - strtotime($firstDayDate)) / (60 * 60 * 24) + 1 : 0;
                                $maxWeeks = ceil($totalDays / $daysPerWeek);

                                if ($firstDayDate) {
                                    $startOfWeek = date('Y-m-d', strtotime($firstDayDate . ' + ' . (($week - 1) * $daysPerWeek) . ' days'));
                                    $weekDates = [];
                                    for ($i = 0; $i < $daysPerWeek; $i++) {
                                        $weekDates[] = date('Y-m-d', strtotime($startOfWeek . " +$i days"));
                                    }
                                } else {
                                    $weekDates = array_fill(0, $daysPerWeek, date('Y-m-d'));
                                }
                                ?>
                                <!-- journal panel -->
                                <div id="journal-panel" role="tabpanel" aria-labelledby="journal-tab" hidden class="tab-panel">
                                    <div class="journal-header">
                                        <h2 style="color: var(--accent);">My Journal</h2>
                                        <a id="show-all-journals" class="btn" href="/attendance-system/app/view/user/all_journals.php?user_id=<?= get_profile_user_id($user) ?>" target="_blank" rel="noopener">Show All Journals</a>
                                    </div>

                                    <div class="pagination-controls">
                                        <button id="prev-week" class="pagination-btn" <?= $week <= 1 ? 'disabled' : '' ?>>Previous Week</button>
                                        <span id="current-week-display">Week <?= $week ?> of <?= max(1, $maxWeeks) ?></span>
                                        <button id="next-week" class="pagination-btn" <?= $week >= $maxWeeks ? 'disabled' : '' ?>>Next Week</button>
                                    </div>

                                    <div class="weekly-journals">
                                        <?php
                                        $hasJournal = false;
                                        foreach ($weekDates as $idx => $date) {
                                            $dayNum = $idx + 1 + (($week - 1) * $daysPerWeek);
                                            if (isset($journalMap[$date])) {
                                                $hasJournal = true;
                                                $entry = $journalMap[$date];
                                                $journalText = isset($journalMap[$date]) ? $journalMap[$date]['journal_text'] : '';
                                                $journalId = isset($journalMap[$date]) ? $journalMap[$date]['id'] : 0;
                                                $isEdited = isset($journalMap[$date]) && $journalMap[$date]['is_edited'] == 1;
                                                $isAdminEdited = isset($journalMap[$date]) && $journalMap[$date]['edited_by_admin'] == 1;
                                                $dayClass = !empty($journalText) ? 'has-journal' : 'no-journal';
                                                
                                                $headerStyle = 'cursor:pointer; color: ' . ($isEdited ? 'green' : 'var(--secondary)') . ';';
                                                $entryStyle = $isAdminEdited ? 'background-color: #8c0364;' : '';

                                                echo '<div class="journal-entry ' . $dayClass . '" data-journal-id="' . $journalId . '" data-date="' . $date . '" style="' . $entryStyle . '">';
                                                echo '<h3 class="journal-day-header" style="' . $headerStyle . '">Day ' . $dayNum . '</h3>';
                                                echo '<div class="journal-content">';
                                                echo '<p class="journal-text-display" style="color:var(--text);">' . nl2br(htmlspecialchars($journalText ?: 'No journal entry.')) . '</p>';
                                                echo '<small class="journal-date">' . date('F j, Y', strtotime($date)) . '</small>';
                                                echo '</div></div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    
                                    <div id="no-journals-week" class="empty-state" style="<?= $hasJournal ? 'display:none;' : '' ?>">
                                        <p>No journal entries for this week.</p>
                                    </div>
                                </div>
                                    
                                <div id="all-journals-modal" class="modal" style="display: none;">
                                    <div class="modal-content" style="max-width: 700px;">
                                        <span class="close-modal" style="float:right;cursor:pointer;font-size:1.5rem;">&times;</span>
                                        <h2>All Journal Entries</h2>
                                        <div class="sort-controls" style="margin-bottom:1rem;">
                                            <label>Sort by:</label>
                                            <select id="journal-sort">
                                                <option value="date-desc">Date (Newest First)</option>
                                                <option value="date-asc">Date (Oldest First)</option>
                                            </select>
                                        </div>
                                        <div id="all-journals-container" style="max-height:400px;overflow-y:auto;"></div>
                                    </div>
                                </div>

                                                    <script>
                                                        document.addEventListener('DOMContentLoaded', function() {
                                                            // Pagination controls
                                                            document.getElementById('prev-week').addEventListener('click', function() {
                                                                let prev = <?= $week ?> - 1;
                                                                if (prev < 1) prev = 1;
                                                                // Save current scroll position
                                                                const scrollY = window.scrollY;
                                                                window.location.href = updateQueryStringParameter(window.location.search, 'week', prev) + '#journal-panel';
                                                                // Restore scroll position after navigation (if possible)
                                                                window.addEventListener('DOMContentLoaded', function() {
                                                                    window.scrollTo(0, scrollY);
                                                                });
                                                            });
                                                        
                                                            document.getElementById('next-week').addEventListener('click', function() {
                                                                let next = <?= $week ?> + 1;
                                                                window.location.href = updateQueryStringParameter(window.location.search, 'week', next) + '#journal-panel';
                                                            });
                                                        
                                                            function updateQueryStringParameter(uri, key, value) {
                                                                var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
                                                                var separator = uri.indexOf('?') !== -1 ? "&" : "?";
                                                                if (uri.match(re)) {
                                                                    return uri.replace(re, '$1' + key + "=" + value + '$2');
                                                                }
                                                                else {
                                                                    return uri + separator + key + "=" + value;
                                                                }
                                                            }
                                                        
                                                            // Modal functionality
                                                            const modal = document.getElementById('all-journals-modal');
                                                            document.getElementById('show-all-journals').addEventListener('click', function() {
                                                                modal.style.display = 'block';
                                                                fetchAllJournals('date-desc');
                                                            });
                                                        
                                                            document.querySelector('.close-modal').addEventListener('click', function() {
                                                                modal.style.display = 'none';
                                                            });
                                                        
                                                            document.getElementById('journal-sort').addEventListener('change', function() {
                                                                fetchAllJournals(this.value);
                                                            });
                                                        
                                                            function fetchAllJournals(sortOrder) {
                                                                fetch(`app/view/user/fetch_journals.php?user_id=<?= get_profile_user_id($user) ?>&sort=${sortOrder}`)
                                                                    .then(response => response.json())
                                                                    .then(data => {
                                                                        const container = document.getElementById('all-journals-container');
                                                                        container.innerHTML = '';
                                                                    
                                                                        if (data.length === 0) {
                                                                            container.innerHTML = '<p style="text-align:center;">No journal entries found.</p>';
                                                                            return;
                                                                        }
                                                                    
                                                                        // Calculate day numbers based on first entry
                                                                        const firstDate = data.length > 0 ? new Date(data[data.length - 1].date) : null;
                                                                    
                                                                        data.forEach(journal => {
                                                                            const dayNum = firstDate ? 
                                                                                Math.floor((new Date(journal.date) - firstDate) / (1000 * 60 * 60 * 24)) + 1 : 0;
                                                                        
                                                                            const entry = document.createElement('div');
                                                                            entry.className = 'journal-entry';
                                                                            entry.style = "border-bottom:1px solid #eee;padding:0.75rem 0;";
                                                                            entry.innerHTML = `
                                                                                <div style="display:flex;justify-content:space-between;">
                                                                                    <span style="font-weight:bold;">Day ${dayNum}</span>
                                                                                    <span>${new Date(journal.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                                                                                </div>
                                                                                <div style="margin:0.5rem 0 0.25rem 0;white-space:pre-line;">${journal.journal_text}</div>
                                                                                <small style="color:#888;">Created: ${journal.created_at}</small>
                                                                            `;
                                                                            container.appendChild(entry);
                                                                        });
                                                                    })
                                                                    .catch(error => {
                                                                        console.error('Error:', error);
                                                                        document.getElementById('all-journals-container').innerHTML = 
                                                                            '<p style="color:red;">Error loading journals. Please try again.</p>';
                                                                    });
                                                            }
                                                        });
                                                    </script>
                    </section>
            </article>
        </div>

        <!-- Modal for editing all profile information -->
        <div class="modal" id="editModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Profile Information</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
    <form action="/attendance-system/profile/update" method="post" id="profileForm" enctype="multipart/form-data">
        <?php if (isset($_SESSION['csrf_token'])): ?>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <?php endif; ?>
        <?php if (($_SESSION['role'] ?? '') === 'admin' && isset($_GET['student_id'])): ?>
            <input type="hidden" name="target_user_id" value="<?= htmlspecialchars($_GET['student_id']) ?>">
        <?php endif; ?>
        <div class="form-columns">
            <!-- Left Column -->
            <div class="form-column">
                <!-- Avatar Upload -->
                <div class="form-group">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*">
                    <small class="form-text">Current: <?= htmlspecialchars($user['avatar'] ?? 'default.jpg') ?></small>
                </div>
                
                <!-- Personal Information -->
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" 
                           value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                </div>

                <!-- Contact Information -->
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" 
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>

                <!-- School Information -->
                <div class="form-group">
                    <label class="form-label">School</label>
                    <input type="text" name="university" class="form-control" 
                           value="<?= htmlspecialchars($user['university'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Year Level</label>
                    <input type="text" name="college" class="form-control" 
                           value="<?= htmlspecialchars($user['college'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Program/Course</label>
                    <input type="text" name="program" class="form-control" 
                           value="<?= htmlspecialchars($user['program'] ?? '') ?>">
                </div>
            </div>

            <!-- Right Column -->
            <div class="form-column">
                <!-- Address Information -->
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" 
                           value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                </div>

                <!-- Internship Information -->
                <div class="form-group">

                <div class="form-group">
                    <label class="form-label">Required Hours</label>
                    <input type="number" name="required_hours" class="form-control" 
                           value="<?= htmlspecialchars($user['required_hours'] ?? '') ?>">
                </div>

                <!-- Supervisor Information -->
                <div class="form-group">
                    <label class="form-label">Supervisor Name</label>
                    <input type="text" name="supervisor" class="form-control"
                           value="<?= htmlspecialchars($user['supervisor'] ?? '') ?>">
                </div>
                

            </div>
        </div>
        
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
            </div>
        </div>

    <!-- Removed duplicate form tag to avoid nested forms and JS confusion -->
        <script>
        // Main DOM content loaded event - runs when page is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality implementation

            // Get all tab buttons and panels
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

            // Keyboard navigation for tabs (accessibility)
            document.querySelector('[role="tablist"]').addEventListener('keydown', function(e) {
                const activeTab = document.querySelector('[role="tab"][aria-selected="true"]');

                // Right/left arrow key navigation
                if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                    const direction = e.key === 'ArrowRight' ? 1 : -1;
                    const tabs = Array.from(tabButtons);
                    const currentIndex = tabs.indexOf(activeTab);
                    let nextIndex = currentIndex + direction;

                    // Wrap around if at beginning/end
                    if (nextIndex < 0) nextIndex = tabs.length - 1;
                    if (nextIndex >= tabs.length) nextIndex = 0;

                    switchTab(tabs[nextIndex]);
                    tabs[nextIndex].focus();
                }

                // Home/End key navigation
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

            /**
             * Function to switch between tabs
             * @param {HTMLElement} newTab - The tab to activate
             */
            function switchTab(newTab) {
                const controls = newTab.getAttribute('aria-controls');
                const panel = document.getElementById(controls);
                const tabTarget = newTab.getAttribute('data-tab-target');

                // Update active tab indicator
                tabNav.setAttribute('data-active-tab', tabTarget);

                // Deactivate all tabs
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

            // Initialize first tab as active, or show tab based on hash
            function activateTabByHash() {
                const hash = window.location.hash;
                if (hash) {
                    const panel = document.querySelector(hash);
                    if (panel && panel.getAttribute('role') === 'tabpanel') {
                        const tabId = panel.getAttribute('aria-labelledby');
                        const tab = document.getElementById(tabId);
                        if (tab) {
                            switchTab(tab);
                            return;
                        }
                    }
                }
                // Default: activate first tab
                const firstTab = document.querySelector('[role="tab"][aria-selected="true"]');
                if (firstTab) {
                    firstTab.classList.add('active');
                    const firstPanel = document.getElementById(firstTab.getAttribute('aria-controls'));
                    if (firstPanel) {
                        firstPanel.hidden = false;
                    }
                }
            }
            activateTabByHash();
            window.addEventListener('hashchange', activateTabByHash);

            // Modal functionality for the profile edit button
            const profileEditBtn = document.getElementById('profileEditBtn');
            const editModal = document.getElementById('editModal');
            const modalClose = document.querySelector('.modal-close');

            // Open modal when edit button is clicked
            profileEditBtn.addEventListener('click', function() {
                editModal.classList.add('active');
            });

            // Close modal when X button is clicked
            modalClose.addEventListener('click', function() {
                editModal.classList.remove('active');
            });

            // Close modal when clicking outside content
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

            /**
             * Handles form submission for the profile edit form (AJAX)
             * Updates all profile data without page reload
             */
        document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('field', 'all');

    fetch('/attendance-system/profile/update', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error('Network response was not ok: ' + errorText);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update all displayed profile information
            document.querySelector('.profile-title').textContent = formData.get('full_name');
            
            // Update contact info
            document.getElementById('profile-contact').innerHTML = 
                `${formData.get('phone')}<small>${formData.get('email')}</small>`;

            // Update school info
            document.getElementById('profile-school').textContent = formData.get('university');
            
            // Update course info
            document.getElementById('profile-course').innerHTML = 
                `${formData.get('program')}<small>Year Level: ${formData.get('college')}</small>`;
            
            // Update address
            document.getElementById('profile-address').textContent = formData.get('address');

            // Update internship dates
            const startDate = new Date(formData.get('internship_start'));
            const endDate = new Date(formData.get('internship_end'));
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const weeks = Math.floor(diffDays / 7);

            document.getElementById('profile-dates').innerHTML = 
                `${startDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })} 
                to 
                ${endDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}
                <small>(${weeks} weeks, ${formData.get('required_hours')} required hours)</small>`;

            // Update supervisor
            document.getElementById('profile-supervisor').textContent = formData.get('supervisor');



            // Close the modal
            editModal.classList.remove('active');

            // Show success message
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
        </script>

       <script>
       document.addEventListener('DOMContentLoaded', function() {
           const editJournalModal = document.getElementById('editJournalModal');
           const closeJournalModal = document.getElementById('closeJournalModal');
           const cancelJournalEdit = document.getElementById('cancelJournalEdit');
           const saveJournalEdit = document.getElementById('saveJournalEdit');
           const editJournalId = document.getElementById('editJournalId');
           const editJournalText = document.getElementById('editJournalText');
           const editJournalTitle = document.getElementById('editJournalTitle');

           const canEdit = <?= (isset($_SESSION['admin_id']) || $_SESSION['user_id'] == $view_user_id) ? 'true' : 'false' ?>;
           if (canEdit) {
               document.querySelectorAll('.journal-day-header').forEach(header => {
                   header.addEventListener('click', function() {
                       const entryDiv = this.closest('.journal-entry');
                       const journalId = entryDiv.dataset.journalId;
                       const date = entryDiv.dataset.date;
                       const currentText = entryDiv.querySelector('.journal-text-display').innerText;

                       if(journalId && journalId !== "0") {
                           editJournalId.value = journalId;
                           editJournalText.value = currentText === 'No journal entry.' ? '' : currentText;
                           const formattedDate = new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                           editJournalTitle.innerText = `Edit Journal for ${formattedDate}`;
                           editJournalModal.classList.add('active');
                       } else {
                           alert('No journal entry to edit for this day.');
                       }
                   });
               });
           }

           function closeEditModal() {
               editJournalModal.classList.remove('active');
           }

           closeJournalModal.addEventListener('click', closeEditModal);
           cancelJournalEdit.addEventListener('click', closeEditModal);
           editJournalModal.addEventListener('click', function(e) {
               if (e.target === editJournalModal) {
                   closeEditModal();
               }
           });

           saveJournalEdit.addEventListener('click', function() {
               const journalId = editJournalId.value;
               const journalText = editJournalText.value;

               const formData = new FormData();
               formData.append('journal_id', journalId);
               formData.append('journal_text', journalText);
               formData.append('user_id', <?= $view_user_id ?>); // Pass target user ID for authorization

               fetch('/attendance-system/profile/journal/update', {
                   method: 'POST',
                   body: formData
               })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       const entryDiv = document.querySelector(`.journal-entry[data-journal-id='${journalId}']`);
                       const isAdmin = <?= isset($_SESSION['admin_id']) ? 'true' : 'false' ?>;
                       entryDiv.querySelector('.journal-text-display').innerHTML = nl2br(escapeHtml(journalText));
                       if (isAdmin) {
                           entryDiv.style.backgroundColor = '#8c0364';
                       } else {
                           entryDiv.querySelector('.journal-day-header').style.color = 'green';
                       }
                       closeEditModal();
                       alert('Journal updated successfully!');
                   } else {
                       alert('Error: ' + data.message);
                   }
               })
               .catch(error => {
                   console.error('Error:', error);
                   alert('An error occurred while updating the journal.');
               });
           });

           function escapeHtml(text) {
               var map = {
                   '&': '&',
                   '<': '<',
                   '>': '>',
                   '"': '"',
                   "'": '&#039;'
               };
               return text.replace(/[&<>"']/g, function(m) { return map[m]; });
           }

           function nl2br(str) {
               return str.replace(/\\r\\n|\\n\\r|\\r|\\n/g, '<br>');
           }
       });
       </script>
</body>
</html>
