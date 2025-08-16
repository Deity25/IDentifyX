<?php
$db_host = 'localhost';     
$db_user = '';           
$db_pass = '';          
$db_name = 'employee_attendance_system';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>