<?php
require('fpdf/fpdf.php');
include 'db_config.php';

if (!isset($_GET['id'])) {
    die('ID de rendez-vous non spécifié');
}

$rdv_id = intval($_GET['id']);

// Récupération des données du rendez-vous
$sql = "SELECT 
    a.appointment_datetime,
    a.nom,
    a.prenom,
    a.email,
    a.num,
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

class PDF extends FPDF {
    function Header() {
        // Charger la police personnalisée
        $this->AddFont('DejaVu', '', 'DejaVuSans.php');
        $this->SetFont('DejaVu', '', 15);
        $this->Image('img/la centrale1.png', 10, 10, 50);
        $this->Cell(0, 20, 'Confirmation de Rendez-vous', 0, 1, 'C');
        $this->Ln(10);
    }
}

$pdf = new PDF();
$pdf->AddFont('DejaVu', '', 'DejaVuSans.php');
$pdf->AddPage();
$pdf->SetFont('DejaVu', '', 12);

// Affichage des informations
$pdf->Cell(0, 10, 'Date et heure : ' . date('d/m/Y H:i', strtotime($rdv['appointment_datetime'])), 0, 1);
$pdf->Cell(0, 10, 'Médecin : Dr. ' . $rdv['doctor_name'], 0, 1);
$pdf->Cell(0, 10, 'Patient : ' . $rdv['nom'] . ' ' . $rdv['prenom'], 0, 1);
$pdf->Cell(0, 10, 'Email : ' . $rdv['email'], 0, 1);
$pdf->Cell(0, 10, 'Téléphone : ' . $rdv['num'], 0, 1);

// Export du PDF
$pdf->Output('D', 'Confirmation_RDV_' . $rdv_id . '.pdf');
?>
