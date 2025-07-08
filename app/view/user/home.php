<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System - Home</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <link rel="stylesheet" href="/attendance-system/assets/css/home.css">
<style>
    /* Modern, clean header styles */
    .ocd_header {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
    }
    .ocd_header img {
        height: 40px;
        width: auto;
        margin-right: 1rem;
    }
    .header-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 1rem;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .user-info strong {
        font-size: 1rem;
        color: #222;
        margin-right: 0.5rem;
    }
    .profile-btn, .logout-btn {
        padding: 0.4em 1em;
        border: none;
        border-radius: 20px;
        background: #f5f5f5;
        color: #333;
        text-decoration: none;
        font-size: 0.95rem;
        transition: background 0.2s, color 0.2s;
    }
    .profile-btn:hover, .logout-btn:hover {
        background: #007bff;
        color: #fff;
    }
    @media (max-width: 600px) {
        .header-main {
            flex-direction: column;
            align-items: flex-start;
            padding: 0.75rem 0.5rem;
        }
        .ocd_header {
            margin-bottom: 0.5rem;
            padding: 0;
        }
        .user-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.3rem;
            width: 100%;
        }
        .profile-btn, .logout-btn {
            width: 100%;
            margin-bottom: 0.3rem;
            font-size: 1rem;
        }
    }
    header {
        position: relative;
        width: 100%;
        height: 270px;
        background: url('/attendance-system/assets/img/ocd.png') center center/cover no-repeat;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.90);
        z-index: 1;
    }
    .profile-header {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 0;
        width: 100%;
        max-width: none;
        z-index: 2;
    }
    .profile-header .profile-img {
        width: 80px;
        height: 80px;
        margin-bottom: 0.8rem;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #007bff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        background: #f5f5f5;
    }
    .profile-header .divider {
        width: 36px;
        height: 2px;
        margin-bottom: 0.7rem;
    }
    .profile-header .user-name {
        font-size: 1.5rem;
        color: orange;
        margin-bottom: 1.1rem;
    }
    .profile-header .profile-actions {
        gap: 0.6rem;
    }
    .profile-header .profile-btn, .profile-header .logout-btn {
        padding: 0.35em 1.1em;
        font-size: 0.95rem;
    }
    @media (max-width: 600px) {
        header {
            height: 170px;
        }
        .profile-header .profile-img {
            width: 60px;
            height: 60px;
        }
        .profile-header .divider {
            width: 24px;
            height: 2px;
            margin-bottom: 0.5rem;
        }
        .profile-header .user-name {
            font-size: 1.1rem;
        }
        .profile-header .profile-actions {
            gap: 0.5rem;
        }
        .profile-header .profile-btn, .profile-header .logout-btn {
            font-size: 0.85rem;
            padding: 0.25em 0.7em;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <header>
            <div class="profile-header">
                <?php
                    $avatar = $data['user']['avatar'] ?? '';
                    $profileImg = (!empty($avatar) && $avatar !== 'default.jpg')
                        ? '/attendance-system/assets/uploads/avatars/' . htmlspecialchars($avatar)
                        : '/attendance-system/assets/img/ocd.png';
                ?>
                <img src="<?php echo $profileImg; ?>" alt="Profile Logo" class="profile-img">
                <div class="divider"></div>
                <div class="user-name">
                    <?php echo htmlspecialchars($data['user']['full_name'] ?? 'User'); ?>
                </div>
                <div class="profile-actions">
                    <a href="/attendance-system/profile" class="profile-btn">Profile</a>
                    <a href="/attendance-system/logout" class="logout-btn">Logout</a>
                </div>
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
        
        <!-- Page footer -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> erroljohnpardillo@gmail.com </p>
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