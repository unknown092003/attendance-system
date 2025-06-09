// JavaScript for admin pages
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin JS loaded');
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
    
    // Special Attendance Modal functionality
    const specialAttendanceBtn = document.getElementById('special-attendance-btn');
    const specialAttendanceModal = document.getElementById('special-attendance-modal');
    
    console.log('Button:', specialAttendanceBtn);
    console.log('Modal:', specialAttendanceModal);
    
    if (specialAttendanceBtn && specialAttendanceModal) {
        // Open modal when clicking the button
        specialAttendanceBtn.addEventListener('click', function() {
            console.log('Button clicked');
            specialAttendanceModal.style.display = 'block';
        });
        
        // Close modal when clicking X
        const closeBtn = document.querySelector('#special-attendance-modal .close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                specialAttendanceModal.style.display = 'none';
            });
        }
        
        // Close modal when clicking Cancel
        const cancelBtn = document.getElementById('cancel-special-attendance');
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
        const specialAttendanceForm = document.getElementById('special-attendance-form');
        if (specialAttendanceForm) {
            specialAttendanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Check if at least one student is selected
                const selectedStudents = document.querySelectorAll('input[name="students[]"]:checked');
                if (selectedStudents.length === 0) {
                    alert('Please select at least one student.');
                    return;
                }
                
                // Submit the form via AJAX
                const formData = new FormData(specialAttendanceForm);
                
                fetch(specialAttendanceForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        specialAttendanceModal.style.display = 'none';
                        
                        // Show success message
                        const successAlert = document.createElement('div');
                        successAlert.className = 'alert success';
                        successAlert.textContent = 'Special attendance recorded successfully.';
                        document.querySelector('main').insertBefore(successAlert, document.querySelector('main').firstChild);
                        
                        // Auto-hide the alert
                        setTimeout(function() {
                            successAlert.style.opacity = '0';
                            setTimeout(function() {
                                successAlert.remove();
                            }, 500);
                        }, 5000);
                        
                        // Reload the page after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        alert(data.message || 'Failed to record special attendance. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        }
    }
});