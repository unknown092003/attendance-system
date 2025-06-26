<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System - Home</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <link rel="stylesheet" href="/attendance-system/assets/css/home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Attendance System</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($data['user']['full_name'] ?? 'User'); ?></span>
                <a href="/attendance-system/profile" class="profile-btn">Profile</a>
                <a href="/attendance-system/logout" class="logout-btn">Logout</a>
            </div>
        </header>
        
        <main>
            <?php if (Session::has('_flash')): ?>
                <?php if (Session::getFlash('success')): ?>
                    <div class="alert success"><?php echo Session::getFlash('success'); ?></div>
                <?php endif; ?>
                <?php if (Session::getFlash('error')): ?>
                    <div class="alert error"><?php echo Session::getFlash('error'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Hidden inputs for JavaScript -->
            <input type="hidden" id="has-active-session" value="<?php echo $data['activeSession'] ? 'true' : 'false'; ?>">
            <input type="hidden" id="fresh-login" value="<?php echo Session::get('fresh_login') ? 'true' : 'false'; ?>">
            <?php Session::remove('fresh_login'); // Clear the flag after use ?>
            <input type="hidden" id="has-journal" value="<?php echo !empty($data['journal']) ? 'true' : 'false'; ?>">
            <input type="hidden" id="day-ended" value="<?php echo Session::get('day_ended') ? 'true' : 'false'; ?>">
            <?php Session::remove('day_ended'); // Clear the flag after use ?>
            <input type="hidden" id="last-end-day" value="<?php echo $data['last_end_day']; ?>">
            <?php if (isset($data['activeAttendance']) && $data['activeAttendance']): ?>
            <input type="hidden" id="active-time-in" value="<?php echo $data['activeAttendance']['time_in']; ?>">
            <?php endif; ?>
            
            <div class="attendance-actions">
                <h2>Today's Attendance</h2>
                
                <!-- Stopwatch Display -->
                <div class="stopwatch-container">
                    <div id="stopwatch">00:00:00</div>
                    <div id="stopwatch-status">Not active</div>
                </div>
                
                <div class="action-buttons">
                    <button id="time-in-btn" class="btn primary" <?php echo $data['activeSession'] ? 'disabled' : ''; ?>>Time In</button>
                    <button id="time-out-btn" class="btn secondary" <?php echo !$data['activeSession'] ? 'disabled' : ''; ?>>Time Out</button>
                    <button id="end-day-btn" class="btn danger">End Day</button>
                </div>
            </div>
            
            <?php if (!empty($data['user']['required_hours'])): ?>
            <div class="internship-progress">
                <h2>Internship Progress</h2>
                <div class="progress-info">
                    <div class="progress-item">
                        <span class="label">Required Hours:</span>
                        <span class="value"><?php echo number_format($data['user']['required_hours'], 2); ?> hrs</span>
                    </div>
                    <div class="progress-item">
                        <span class="label">Completed Hours:</span>
                        <span class="value"><?php echo number_format($data['total_hours_completed'], 2); ?> hrs</span>
                    </div>
                    <div class="progress-item">
                        <span class="label">Remaining Hours:</span>
                        <span class="value"><?php echo number_format($data['remaining_hours'], 2); ?> hrs</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <?php $percentage = min(100, ($data['total_hours_completed'] / $data['user']['required_hours']) * 100); ?>
                    <div class="progress" style="width: <?php echo $percentage; ?>%"></div>
                </div>
                <div class="progress-text">
                    <?php echo number_format($percentage, 1); ?>% Complete
                </div>
            </div>
            <?php endif; ?>
            
            <div class="attendance-records">
                <h2>Today's Records</h2>
                <?php if (empty($data['today'])): ?>
                    <p>No attendance records for today.</p>
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
                            <?php foreach ($data['today'] as $record): ?>
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
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($data['journal'])): ?>
            <div class="journal-entry">
                <h2>Today's Journal</h2>
                <div class="journal-content">
                    <?php echo nl2br(htmlspecialchars($data['journal']['journal_text'])); ?>
                </div>
                <button id="edit-journal-btn" class="btn primary">Edit Journal</button>
            </div>
            <?php endif; ?>
            
            <div class="monthly-summary">
                <?php 
                    $days = [];
                    $totalHours = 0;
                    
                    if (!empty($data['month'])) {
                        foreach ($data['month'] as $record) {
                            $date = date('Y-m-d', strtotime($record['time_in']));
                            if (!isset($days[$date])) {
                                $days[$date] = 0;
                            }
                            
                            if (!empty($record['time_out'])) {
                                $timeIn = new DateTime($record['time_in']);
                                $timeOut = new DateTime($record['time_out']);
                                $hours = ($timeOut->getTimestamp() - $timeIn->getTimestamp()) / 3600;
                                $days[$date] += $hours;
                                $totalHours += $hours;
                            }
                        }
                    }
                ?>
                <div class="monthly-total">
                    <h2>Monthly Summary </h2>
                    <h4> Total: <?php echo number_format($totalHours, 2); ?> hrs</h2>
                </div>
                
                <?php if (empty($data['month'])): ?>
                    <p>No attendance records for this month.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($days as $date => $hours):
                            ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($date)); ?></td>
                                    <td><?php echo number_format($hours, 2); ?> hrs</td>
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
    
    <!-- Journal Modal -->
    <div id="journal-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="journal-modal-title">End Your Day</h2>
            <p id="journal-modal-description">Please write a brief journal about what you accomplished today before ending your day.</p>
            
            <div class="form-group">
                <label for="journal-text">Today's Journal</label>
                <textarea id="journal-text" class="form-control" required placeholder="Write about what you accomplished today, challenges you faced, and plans for tomorrow..."></textarea>
            </div>
            
            <div class="btn-container">
                <button id="cancel-journal" class="btn secondary">Cancel</button>
                <button id="submit-journal" class="btn primary">Submit</button>
            </div>
        </div>
    </div>
    
    <script src="/attendance-system/assets/js/home.js"></script>
</body>
</html>