<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>End Day - Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/assets/css/style.css">
    <style>
        .end-day-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-secondary {
            background-color: #f44336;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #45a049;
        }
        
        .btn-secondary:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="end-day-container">
            <h2>End Your Day</h2>
            <p>Please write a brief journal about what you accomplished today before ending your day.</p>
            
            <form action="/attendance-system/public/end-day" method="post">
                <div class="form-group">
                    <label for="journal">Today's Journal</label>
                    <textarea id="journal" name="journal" class="form-control" required placeholder="Write about what you accomplished today, challenges you faced, and plans for tomorrow..."></textarea>
                </div>
                
                <div class="btn-container">
                    <a href="/attendance-system/public/home" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit & End Day</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>