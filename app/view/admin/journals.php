<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML document setup -->
    <meta charset="UTF-8"> <!-- Character encoding for proper text rendering -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport settings -->
    <title>Student Journals - Attendance System</title> <!-- Page title shown in browser tab -->
    
    <!-- CSS stylesheets -->
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css"> <!-- Main stylesheet -->
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css"> <!-- Admin-specific styles -->
</head>
<body>
    <!-- Main page container -->
    <div class="container">
        <!-- Include admin header navigation -->
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <!-- Main content area -->
        <main>
            <!-- Page header section -->
            <div class="page-header">
                <h2>Student Journals</h2> <!-- Main page heading -->
            </div>
            
            <?php if (isset($data['student'])): ?>
                <!-- SINGLE STUDENT JOURNAL VIEW (shown when specific student is selected) -->
                
                <!-- Student journal header section -->
                <div class="journal-header">
                    <h3><?php echo htmlspecialchars($data['student']['full_name']); ?>'s Journal</h3> <!-- Student name -->
                    
                    <!-- Date selection form -->
                    <div class="journal-date">
                        <form action="/attendance-system/admin/journals" method="get">
                            <input type="hidden" name="student_id" value="<?php echo $data['student']['id']; ?>"> <!-- Hidden student ID -->
                            <input type="date" name="date" value="<?php echo $data['date']; ?>" onchange="this.form.submit()"> <!-- Date picker that auto-submits -->
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($data['journal'])): ?>
                    <!-- Journal entry display -->
                    <div class="journal-entry">
                        <div class="journal-content">
                            <!-- Display journal text with proper formatting and escaping -->
                            <?php echo nl2br(htmlspecialchars($data['journal']['journal_text'])); ?>
                        </div>
                        <div class="journal-meta">
                            <!-- Display when journal was submitted -->
                            <p>Submitted: <?php echo date('M d, Y h:i A', strtotime($data['journal']['created_at'])); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Message when no journal exists for selected date -->
                    <div class="alert info">No journal entry found for this date.</div>
                <?php endif; ?>
                
                <!-- Attendance summary section -->
                <div class="attendance-summary">
                    <h4>Attendance for <?php echo date('M d, Y', strtotime($data['date'])); ?></h4> <!-- Formatted date -->
                    
                    <?php if (empty($data['attendance'])): ?>
                        <!-- Message when no attendance records exist -->
                        <p>No attendance records for this date.</p>
                    <?php else: ?>
                        <!-- Attendance records table -->
                        <table>
                            <thead>
                                <tr>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalHours = 0; // Initialize total hours counter
                                foreach ($data['attendance'] as $record): 
                                    $duration = 0; // Initialize duration for this record
                                    if (!empty($record['time_out'])) {
                                        // Calculate duration between time in and time out
                                        $timeIn = new DateTime($record['time_in']);
                                        $timeOut = new DateTime($record['time_out']);
                                        $duration = ($timeOut->getTimestamp() - $timeIn->getTimestamp()) / 3600;
                                        $totalHours += $duration;
                                    }
                                ?>
                                    <tr>
                                        <!-- Time in column -->
                                        <td><?php echo date('h:i A', strtotime($record['time_in'])); ?></td>
                                        <!-- Time out column -->
                                        <td>
                                            <?php if (!empty($record['time_out'])): ?>
                                                <?php echo date('h:i A', strtotime($record['time_out'])); ?>
                                            <?php else: ?>
                                                - <!-- Placeholder when no time out recorded -->
                                            <?php endif; ?>
                                        </td>
                                        <!-- Duration column -->
                                        <td>
                                            <?php if (!empty($record['time_out'])): ?>
                                                <?php 
                                                    // Calculate and format duration as hours and minutes
                                                    $timeIn = new DateTime($record['time_in']);
                                                    $timeOut = new DateTime($record['time_out']);
                                                    $interval = $timeIn->diff($timeOut);
                                                    echo $interval->format('%h hrs %i mins');
                                                ?>
                                            <?php else: ?>
                                                - <!-- Placeholder when no time out recorded -->
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Total hours row -->
                                <tr class="total-row">
                                    <td colspan="2" class="text-right"><strong>Total Hours:</strong></td>
                                    <td><strong><?php echo number_format($totalHours, 2); ?> hrs</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <!-- Back button to return to all students view -->
                <div class="back-link">
                    <a href="/attendance-system/admin/journals" class="btn secondary">Back to All Students</a>
                </div>
            <?php else: ?>
                <!-- ALL STUDENTS LIST VIEW (shown when no specific student is selected) -->
                
                <!-- Date selection form -->
                <div class="date-selector">
                    <form action="/attendance-system/admin/journals" method="get">
                        <label for="date">Select Date:</label>
                        <input type="date" id="date" name="date" value="<?php echo $data['date']; ?>" onchange="this.form.submit()"> <!-- Auto-submits on change -->
                    </form>
                </div>
                
                <!-- Students list section -->
                <div class="students-list">
                    <?php if (empty($data['students'])): ?>
                        <!-- Message when no students found -->
                        <p>No students found.</p>
                    <?php else: ?>
                        <!-- Students table -->
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['students'] as $student): ?>
                                    <tr>
                                        <!-- Student name -->
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <!-- Student role -->
                                        <td><?php echo htmlspecialchars($student['role']); ?></td>
                                        <!-- Action button -->
                                        <td>
                                            <a href="/attendance-system/admin/journals?student_id=<?php echo $student['id']; ?>&date=<?php echo $data['date']; ?>" class="btn-sm">
                                                <!-- Dynamic button text based on journal existence -->
                                                <?php echo $student['has_journal'] ? 'View Journal & Attendance' : 'View Attendance'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
        
        <!-- Page footer -->
        <footer>
            <!-- Dynamic copyright year -->
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </footer>
    </div>
</body>
</html>