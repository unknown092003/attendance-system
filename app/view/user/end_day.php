<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML document setup -->
    <meta charset="UTF-8"> <!-- Character encoding for proper text display -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport settings -->
    <title>End Day - Attendance System</title> <!-- Page title shown in browser tab -->
    
    <!-- CSS includes -->
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css"> <!-- Main stylesheet -->
    
    <!-- Inline styles specific to this page -->
    <style>
        /* Main container styling */
        .end-day-container {
            max-width: 600px; /* Limits width for better readability */
            margin: 50px auto; /* Centers container vertically and horizontally */
            padding: 30px; /* Internal spacing */
            background-color: #fff; /* White background */
            border-radius: 5px; /* Rounded corners */
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); /* Subtle shadow for depth */
        }
        
        /* Form group styling for input sections */
        .form-group {
            margin-bottom: 20px; /* Space between form elements */
        }
        
        /* Label styling */
        .form-group label {
            display: block; /* Makes label take full width */
            margin-bottom: 5px; /* Space between label and input */
            font-weight: 600; /* Semi-bold text */
        }
        
        /* Input field styling */
        .form-control {
            width: 100%; /* Full width */
            padding: 10px; /* Internal spacing */
            border: 1px solid #ddd; /* Light gray border */
            border-radius: 4px; /* Slightly rounded corners */
            font-size: 16px; /* Comfortable reading size */
        }
        
        /* Textarea specific styling */
        textarea.form-control {
            min-height: 150px; /* Minimum height for journal entry */
            resize: vertical; /* Allows only vertical resizing */
        }
        
        /* Button container layout */
        .btn-container {
            display: flex; /* Enables flexible box layout */
            justify-content: space-between; /* Spaces buttons evenly */
            margin-top: 20px; /* Space above buttons */
        }
        
        /* Base button styling */
        .btn {
            padding: 10px 20px; /* Button size */
            border: none; /* Removes default border */
            border-radius: 4px; /* Rounded corners */
            cursor: pointer; /* Changes cursor to pointer */
            font-weight: 600; /* Semi-bold text */
        }
        
        /* Primary button (submit) styling */
        .btn-primary {
            background-color: #4CAF50; /* Green color */
            color: white; /* White text */
        }
        
        /* Secondary button (cancel) styling */
        .btn-secondary {
            background-color: #f44336; /* Red color */
            color: white; /* White text */
        }
        
        /* Hover effects */
        .btn-primary:hover {
            background-color: #45a049; /* Darker green on hover */
        }
        
        .btn-secondary:hover {
            background-color: #d32f2f; /* Darker red on hover */
        }
    </style>
</head>
<body>
    <!-- Main page container -->
    <div class="container">
        <!-- End day form container with custom styling -->
        <div class="end-day-container">
            <!-- Page heading -->
            <h2>End Your Day</h2>
            
            <!-- Instructions for user -->
            <p>Please write a brief journal about what you accomplished today before ending your day.</p>
            
            <!-- Form that submits to end-day endpoint -->
            <form action="/attendance-system/public/end-day" method="post">
                <!-- Journal textarea section -->
                <div class="form-group">
                    <label for="journal">Today's Journal</label>
                    <!-- Textarea for journal entry with placeholder instructions -->
                    <textarea id="journal" name="journal" class="form-control" required 
                              placeholder="Write about what you accomplished today, challenges you faced, and plans for tomorrow..."></textarea>
                </div>
                
                <!-- Button container with two action buttons -->
                <div class="btn-container">
                    <!-- Cancel button linking back to home -->
                    <a href="/attendance-system/public/home" class="btn btn-secondary">Cancel</a>
                    <!-- Submit button to end the day -->
                    <button type="submit" class="btn btn-primary">Submit & End Day</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>