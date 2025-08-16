<?php
require_once 'config.php';
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', 'root', 'employee_attendance_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation']);
    $contact = trim($_POST['contact']);
    $instagram = trim($_POST['instagram']);
    
    // Generate employee ID
    $employee_id = 'EMP' . strtoupper(bin2hex(random_bytes(4)));
    
    // Handle photo upload
    $photo_path = '';
    $upload_ok = false;
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $check = getimagesize($_FILES['photo']['tmp_name']);
        if ($check !== false) {
            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $filename = $employee_id . '.' . $file_ext;
                $target_file = $target_dir . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                    $photo_path = $target_file;
                    $upload_ok = true;
                }
            }
        }
    }
    
    if ($upload_ok) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO employees (employee_id, full_name, designation, contact, instagram, photo_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $employee_id, $name, $designation, $contact, $instagram, $photo_path);
        
        if ($stmt->execute()) {
            header("Location: id_card.php?id=" . $employee_id);
            exit();
        } else {
            $error = "Database error. Please try again.";
            if (file_exists($target_file)) {
                unlink($target_file);
            }
        }
    } else {
        $error = "Please upload a valid image file";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .photo-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #007bff;
            border-radius: 5px;
            margin: 0 auto 20px;
            overflow: hidden;
            background: #f8f9fa;
        }
        #imagePreview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .form-label {
            font-weight: 600;
            color: #343a40;
        }
        .input-group-text {
            background-color: #f8f9fa;
        }
        .contact-input {
            position: relative;
        }
        .contact-icon {
            position: absolute;
            left: 10px;
            top: 10px;
            color: #25D366;
            z-index: 10;
        }
        .instagram-input {
            position: relative;
        }
        .instagram-icon {
            position: absolute;
            left: 10px;
            top: 10px;
            color: #E1306C;
            z-index: 10;
        }
        .input-with-icon {
            padding-left: 35px;
        }
    </style>
</head>
<body>
    <div class="container form-container">
        <h2 class="text-center mb-4" style="color: #007bff;">Add New Member</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4 text-center">
                <div class="photo-preview">
                    <img id="imagePreview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f8f9fa'/%3E%3Ctext x='100' y='110' font-family='Arial' font-size='16' fill='%23007bff' text-anchor='middle'%3EUpload Photo%3C/text%3E%3C/svg%3E">
                </div>
                <input type="file" class="form-control d-none" id="photo" name="photo" accept="image/*" required>
                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('photo').click()">Choose Photo</button>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Position</label>
                <input type="text" name="designation" class="form-control" required>
            </div>
            
            <div class="mb-3 instagram-input">
                <label class="form-label">Instagram Handle</label>
                <input type="text" name="instagram" class="form-control input-with-icon" placeholder="username">
            </div>
            
            <div class="mb-3 contact-input">
                <label class="form-label">Contact Number</label>
                <input type="tel" name="contact" class="form-control input-with-icon" placeholder="+1234567890" required>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Generate ID Card</button>
            </div>
        </form>
    </div>

    <script>
        // Image preview
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('imagePreview').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>