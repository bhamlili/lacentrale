<?php
session_start();
include './db_config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$appointment_id = intval($_GET['id']);

// Récupérer les informations du rendez-vous
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
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$rdv = $stmt->get_result()->fetch_assoc();

if (!$rdv) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de rendez-vous</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('img/accueil.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .confirmation-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            height: 100px;
            width: auto;
        }

        h2 {
            color: #0077b6;
            text-align: center;
            margin-bottom: 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            flex: 1;
        }

        .btn-pdf {
            background: #dc3545;
            color: white;
        }

        .btn-retour {
            background: #0077b6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <div class="logo">
            <img src="img/la centrale1.png" alt="LaCentrale.ma">
        </div>
        <h2>✅ Rendez-vous confirmé</h2>
        
        <div class="detail-row">
            <span>Date et heure:</span>
            <span><?php echo date('d/m/Y à H:i', strtotime($rdv['appointment_datetime'])); ?></span>
        </div>
        <div class="detail-row">
            <span>Médecin:</span>
            <span>Dr. <?php echo htmlspecialchars($rdv['doctor_name']); ?></span>
        </div>
        <div class="detail-row">
            <span>Nom:</span>
            <span><?php echo htmlspecialchars($rdv['nom']); ?></span>
        </div>
        <div class="detail-row">
            <span>Prénom:</span>
            <span><?php echo htmlspecialchars($rdv['prenom']); ?></span>
        </div>
        <div class="detail-row">
            <span>Email:</span>
            <span><?php echo htmlspecialchars($rdv['email']); ?></span>
        </div>
        <div class="detail-row">
            <span>Téléphone:</span>
            <span><?php echo htmlspecialchars($rdv['num']); ?></span>
        </div>

        <div class="actions">
            <a href="export_pdf.php?id=<?php echo $appointment_id; ?>" class="btn btn-pdf" download>
                📄 Télécharger PDF
            </a>
            <a href="index.php" class="btn btn-retour">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
