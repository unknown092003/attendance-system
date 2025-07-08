// Profile page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile JS loaded');
    

    
    // Tab switching functionality
    const tabNav = document.querySelector('.tab-nav');
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanels = document.querySelectorAll('.tab-panel');
    
    console.log('Tab nav found:', tabNav);
    console.log('Tab buttons found:', tabButtons.length);
    console.log('Tab panels found:', tabPanels.length);
    
    // Add click handlers to tab buttons
    tabButtons.forEach(button => {
        console.log('Adding click handler to button:', button.textContent.trim());
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            console.log('Tab button clicked:', targetTab);

            
            // Remove active class from all buttons and panels
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => {
                panel.classList.remove('active');
                panel.style.display = 'none'; // Force hide
            });
            
            // Add active class to clicked button and target panel
            this.classList.add('active');
            const targetPanel = document.getElementById(targetTab);
            if (targetPanel) {
                targetPanel.classList.add('active');
                targetPanel.style.display = 'block'; // Force show
                console.log('Activated panel:', targetTab);
            } else {
                console.error('Target panel not found:', targetTab);
            }
            
            // Update the data-active-tab attribute on the nav for CSS animations
            if (tabNav) {
                if (targetTab === 'dtr-panel') {
                    tabNav.setAttribute('data-active-tab', 'dtr');
                } else if (targetTab === 'journal-panel') {
                    tabNav.setAttribute('data-active-tab', 'journal');
                }
            }
        });
    });
    
    // Profile edit modal functionality
    const profileEditBtn = document.getElementById('profileEditBtn');
    const profileEditModal = document.getElementById('editModal');
    const closeProfileModal = document.querySelector('.modal-close');
    const cancelProfileEdit = document.getElementById('cancelProfileEdit');
    const saveProfileEdit = document.getElementById('saveProfileEdit');
    
    console.log('Profile edit elements:', {
        btn: profileEditBtn,
        modal: profileEditModal,
        close: closeProfileModal,
        cancel: cancelProfileEdit,
        save: saveProfileEdit
    });
    
    if (profileEditBtn && profileEditModal) {
        // Open modal
        profileEditBtn.addEventListener('click', function() {
            console.log('Profile edit button clicked');
            profileEditModal.style.display = 'block';
        });
        
        // Close modal functions
        const closeModal = function() {
            profileEditModal.style.display = 'none';
        };
        
        if (closeProfileModal) closeProfileModal.addEventListener('click', closeModal);
        if (cancelProfileEdit) cancelProfileEdit.addEventListener('click', closeModal);
        
        // Close when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === profileEditModal) {
                closeModal();
            }
        });
        
        // Save profile changes - handle form submission
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Profile form submitted');
                
                const formData = new FormData(this);
                
                fetch('/attendance-system/profile/update', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Profile updated successfully!');
                        location.reload();
                    } else {
                        alert('Error updating profile: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the profile.');
                });
            });
        }
    }
    
    // Journal edit modal functionality
    const editJournalModal = document.getElementById('editJournalModal');
    const closeJournalModal = document.getElementById('closeJournalModal');
    const cancelJournalEdit = document.getElementById('cancelJournalEdit');
    const saveJournalEdit = document.getElementById('saveJournalEdit');
    
    if (editJournalModal) {
        const closeJournalModalFunc = function() {
            editJournalModal.style.display = 'none';
        };
        
        if (closeJournalModal) closeJournalModal.addEventListener('click', closeJournalModalFunc);
        if (cancelJournalEdit) cancelJournalEdit.addEventListener('click', closeJournalModalFunc);
        
        // Close when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === editJournalModal) {
                closeJournalModalFunc();
            }
        });
        
        // Save journal changes
        if (saveJournalEdit) {
            saveJournalEdit.addEventListener('click', function() {
                const form = document.getElementById('editJournalForm');
                if (form) {
                    const formData = new FormData(form);
                    
                    fetch('/attendance-system/api/journal/update', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Journal updated successfully!');
                            location.reload();
                        } else {
                            alert('Error updating journal: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the journal.');
                    });
                }
            });
        }
    }
    
    // Feedback modal functionality
    const feedbackModal = document.getElementById('feedbackModal');
    const closeFeedbackModal = document.getElementById('closeFeedbackModal');
    const cancelFeedback = document.getElementById('cancelFeedback');
    const saveFeedback = document.getElementById('saveFeedback');
    const deleteFeedback = document.getElementById('deleteFeedback');
    
    if (feedbackModal) {
        const closeFeedbackModalFunc = function() {
            feedbackModal.style.display = 'none';
            // Reset form
            const form = document.getElementById('feedbackForm');
            if (form) form.reset();
            // Hide delete button
            if (deleteFeedback) deleteFeedback.style.display = 'none';
        };
        
        if (closeFeedbackModal) closeFeedbackModal.addEventListener('click', closeFeedbackModalFunc);
        if (cancelFeedback) cancelFeedback.addEventListener('click', closeFeedbackModalFunc);
        
        // Close when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === feedbackModal) {
                closeFeedbackModalFunc();
            }
        });
        
        // Save feedback
        if (saveFeedback) {
            saveFeedback.addEventListener('click', function() {
                const form = document.getElementById('feedbackForm');
                if (form) {
                    const formData = new FormData(form);
                    
                    fetch('/attendance-system/api/feedback/save', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Feedback saved successfully!');
                            location.reload();
                        } else {
                            alert('Error saving feedback: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while saving feedback.');
                    });
                }
            });
        }
        
        // Delete feedback
        if (deleteFeedback) {
            deleteFeedback.addEventListener('click', function() {
                const journalId = document.getElementById('feedbackJournalId').value;
                
                if (confirm('Are you sure you want to delete this feedback?')) {
                    fetch('/attendance-system/api/feedback/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ journal_id: journalId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Feedback deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error deleting feedback: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting feedback.');
                    });
                }
            });
        }
    }
    
    // Add click handlers for edit journal buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-journal-btn')) {
            const journalId = e.target.getAttribute('data-journal-id');
            const journalText = e.target.getAttribute('data-journal-text');
            
            document.getElementById('editJournalId').value = journalId;
            document.getElementById('editJournalText').value = journalText;
            document.getElementById('editJournalModal').style.display = 'block';
        }
        
        // Add feedback button
        if (e.target.classList.contains('add-feedback-btn')) {
            const journalId = e.target.getAttribute('data-journal-id');
            document.getElementById('feedbackJournalId').value = journalId;
            document.getElementById('feedbackModalTitle').textContent = 'Add Feedback';
            document.getElementById('deleteFeedback').style.display = 'none';
            document.getElementById('feedbackModal').style.display = 'block';
        }
        
        // Edit feedback button
        if (e.target.classList.contains('edit-feedback-btn')) {
            const journalId = e.target.getAttribute('data-journal-id');
            const feedbackText = e.target.getAttribute('data-feedback-text');
            
            document.getElementById('feedbackJournalId').value = journalId;
            document.getElementById('feedbackText').value = feedbackText;
            document.getElementById('feedbackModalTitle').textContent = 'Edit Feedback';
            document.getElementById('deleteFeedback').style.display = 'inline-block';
            document.getElementById('feedbackModal').style.display = 'block';
        }
    });
    
    // Show all journals functionality
    const showAllJournalsBtn = document.getElementById('show-all-journals');
    const allJournalsContainer = document.getElementById('all-journals-container');
    const weeklyJournals = document.querySelector('.weekly-journals');
    
    if (showAllJournalsBtn && allJournalsContainer && weeklyJournals) {
        showAllJournalsBtn.addEventListener('click', function() {
            if (allJournalsContainer.style.display === 'none' || !allJournalsContainer.style.display) {
                allJournalsContainer.style.display = 'block';
                weeklyJournals.style.display = 'none';
                showAllJournalsBtn.textContent = 'Show Weekly View';
            } else {
                allJournalsContainer.style.display = 'none';
                weeklyJournals.style.display = 'block';
                showAllJournalsBtn.textContent = 'Show All Journals';
            }
        });
    }
    
    console.log('Profile JS initialization complete');
}); 