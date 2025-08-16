<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', 'root', 'employee_attendance_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process attendance
$message = '';
if (isset($_GET['emp_id'])) {
    $employee_id = trim($_GET['emp_id']);
    
    // Verify employee exists
    $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        $current_time = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        
        // Check existing attendance
        $stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND DATE(check_in) = ? AND check_out IS NULL");
        $stmt->bind_param("ss", $employee_id, $today);
        $stmt->execute();
        $attendance = $stmt->get_result();
        
        if ($attendance->num_rows > 0) {
            // Check out
            $stmt = $conn->prepare("UPDATE attendance SET check_out = ? WHERE employee_id = ? AND check_out IS NULL");
            $stmt->bind_param("ss", $current_time, $employee_id);
            if ($stmt->execute()) {
                $message = "✅ " . htmlspecialchars($employee['full_name']) . " checked OUT at " . date('h:i A');
            } else {
                $message = "❌ Failed to record check out";
            }
        } else {
            // Check in
            $stmt = $conn->prepare("INSERT INTO attendance (employee_id, check_in) VALUES (?, ?)");
            $stmt->bind_param("ss", $employee_id, $current_time);
            if ($stmt->execute()) {
                $message = "✅ " . htmlspecialchars($employee['full_name']) . " checked IN at " . date('h:i A');
            } else {
                $message = "❌ Failed to record check in";
            }
        }
    } else {
        $message = "❌ Invalid employee ID: " . htmlspecialchars($employee_id);
    }
}

// Get recent attendance
$recent_attendance = $conn->query("
    SELECT e.full_name, e.employee_id, a.check_in, a.check_out 
    FROM attendance a
    JOIN employees e ON a.employee_id = e.employee_id
    ORDER BY a.check_in DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get employee list for reference
$employees = $conn->query("SELECT employee_id, full_name FROM employees ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/dist/html5-qrcode.min.js"></script>
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .scanner-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        #qr-reader {
            width: 100%;
            margin: 20px 0;
        }
        #scan-result {
            min-height: 60px;
            margin: 15px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .manual-entry {
            margin: 20px 0;
            padding: 15px;
            background: #f0f8ff;
            border-radius: 5px;
        }
        .employee-list {
            max-height: 200px;
            overflow-y: auto;
        }
        .employee-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #e9ecef;
            border-radius: 4px;
            font-family: monospace;
        }
        .camera-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <h2 class="text-center mb-4">Employee Attendance Scanner</h2>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div id="qr-reader"></div>
        <div id="scan-result" class="text-center">
            <div class="camera-warning">
                <i class="bi bi-camera"></i> Camera access requires HTTPS or localhost
            </div>
            <p>Point your camera at the employee ID card QR code</p>
        </div>
        
        <div class="manual-entry">
            <h5 class="text-center mb-3">Alternative Check-In</h5>
            <div class="input-group mb-3">
                <input type="text" id="manual-id" class="form-control" placeholder="Enter Employee ID">
                <button class="btn btn-primary" onclick="submitManual()">Submit</button>
            </div>
            <small class="text-muted">Example: EMPA3B7C9D2</small>
            
            <div class="mt-3 text-center">
                <button class="btn btn-outline-primary" onclick="getLocation()">
                    <i class="bi bi-geo-alt"></i> Verify Location
                </button>
                <div id="location-status" class="mt-2"></div>
            </div>
        </div>
        
        <div class="mt-4">
            <h5>Registered Employees</h5>
            <div class="employee-list">
                <?php if (count($employees) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($employees as $emp): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= htmlspecialchars($emp['full_name']) ?></span>
                                <span class="employee-badge"><?= htmlspecialchars($emp['employee_id']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-warning">No employees found in database</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-4">
            <h5>Recent Attendance</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_attendance as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['full_name']) ?></td>
                            <td><?= date('h:i A', strtotime($record['check_in'])) ?></td>
                            <td><?= $record['check_out'] ? date('h:i A', strtotime($record['check_out'])) : '--' ?></td>
                            <td>
                                <span class="badge <?= $record['check_out'] ? 'bg-success' : 'bg-warning' ?>">
                                    <?= $record['check_out'] ? 'Completed' : 'Checked In' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="admin.php" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
    </div>

    <script>
        // Handle scan results
        function onScanSuccess(decodedText) {
            // Clean the scanned ID (remove any special characters)
            const empId = decodedText.replace(/[^a-zA-Z0-9]/g, '');
            
            console.log("Scanned ID:", empId);
            document.getElementById('scan-result').innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Detected: ${empId}
                    <p>Processing attendance...</p>
                </div>`;
            
            // Submit to server
            window.location.href = `scanner.php?emp_id=${empId}`;
        }

        // Manual submission
        function submitManual() {
            const input = document.getElementById('manual-id');
            const empId = input.value.trim();
            
            if (!empId) {
                alert("Please enter the employee ID");
                return;
            }
            
            // Clean the input
            const cleanEmpId = empId.replace(/[^a-zA-Z0-9]/g, '');
            
            console.log("Manual submission:", cleanEmpId);
            document.getElementById('scan-result').innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-person-check"></i> Processing ${cleanEmpId}...
                </div>`;
            
            window.location.href = `scanner.php?emp_id=${cleanEmpId}`;
        }

        // Geolocation verification
        function getLocation() {
            const statusElement = document.getElementById('location-status');
            
            if (!navigator.geolocation) {
                statusElement.innerHTML = '<div class="alert alert-danger">Geolocation is not supported by your browser</div>';
                return;
            }

            statusElement.innerHTML = '<div class="alert alert-info">Checking location...</div>';
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const distance = calculateDistance(lat, lng, 12.9716, 77.5946); // Office coordinates
                    
                    if (distance <= 100) { // 100 meters radius
                        statusElement.innerHTML = `
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Location verified (${distance.toFixed(1)}m from office)
                            </div>`;
                    } else {
                        statusElement.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> Too far from office (${distance.toFixed(1)}m)
                            </div>`;
                    }
                },
                (error) => {
                    statusElement.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Error getting location: ${error.message}
                        </div>`;
                }
            );
        }

        // Calculate distance between two coordinates in meters
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // Earth radius in meters
            const φ1 = lat1 * Math.PI/180;
            const φ2 = lat2 * Math.PI/180;
            const Δφ = (lat2-lat1) * Math.PI/180;
            const Δλ = (lon2-lon1) * Math.PI/180;

            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ/2) * Math.sin(Δλ/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

            return R * c;
        }

        // Initialize scanner only if on HTTPS or localhost
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.protocol === 'https:' || window.location.hostname === 'localhost') {
                const html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader",
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        rememberLastUsedCamera: true
                    },
                    false
                );
                html5QrcodeScanner.render(onScanSuccess);
                
                // Hide the warning if camera is supported
                document.querySelector('.camera-warning').style.display = 'none';
            }
            
            // Allow pressing Enter in manual ID field
            document.getElementById('manual-id').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    submitManual();
                }
            });
        });
    </script>
</body>
</html>