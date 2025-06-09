<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Journals - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css">
</head>
<body>
    <div class="container">
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Student Journals</h2>
            </div>
            
            <?php if (isset($data['student'])): ?>
                <!-- Single student journal view -->
                <div class="journal-header">
                    <h3><?php echo htmlspecialchars($data['student']['full_name']); ?>'s Journal</h3>
                    <div class="journal-date">
                        <form action="/attendance-system/admin/journals" method="get">
                            <input type="hidden" name="student_id" value="<?php echo $data['student']['id']; ?>">
                            <input type="date" name="date" value="<?php echo $data['date']; ?>" onchange="this.form.submit()">
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($data['journal'])): ?>
                    <div class="journal-entry">
                        <div class="journal-content">
                            <?php echo nl2br(htmlspecialchars($data['journal']['journal_text'])); ?>
                        </div>
                        <div class="journal-meta">
                            <p>Submitted: <?php echo date('M d, Y h:i A', strtotime($data['journal']['created_at'])); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert info">No journal entry found for this date.</div>
                <?php endif; ?>
                
                <div class="attendance-summary">
                    <h4>Attendance for <?php echo date('M d, Y', strtotime($data['date'])); ?></h4>
                    <?php if (empty($data['attendance'])): ?>
                        <p>No attendance records for this date.</p>
                    <?php else: ?>
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
                                $totalHours = 0;
                                foreach ($data['attendance'] as $record): 
                                    $duration = 0;
                                    if (!empty($record['time_out'])) {
                                        $timeIn = new DateTime($record['time_in']);
                                        $timeOut = new DateTime($record['time_out']);
                                        $duration = ($timeOut->getTimestamp() - $timeIn->getTimestamp()) / 3600;
                                        $totalHours += $duration;
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo date('h:i A', strtotime($record['time_in'])); ?></td>
                                        <td>
                                            <?php if (!empty($record['time_out'])): ?>
                                                <?php echo date('h:i A', strtotime($record['time_out'])); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($record['time_out'])): ?>
                                                <?php 
                                                    $timeIn = new DateTime($record['time_in']);
                                                    $timeOut = new DateTime($record['time_out']);
                                                    $interval = $timeIn->diff($timeOut);
                                                    echo $interval->format('%h hrs %i mins');
                                                ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="2" class="text-right"><strong>Total Hours:</strong></td>
                                    <td><strong><?php echo number_format($totalHours, 2); ?> hrs</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="back-link">
                    <a href="/attendance-system/admin/journals" class="btn secondary">Back to All Students</a>
                </div>
            <?php else: ?>
                <!-- List of students -->
                <div class="date-selector">
                    <form action="/attendance-system/admin/journals" method="get">
                        <label for="date">Select Date:</label>
                        <input type="date" id="date" name="date" value="<?php echo $data['date']; ?>" onchange="this.form.submit()">
                    </form>
                </div>
                
                <div class="students-list">
                    <?php if (empty($data['students'])): ?>
                        <p>No students found.</p>
                    <?php else: ?>
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
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['role']); ?></td>
                                        <td>
                                            <a href="/attendance-system/admin/journals?student_id=<?php echo $student['id']; ?>&date=<?php echo $data['date']; ?>" class="btn-sm">
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
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </footer>
    </div>
</body>
</html>
