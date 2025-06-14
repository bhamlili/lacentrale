<?php
// Désactiver la mise en cache
header('Cache-Control: no-cache, private');
header('Pragma: no-cache');

// Vérifier si FPDF existe
if (!file_exists('fpdf/fpdf.php')) {
    die('FPDF n\'est pas installé. Veuillez l\'installer d\'abord.');
}

require('fpdf/fpdf.php');
require('db_config.php');

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
        // Logo plus haut
        if (file_exists('img/La Centrale1.png')) {
            $this->Image('img/La Centrale1.png', 60, 5, 90); // Y réduit de 30 à 5
        }
        
        // Réduire l'espace après le logo
        $this->Ln(40); // Réduit de 60 à 40
        
        // Titre aligné à gauche, position ajustée
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(20, 80); // Y réduit de 80 à 55
        $this->Cell(170, 10, utf8_decode('Confirmation de Rendez-vous'), 0, 1, 'L');
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

try {
    $pdf = new PDF();
    $pdf->AddPage();
    
    // En-tête d'informations
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetXY(20, 95);
    $pdf->Cell(170, 10, 'INFORMATIONS DU RENDEZ-VOUS', 0, 1, 'C', true);
    
    // Cadre d'informations
    $pdf->Rect(20, 105, 170, 100, 'D');
    
    // Espacement entre les lignes
    $lineHeight = 16;
    $y = 115;
    
    // Labels et valeurs avec alignement et espacement uniforme
    $infos = array(
        'Date et heure' => date('d/m/Y H:i', strtotime($rdv['appointment_datetime'])),
        'Medecin' => 'Dr. ' . utf8_decode($rdv['doctor_name']),
        'Patient' => utf8_decode($rdv['nom'] . ' ' . $rdv['prenom']),
        'Email' => $rdv['email'],
        'Telephone' => $rdv['num']
    );
    
    foreach($infos as $label => $value) {
        $pdf->SetXY(30, $y);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, $label . ' :', 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $value, 0);
        $y += $lineHeight;
    }

    // Ajustement de la position des notes
    $pdf->SetXY(20, 220);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(0, 10, 'NOTES IMPORTANTES', 0, 1, 'C', true);
    
    $pdf->Ln(5);
    $pdf->SetX(25);
    $pdf->SetFont('Arial', '', 11);
    
    // Icône bullet point personnalisé
    $bullet = chr(127);
    
    // Notes avec indentation et espacement
    $notes = array(
        "Veuillez vous présenter 10 minutes avant l'heure de votre rendez-vous",
        "Apportez votre carte d'identité et votre carte vitale",
        "En cas d'empêchement, prévenez-nous 24h à l'avance au : 06-XX-XX-XX-XX",
        "Port du masque recommandé dans notre établissement"
    );
    
    foreach($notes as $note) {
        $pdf->SetX(25);
        $pdf->Cell(5, 8, $bullet, 0, 0);
        $pdf->MultiCell(160, 8, utf8_decode($note));
    }

    // Ajout d'un cadre autour des notes
    $pdf->Rect(20, 220, 170, 60);

    // Forcer le téléchargement
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Confirmation_RDV_'.$rdv_id.'.pdf"');
    
    // Générer et envoyer le PDF
    $pdf->Output('D', 'Confirmation_RDV_'.$rdv_id.'.pdf');
    exit;
} catch (Exception $e) {
    die('Erreur lors de la génération du PDF: ' . $e->getMessage());
}
?>
