<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css">
</head>
<body>
    <div class="container">
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <main>
            <div class="page-header">
                <h2>Attendance Reports</h2>
            </div>
            
            <div class="report-section">
                <h3>Monthly Attendance Summary</h3>
                <?php if (empty($data['monthly'])): ?>
                    <p>No attendance data available for this month.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Days Present</th>
                                <th>Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $daysInMonth = date('t');
                                foreach ($data['monthly'] as $record): 
                                $attendanceRate = ($record['days_present'] / $daysInMonth) * 100;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                                    <td><?php echo $record['days_present']; ?> / <?php echo $daysInMonth; ?></td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress" style="width: <?php echo $attendanceRate; ?>%"></div>
                                            <span><?php echo number_format($attendanceRate, 1); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="report-section">
                <h3>Student Details</h3>
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
                                        <a href="/attendance-system/public/admin/journals?student_id=<?php echo $student['id']; ?>" class="btn-sm">View Journals</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
        
        <!-- Page footer -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> erroljohnpardillo@gmail.com </p>
        </footer>
    </div>
</body>
</html>