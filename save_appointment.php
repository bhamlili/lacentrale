<?php
session_start();
require_once 'config.php';
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $patient_name = $_POST['patient_name'] ?? '';
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';
        $motif = $_POST['motif'] ?? '';
        $doctor_id = $_SESSION['doctor_id'] ?? 0;

        if (empty($patient_name) || empty($appointment_date) || empty($appointment_time)) {
            throw new Exception("Tous les champs sont obligatoires");
        }

        $appointment_datetime = $appointment_date . ' ' . $appointment_time;

        // Créer un nouveau patient
        $stmt = $conn->prepare("INSERT INTO patients (name) VALUES (?)");
        $stmt->bind_param("s", $patient_name);
        $stmt->execute();
        $patient_id = $conn->insert_id;

        // Insérer le rendez-vous
        $stmt = $conn->prepare("INSERT INTO appointments (doctor_id, patient_id, appointment_datetime, motif, status) VALUES (?, ?, ?, ?, 'scheduled')");
        $stmt->bind_param("iiss", $doctor_id, $patient_id, $appointment_datetime, $motif);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Rendez-vous enregistré avec succès']);
        } else {
            throw new Exception("Erreur lors de l'insertion du rendez-vous");
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
