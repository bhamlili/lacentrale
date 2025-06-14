<?php
session_start();
require_once 'db_config.php';

$doctor_id = $_SESSION['doctor_id'];
$sql = "SELECT * FROM appointments 
        WHERE doctor_id = ? 
        AND status != 'cancelled'
        ORDER BY appointment_datetime DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = array();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

header('Content-Type: application/json');
echo json_encode($appointments);
?>
