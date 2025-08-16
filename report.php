<?php
$conn = new mysqli('localhost', 'root', 'root', 'employee_attendance_system');
$result = $conn->query("
    SELECT e.full_name, e.employee_id, a.check_in, a.check_out 
    FROM attendance a
    JOIN employees e ON a.employee_id = e.employee_id
    ORDER BY a.check_in DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Attendance Report</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>ID</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['full_name'] ?></td>
                    <td><?= $row['employee_id'] ?></td>
                    <td><?= $row['check_in'] ?></td>
                    <td><?= $row['check_out'] ?: '--' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="admin.php" class="btn btn-secondary">Back to Admin</a>
    </div>
</body>
</html>