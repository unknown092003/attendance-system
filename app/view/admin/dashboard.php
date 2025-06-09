<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <main>
            <?php if (Session::has('_flash')): ?>
                <?php if (Session::getFlash('success')): ?>
                    <div class="alert success"><?php echo Session::getFlash('success'); ?></div>
                <?php endif; ?>
                <?php if (Session::getFlash('error')): ?>
                    <div class="alert error"><?php echo Session::getFlash('error'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <div class="stat-value"><?php echo $data['userCount']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Students Present Today</h3>
                    <div class="stat-value"><?php echo $data['todayAttendance']; ?></div>
                </div>
            </div>
            
            <div class="dashboard-section">
                <h2>Today's Student Attendance</h2>
                <div class="action-buttons">
                    <button id="special-attendance-btn" class="btn primary">Record Special Attendance</button>
                </div>
                <?php if (empty($data['studentsToday'])): ?>
                    <p>No students have checked in today.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['studentsToday'] as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['full_name'] ?? 'Unknown'); ?></td>
                                    <td>
                                        <?php if (!empty($student['first_time_in'])): ?>
                                            <?php echo date('h:i A', strtotime($student['first_time_in'])); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($student['last_time_out'])): ?>
                                            <?php echo date('h:i A', strtotime($student['last_time_out'])); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/attendance-system/admin/journals?student_id=<?php echo $student['user_id']; ?>&date=<?php echo $data['today']; ?>" class="btn-sm">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Special Attendance Modal -->
            <div id="special-attendance-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Record Special Attendance</h2>
                    <p>Select students who should receive special attendance (8:00 AM - 4:00 PM):</p>
                    
                    <div id="special-attendance-result" class="alert" style="display: none;"></div>
                    
                    <form id="special-attendance-form">
                        <div class="student-list">
                            <?php 
                            // Get users directly from database
                            $config = require __DIR__ . '/../../../config/database.php';
                            $db = new PDO(
                                "mysql:host={$config['host']};dbname={$config['dbname']}",
                                $config['username'],
                                $config['password'],
                                $config['options']
                            );
                            
                            // Get today's date
                            $today = date('Y-m-d');
                            
                            // Get all students
                            $stmt = $db->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
                            $allStudents = $stmt->fetchAll();
                            
                            // Get students who already have attendance today
                            $stmt = $db->prepare("
                                SELECT DISTINCT user_id 
                                FROM attendance 
                                WHERE DATE(time_in) = ?
                            ");
                            $stmt->execute([$today]);
                            $presentStudentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            // Filter out students who are already present
                            $absentStudents = [];
                            foreach ($allStudents as $student) {
                                if (!in_array($student['id'], $presentStudentIds)) {
                                    $absentStudents[] = $student;
                                }
                            }
                            
                            if (empty($absentStudents)) {
                                echo '<p>All students are already present today.</p>';
                            } else {
                                foreach ($absentStudents as $student): ?>
                                    <div class="student-item">
                                        <input type="checkbox" name="students[]" id="student-<?php echo $student['id']; ?>" value="<?php echo $student['id']; ?>">
                                        <label for="student-<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></label>
                                    </div>
                                <?php endforeach;
                            } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="attendance-date">Date:</label>
                            <input type="date" id="attendance-date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <input type="hidden" name="hours" value="8">
                        
                        <div class="btn-container">
                            <button type="button" id="cancel-special-attendance" class="btn secondary">Cancel</button>
                            <button type="submit" id="submit-special-attendance" class="btn primary">Record Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="dashboard-section">
                <h2>Internship Progress</h2>
                <?php if (empty($data['internProgress'])): ?>
                    <p>No interns found with required hours.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Required Hours</th>
                                <th>Completed Hours</th>
                                <th>Remaining Hours</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['internProgress'] as $intern): 
                                $completedHours = (float)$intern['completed_hours'];
                                $requiredHours = (float)$intern['required_hours'];
                                $remainingHours = max(0, $requiredHours - $completedHours);
                                $percentage = $requiredHours > 0 ? min(100, ($completedHours / $requiredHours) * 100) : 0;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($intern['full_name']); ?></td>
                                    <td><?php echo number_format($requiredHours, 2); ?> hrs</td>
                                    <td><?php echo number_format($completedHours, 2); ?> hrs</td>
                                    <td><?php echo number_format($remainingHours, 2); ?> hrs</td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress" style="width: <?php echo $percentage; ?>%"></div>
                                            <span><?php echo number_format($percentage, 1); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </footer>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Special Attendance Modal functionality
            const specialAttendanceBtn = document.getElementById('special-attendance-btn');
            const specialAttendanceModal = document.getElementById('special-attendance-modal');
            const closeBtn = document.querySelector('#special-attendance-modal .close');
            const cancelBtn = document.getElementById('cancel-special-attendance');
            const resultDiv = document.getElementById('special-attendance-result');
            const form = document.getElementById('special-attendance-form');
            
            if (specialAttendanceBtn && specialAttendanceModal) {
                // Open modal when clicking the button
                specialAttendanceBtn.addEventListener('click', function() {
                    specialAttendanceModal.style.display = 'block';
                    resultDiv.style.display = 'none';
                });
                
                // Close modal when clicking X
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        specialAttendanceModal.style.display = 'none';
                    });
                }
                
                // Close modal when clicking Cancel
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function() {
                        specialAttendanceModal.style.display = 'none';
                    });
                }
                
                // Close modal when clicking outside
                window.addEventListener('click', function(event) {
                    if (event.target === specialAttendanceModal) {
                        specialAttendanceModal.style.display = 'none';
                    }
                });
                
                // Form submission
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Check if at least one student is selected
                        const selectedStudents = document.querySelectorAll('input[name="students[]"]:checked');
                        if (selectedStudents.length === 0) {
                            alert('Please select at least one student.');
                            return;
                        }
                        
                        // Create form data
                        const formData = new FormData(form);
                        
                        // Submit via fetch
                        fetch('/attendance-system/admin/special-attendance', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                resultDiv.className = 'alert success';
                                resultDiv.textContent = 'Special attendance recorded successfully.';
                                resultDiv.style.display = 'block';
                                
                                // Clear checkboxes
                                selectedStudents.forEach(checkbox => {
                                    checkbox.checked = false;
                                });
                                
                                // Reload the page after a short delay
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                resultDiv.className = 'alert error';
                                resultDiv.textContent = data.message || 'Failed to record special attendance.';
                                resultDiv.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            resultDiv.className = 'alert error';
                            resultDiv.textContent = 'An error occurred. Please try again.';
                            resultDiv.style.display = 'block';
                        });
                    });
                }
            }
        });
    </script>
</body>
</html>