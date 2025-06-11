<?php
require('fpdf/fpdf.php');
include 'db_config.php';

if (!isset($_GET['id'])) {
    die('ID de rendez-vous non spécifié');
}

$rdv_id = intval($_GET['id']);

class PDF extends FPDF {
    function Header() {
        $this->Image('img/la centrale1.png', 10, 10, 50);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 20, mb_convert_encoding('Confirmation de Rendez-vous', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10);
    }
}

// Récupérer les données du rendez-vous
$sql = "SELECT 
    a.*,
    d.name as doctor_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.appointment_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rdv_id);
$stmt->execute();
$rdv = $stmt->get_result()->fetch_assoc();

if (!$rdv) {
    die('Rendez-vous non trouvé');
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Informations du rendez-vous
$pdf->Cell(0, 10, utf8_decode('Date et heure: ' . date('d/m/Y H:i', strtotime($rdv['appointment_datetime']))), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Médecin: Dr. ' . $rdv['doctor_name']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Patient: ' . $rdv['nom'] . ' ' . $rdv['prenom']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Email: ' . $rdv['email']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Téléphone: ' . $rdv['num']), 0, 1);

$pdf->Output('D', 'Confirmation_RDV_' . $rdv_id . '.pdf');
?>
