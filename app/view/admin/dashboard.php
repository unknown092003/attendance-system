<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML document setup -->
    <meta charset="UTF-8"> <!-- Character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport settings -->
    <title>Admin Dashboard - Attendance System</title> <!-- Page title -->
    
    <!-- CSS stylesheets -->
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css"> <!-- Main stylesheet -->
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css"> <!-- Admin-specific styles -->
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Main container -->
    <div class="container">
        <!-- Include admin header partial -->
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <!-- Main content area -->
        <main>
            <!-- Flash message display section -->
            <?php if (Session::has('_flash')): ?>
                <!-- Success message -->
                <?php if (Session::getFlash('success')): ?>
                    <div class="alert success"><?php echo Session::getFlash('success'); ?></div>
                <?php endif; ?>
                <!-- Error message -->
                <?php if (Session::getFlash('error')): ?>
                    <div class="alert error"><?php echo Session::getFlash('error'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Dashboard statistics cards -->
            <div class="dashboard-stats">
                <!-- Total students card -->
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <div class="stat-value"><?php echo $data['userCount']; ?></div>
                </div>
                <!-- Today's attendance card -->
                <div class="stat-card">
                    <h3>Students Present Today</h3>
                    <div class="stat-value"><?php echo $data['todayAttendance']; ?></div>
                </div>
            </div>
            
            <!-- Today's attendance section -->
            <div class="dashboard-section">
                <h2>Today's Student Attendance</h2>
                <!-- Special attendance button -->
                <div class="action-buttons">
                    <button id="special-attendance-btn" class="btn primary">Record Special Attendance</button>
                </div>
                
                <?php if (empty($data['studentsToday'])): ?>
                    <!-- Message when no attendance records -->
                    <p>No students have checked in today.</p>
                <?php else: ?>
                    <!-- Attendance table -->
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
                            <!-- Loop through today's attendance records -->
                            <?php foreach ($data['studentsToday'] as $student): ?>
                                <tr>
                                    <!-- Student name -->
                                    <td><?php echo htmlspecialchars($student['full_name'] ?? 'Unknown'); ?></td>
                                    <!-- Time in (formatted) -->
                                    <td>
                                        <?php if (!empty($student['first_time_in'])): ?>
                                            <?php echo date('h:i A', strtotime($student['first_time_in'])); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <!-- Time out (formatted) -->
                                    <td>
                                        <?php if (!empty($student['last_time_out'])): ?>
                                            <?php echo date('h:i A', strtotime($student['last_time_out'])); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <!-- View details button -->
                                    <td>
                                        <a href="/attendance-system/admin/journals?student_id=<?php echo $student['user_id']; ?>&date=<?php echo $data['today']; ?>" class="btn-sm">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Special Attendance Modal (hidden by default) -->
            <div id="special-attendance-modal" class="modal">
                <div class="modal-content">
                    <!-- Close button -->
                    <span class="close">&times;</span>
                    <h2>Record Special Attendance</h2>
                    <p>Select students who should receive special attendance (8:00 AM - 4:00 PM):</p>
                    
                    <!-- Result message container -->
                    <div id="special-attendance-result" class="alert" style="display: none;"></div>
                    
                    <!-- Special attendance form -->
                    <form id="special-attendance-form">
                        <div class="student-list">
                            <?php 
                            // Database connection setup
                            $config = require __DIR__ . '/../../../config/database.php';
                            $db = new PDO(
                                "mysql:host={$config['host']};dbname={$config['dbname']}",
                                $config['username'],
                                $config['password'],
                                $config['options']
                            );
                            
                            // Get today's date
                            $today = date('Y-m-d');
                            
                            // Query all students
                            $stmt = $db->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
                            $allStudents = $stmt->fetchAll();
                            
                            // Query students with attendance today
                            $stmt = $db->prepare("
                                SELECT DISTINCT user_id 
                                FROM attendance 
                                WHERE DATE(time_in) = ?
                            ");
                            $stmt->execute([$today]);
                            $presentStudentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            // Filter to only show absent students
                            $absentStudents = [];
                            foreach ($allStudents as $student) {
                                if (!in_array($student['id'], $presentStudentIds)) {
                                    $absentStudents[] = $student;
                                }
                            }
                            
                            if (empty($absentStudents)) {
                                echo '<p>All students are already present today.</p>';
                            } else {
                                // Display checkboxes for absent students
                                foreach ($absentStudents as $student): ?>
                                    <div class="student-item">
                                        <input type="checkbox" name="students[]" id="student-<?php echo $student['id']; ?>" value="<?php echo $student['id']; ?>">
                                        <label for="student-<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></label>
                                    </div>
                                <?php endforeach;
                            } ?>
                        </div>
                        
                        <!-- Date selection -->
                        <div class="form-group">
                            <label for="attendance-date">Date:</label>
                            <input type="date" id="attendance-date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <!-- Hidden hours field (default 8 hours) -->
                        <input type="hidden" name="hours" value="8">
                        
                        <!-- Form buttons -->
                        <div class="btn-container">
                            <button type="button" id="cancel-special-attendance" class="btn secondary">Cancel</button>
                            <button type="submit" id="submit-special-attendance" class="btn primary">Record Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Internship progress section -->
            <div class="dashboard-section">
                <h2>Internship Progress</h2>
                <?php if (empty($data['internProgress'])): ?>
                    <p>No interns found with required hours.</p>
                <?php else: ?>
                    <!-- Internship progress table -->
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
                            <!-- Loop through intern progress data -->
                            <?php foreach ($data['internProgress'] as $intern): 
                                $completedHours = (float)$intern['completed_hours'];
                                $requiredHours = (float)$intern['required_hours'];
                                $remainingHours = max(0, $requiredHours - $completedHours);
                                $percentage = $requiredHours > 0 ? min(100, ($completedHours / $requiredHours) * 100) : 0;
                            ?>
                                <tr>
                                    <!-- Intern details -->
                                    <td><?php echo htmlspecialchars($intern['full_name']); ?></td>
                                    <td><?php echo number_format($requiredHours, 2); ?> hrs</td>
                                    <td><?php echo number_format($completedHours, 2); ?> hrs</td>
                                    <td><?php echo number_format($remainingHours, 2); ?> hrs</td>
                                    <!-- Progress bar -->
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
        
        <!-- Page footer -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </footer>
    </div>
    
    <!-- JavaScript for interactive elements -->
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
                // Open modal when button clicked
                specialAttendanceBtn.addEventListener('click', function() {
                    specialAttendanceModal.style.display = 'block';
                    resultDiv.style.display = 'none';
                });
                
                // Close modal when X clicked
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        specialAttendanceModal.style.display = 'none';
                    });
                }
                
                // Close modal when Cancel clicked
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
                
                // Form submission handling
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Validate at least one student selected
                        const selectedStudents = document.querySelectorAll('input[name="students[]"]:checked');
                        if (selectedStudents.length === 0) {
                            alert('Please select at least one student.');
                            return;
                        }
                        
                        // Prepare form data
                        const formData = new FormData(form);
                        
                        // Submit via AJAX
                        fetch('/attendance-system/admin/special-attendance', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Success message
                                resultDiv.className = 'alert success';
                                resultDiv.textContent = 'Special attendance recorded successfully.';
                                resultDiv.style.display = 'block';
                                
                                // Clear selections
                                selectedStudents.forEach(checkbox => {
                                    checkbox.checked = false;
                                });
                                
                                // Reload page after delay
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                // Error message
                                resultDiv.className = 'alert error';
                                resultDiv.textContent = data.message || 'Failed to record special attendance.';
                                resultDiv.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            // Network error message
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