<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Document metadata and settings -->
    <meta charset="UTF-8"> <!-- Sets character encoding to UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Makes page responsive on mobile devices -->
    <title>Manage Users - Attendance System</title> <!-- Page title shown in browser tab -->
    
    <!-- CSS stylesheets -->
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css"> <!-- Main stylesheet -->
    <link rel="stylesheet" href="/attendance-system/assets/css/admin.css"> <!-- Admin-specific styles -->
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Main page container -->
    <div class="container">
        <!-- Include admin header navigation -->
        <?php include APP_PATH . '/view/admin/partials/header.php'; ?>
        
        <!-- Main content area -->
        <main>
            <!-- Flash message display section -->
            <?php if (Session::has('_flash')): ?>
                <!-- Success message display -->
                <?php if (Session::getFlash('success')): ?>
                    <div class="alert success"><?php echo Session::getFlash('success'); ?></div>
                <?php endif; ?>
                <!-- Error message display -->
                <?php if (Session::getFlash('error')): ?>
                    <div class="alert error"><?php echo Session::getFlash('error'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Page header section with title and create button -->
            <div class="page-header">
                <h2>Manage Users</h2> <!-- Page title -->
                <!-- Button to create new user -->
                <a href="/attendance-system/admin/users/create" class="btn primary">Create New User</a>
            </div>
            
            <!-- Active Users list section -->
            <div class="users-list">
                <h3>Active Users</h3>
                <?php if (empty($data['activeUsers'])): ?>
                    <p>No active users found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>PIN</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['activeUsers'] as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['pin']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'primary' : ($user['role'] === 'intern' ? 'info' : 'secondary'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/attendance-system/admin/profile?student_id=<?php echo $user['id']; ?>" class="btn-sm btn-info mr-2">View Profile</a>
                                        <button class="btn-sm primary" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo $user['status'] ?? 'active'; ?>', '<?php echo $user['pin'] ?? ''; ?>', '<?php echo $user['moa'] ?? 0; ?>')">Edit Status</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Inactive Users list section -->
            <div class="users-list" style="margin-top: 2rem;">
                <h3>Inactive Users</h3>
                <?php if (empty($data['inactiveUsers'])): ?>
                    <p>No inactive users found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>PIN</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['inactiveUsers'] as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['pin']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'primary' : ($user['role'] === 'intern' ? 'info' : 'secondary'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/attendance-system/admin/profile?student_id=<?php echo $user['id']; ?>" class="btn-sm btn-info mr-2">View Profile</a>
                                        <button class="btn-sm primary" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo $user['status'] ?? 'active'; ?>', '<?php echo $user['pin'] ?? ''; ?>', '<?php echo $user['moa'] ?? 0; ?>')">Edit Status</button>
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
            <!-- Dynamic copyright year -->
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </footer>
    </div>

    <!-- Edit Status Modal -->
    <div id="editModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div class="modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #0a2744; padding: 20px; border-radius: 8px; width: 400px;">
            <h3 >Edit Intern Status</h3>
            <form id="editStatusForm">
                <input type="hidden" id="editUserId">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label  >Intern Name:</label>
                    <span id="editUserName" style="font-weight:"></span>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label  for="editStatus">Status:</label>
                    <select id="editStatus" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label  for="editMoa">MOA Status:</label>
                    <select id="editMoa" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="1">Signed</option>
                        <option value="0">Not Signed</option>
                    </select>
                </div>
                <div class="form-group" id="pinGroup" style="margin-bottom: 15px; display: none;">
                    <label>PIN</label>
                    <p style="color: #666; font-size: 14px; margin: 0;">A unique 4-digit PIN will be automatically generated when status is set to active.</p>
                </div>
                <div class="form-actions" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                    <button type="button" id="deleteUserBtn" class="btn danger">Delete Intern</button>
                    <div>
                        <button type="button" onclick="closeEditModal()" class="btn secondary" style="margin-right: 10px;">Cancel</button>
                        <button type="submit" class="btn primary">Update Status</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(userId, userName, status, pin, moa) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUserName').textContent = userName;
            document.getElementById('editStatus').value = status;
            document.getElementById('editMoa').value = moa;
            document.getElementById('editModal').style.display = 'block';
            
            // Show/hide PIN field based on current status
            togglePinField(status);
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editStatusForm').reset();
        }

        function togglePinField(status) {
            const pinGroup = document.getElementById('pinGroup');
            
            if (status === 'inactive') {
                pinGroup.style.display = 'none';
            } else {
                pinGroup.style.display = 'block';
            }
        }

        // Handle status change
        document.getElementById('editStatus').addEventListener('change', function() {
            togglePinField(this.value);
        });

        // Handle form submission
        document.getElementById('editStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('editUserId').value;
            const status = document.getElementById('editStatus').value;
            const moa = document.getElementById('editMoa').value;
            
            console.log('Submitting:', { userId, status, moa }); // Debug log
            
            // Submit the form
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('status', status);
            formData.append('moa', moa);
            
            fetch('/attendance-system/admin/users/update-status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating status.');
            });
        });

        // Handle delete button click
        document.getElementById('deleteUserBtn').addEventListener('click', function() {
            const userId = document.getElementById('editUserId').value;
            const userName = document.getElementById('editUserName').textContent;

            if (confirm(`Are you sure you want to delete the intern "${userName}"? This action cannot be undone.`)) {
                const formData = new FormData();
                formData.append('user_id', userId);

                fetch('/attendance-system/admin/users/delete', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check for a redirect response to get the flash message
                    if (response.ok) {
                        alert('User deleted successfully!');
                        location.reload();
                    } else {
                        alert('Failed to delete user.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the user.');
                });
            }
        });

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
