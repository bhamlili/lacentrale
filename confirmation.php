<?php
session_start();

// Vérifier si les détails de confirmation existent
if (!isset($_SESSION['confirmation_details'])) {
    header("Location: index.php");
    exit();
}

$details = $_SESSION['confirmation_details'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de rendez-vous</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('img/rdv.jpg') no-repeat center center fixed;
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
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            margin: 20px;
            border: 2px solid #0077b6;
        }

        .title {
            color: #0077b6;
            text-align: center;
            margin-bottom: 30px;
        }

        .details-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-label {
            font-weight: 600;
            color: #0077b6;
        }

        .btn-retour {
            display: block;
            width: 200px;
            margin: 20px auto 0;
            padding: 12px;
            background: #0077b6;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <h2 class="title">✅ Rendez-vous confirmé</h2>

        <div class="details-section">
            <div class="detail-row">
                <span class="detail-label">Médecin:</span>
                <span>Dr. <?php echo htmlspecialchars($details['doctor_name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Spécialité:</span>
                <span><?php echo htmlspecialchars($details['doctor_specialty']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date et heure:</span>
                <span><?php echo date('d/m/Y à H:i', strtotime($details['datetime'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Patient:</span>
                <span><?php echo htmlspecialchars($details['patient_nom'] . ' ' . $details['patient_prenom']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span><?php echo htmlspecialchars($details['patient_email']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Téléphone:</span>
                <span><?php echo htmlspecialchars($details['patient_telephone']); ?></span>
            </div>
        </div>

        <a href="index.php" class="btn-retour">Retour à l'accueil</a>
    </div>
</body>
</html>
<?php
// Nettoyer les données de session après affichage
unset($_SESSION['confirmation_details']);
?>
