<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', 'root', 'employee_attendance_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify employee ID exists in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$employee_id = $_GET['id'];

// Get employee data
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Employee not found with ID: " . htmlspecialchars($employee_id));
}

$employee = $result->fetch_assoc();
?>

<!-- <!DOCTYPE html>
<html>
<head>
    <title>Employee ID Card - <?= htmlspecialchars($employee['full_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .id-card {
            width: 350px;
            border: 2px solid #333;
            padding: 20px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
            background: white;
        }
        .company-header {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            margin-bottom: 15px;
        }
        .employee-photo {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            border: 1px solid #ddd;
            overflow: hidden;
        }
        .employee-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .qr-code {
            margin: 15px auto;
            text-align: center;
        }
        .employee-info p {
            margin-bottom: 5px;
        }
        .download-btn {
            margin-top: 20px;
        }
        @media print {
            .download-btn, .btn-secondary {
                display: none;
            }
            body {
                background: white !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="id-card" id="idCard">
            <div class="company-header">
                <h4>YOUR COMPANY NAME</h4>
                <p>Employee Identification Card</p>
            </div>
            
            <div class="employee-photo">
                <?php if (!empty($employee['photo_path']) && file_exists($employee['photo_path'])): ?>
                    <img src="<?= htmlspecialchars($employee['photo_path']) ?>">
                <?php else: ?>
                    <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center;">
                        No Photo
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="employee-info">
                <p><strong>Name:</strong> <?= htmlspecialchars($employee['full_name']) ?></p>
                <p><strong>ID:</strong> <?= htmlspecialchars($employee['employee_id']) ?></p>
                <p><strong>Position:</strong> <?= htmlspecialchars($employee['designation']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($employee['contact']) ?></p>
            </div>
            
            <div class="qr-code" id="qrcode"></div>
        </div>
        
        <div class="text-center">
            <button onclick="downloadID()" class="btn btn-primary download-btn">Download ID Card</button>
            <button onclick="window.print()" class="btn btn-success">Print ID Card</button>
            <a href="admin.php" class="btn btn-secondary">Add Another Employee</a>
        </div>
    </div>

    <script>
        // Generate QR code with employee ID
        document.addEventListener('DOMContentLoaded', function() {
            const empId = "<?= htmlspecialchars($employee['employee_id']) ?>";
            console.log("Generating QR for:", empId);
            
            new QRCode(document.getElementById("qrcode"), {
                text: empId,
                width: 100,
                height: 100,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
        
        // Download ID card as image
        function downloadID() {
            html2canvas(document.getElementById("idCard")).then(canvas => {
                const link = document.createElement('a');
                link.download = 'ID_Card_<?= htmlspecialchars($employee['employee_id']) ?>.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }
    </script>
</body>
</html> -->
<?php
// [Previous PHP code remains the same until the HTML section]
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee ID Card - <?= htmlspecialchars($employee['full_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            background: #f0f2f5;
            padding: 20px;
        }
        .id-card-container {
            perspective: 1000px;
            margin-bottom: 30px;
        }
        .id-card {
            width: 350px;
            height: 220px;
            margin: 0 auto;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .card-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            opacity: 0.15;
        }
        .card-background img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: linear-gradient(to right, rgba(255,255,255,0.9) 60%, rgba(255,255,255,0.7) 100%);
        }
        .company-header {
            background: #2F72B5;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .company-logo {
            height: 30px;
        }
        .company-name {
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-align: right;
            margin-left: 10px;
        }
        .card-body {
            display: flex;
            flex-grow: 1;
            padding: 15px;
        }
        .photo-section {
            width: 120px;
            margin-right: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .employee-photo {
            width: 100px;
            height: 100px;
            border: 3px solid #2F72B5;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 10px;
            background: white;
        }
        .employee-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .employee-id {
            font-size: 12px;
            font-weight: bold;
            color: #2F72B5;
        }
        .info-section {
            flex-grow: 1;
            font-size: 12px;
        }
        .info-row {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .info-label {
            font-weight: bold;
            width: 55px;
            color: #2F72B5;
        }
        .info-value {
            flex-grow: 1;
        }
        .icon-row {
            display: flex;
            align-items: center;
            margin-top: 5px;
            margin-left:-5px;
        }
        .icon-label {
            width: 20px;
            text-align: center;
            margin-right: 1px;
            color: #2F72B5;
        }
        .icon-value {
            color: #333;
            font-weight: bold;
        }
        .instagram-icon {
            color: #E1306C;
        }
        .phone-icon {
            color: #25D366;
        }
        .qr-section {
            position: absolute;
            bottom: 15px;
            right: 12px;
            text-align: center;
            background: white;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .qr-code {
            width: 70px;
            height: 70px;
        }
        .qr-text {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        .actions {
            text-align: center;
            margin-top: 20px;
        }
        @media print {
            .actions {
                display: none;
            }
            body {
                background: white !important;
            }
            .id-card {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="id-card-container">
            <div class="id-card" id="idCard">
                <!-- Background Image -->
                <div class="card-background">
                    <?php if (!empty($employee['photo_path']) && file_exists($employee['photo_path'])): ?>
                        <img src="<?= htmlspecialchars($employee['photo_path']) ?>">
                    <?php endif; ?>
                </div>
                
                <!-- Card Content -->
                <div class="card-content">
                    <!-- Header with Microsoft Logo -->
                    <div class="company-header">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg" class="company-logo" alt="Microsoft Logo">
                        <div class="company-name">Employee ID</div>
                    </div>
                    
                    <!-- Body Content -->
                    <div class="card-body">
                        <div class="photo-section">
                            <div class="employee-photo">
                                <?php if (!empty($employee['photo_path']) && file_exists($employee['photo_path'])): ?>
                                    <img src="<?= htmlspecialchars($employee['photo_path']) ?>">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center;">
                                        <small>No Photo</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="employee-id">
                                ID: <?= htmlspecialchars($employee['employee_id']) ?>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <div class="info-row">
                                <div class="info-label">Name:</div>
                                <div class="info-value"><?= htmlspecialchars($employee['full_name']) ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Position:</div>
                                <div class="info-value"><?= htmlspecialchars($employee['designation']) ?></div>
                            </div>
                            <div class="icon-row">
                                <div class="icon-label instagram-icon">
                                    <i class="fab fa-instagram"></i>
                                </div>
                                <div class="icon-value"><?= htmlspecialchars($employee['instagram']) ?></div>
                            </div>
                            <div class="icon-row">
                                <div class="icon-label phone-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="icon-value"><?= htmlspecialchars($employee['contact']) ?></div>
                            </div>
                        </div>
                        
                        <div class="qr-section">
                            <div class="qr-code" id="qrcode"></div>
                            <div class="qr-text">Scan to verify</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button onclick="downloadID()" class="btn btn-primary">Download ID Card</button>
            <button onclick="window.print()" class="btn btn-success">Print ID Card</button>
            <a href="admin.php" class="btn btn-secondary">Back to Admin</a>
        </div>
    </div>

    <script>
        // Generate QR code with employee ID
        document.addEventListener('DOMContentLoaded', function() {
            const empId = "<?= htmlspecialchars($employee['employee_id']) ?>";
            new QRCode(document.getElementById("qrcode"), {
                text: "MICROSOFT-ID:" + empId,
                width: 70,
                height: 70,
                colorDark: "#2F72B5",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
        
        // Download ID card as image
        function downloadID() {
            html2canvas(document.getElementById("idCard"), {
                scale: 2,
                logging: false,
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Microsoft_ID_<?= htmlspecialchars($employee['employee_id']) ?>.png';
                link.href = canvas.toDataURL('image/png', 1.0);
                link.click();
            });
        }
    </script>
</body>
</html>