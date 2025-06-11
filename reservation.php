<?php
session_start();

include 'config.php';
include 'db_config.php';

// Récupérer les paramètres de l'URL
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
$appointment_datetime = isset($_GET['datetime']) ? $_GET['datetime'] : null;

// Vérifier si les paramètres requis sont présents
if (!$doctor_id || !$appointment_datetime) {
    header("Location: index.php");
    exit();
}

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $patient_nom = $_POST['nom'] ?? '';
    $patient_prenom = $_POST['prenom'] ?? '';
    $patient_email = $_POST['email'] ?? '';
    $patient_telephone = $_POST['num'] ?? '';

    $doctor_id_post = $_POST['doctor_id'] ?? null;
    $appointment_datetime_post = $_POST['appointment_datetime'] ?? null;

    if ($patient_nom && $patient_prenom && $patient_email && $patient_telephone && $doctor_id_post && $appointment_datetime_post) {
        // Check if patient already exists
        $check_sql = "SELECT patient_id FROM patients WHERE num = ?";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            die("Erreur de préparation : " . $conn->error);
        }
        $check_stmt->bind_param("s", $patient_telephone);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $patient = $result->fetch_assoc();
            $patient_id = $patient['patient_id'];
        } else {
            // Insert new patient
            $insert_sql = "INSERT INTO patients (nom, prenom, email, num) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                die("Erreur de préparation : " . $conn->error);
            }
            
            $insert_stmt->bind_param("ssss", 
                $patient_nom, 
                $patient_prenom, 
                $patient_email, 
                $patient_telephone
            );

            if ($insert_stmt->execute()) {
                $patient_id = $conn->insert_id;
            } else {
                die("Erreur d'insertion du patient : " . $conn->error);
            }
            $insert_stmt->close();
        }
        $check_stmt->close();

        // Vérifier si le créneau est toujours disponible
        $check_slot = "SELECT appointment_id FROM appointments 
                      WHERE doctor_id = " . intval($doctor_id_post) . " 
                      AND appointment_datetime = '" . $conn->real_escape_string($appointment_datetime_post) . "'";
        $slot_result = $conn->query($check_slot);
        
        if ($slot_result->num_rows > 0) {
            die("Ce créneau n'est plus disponible.");
        }

        // Insert appointment
        $sql = "INSERT INTO appointments (
            doctor_id, 
            patient_id, 
            appointment_datetime,
            status,
            nom,
            prenom,
            email,
            num
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Erreur de préparation : " . $conn->error);
        }
        
        $status = 'confirmed';
        $stmt->bind_param(
            "iissssss", 
            $doctor_id_post, 
            $patient_id, 
            $appointment_datetime_post,
            $status,
            $patient_nom,
            $patient_prenom,
            $patient_email,
            $patient_telephone
        );

        if ($stmt->execute()) {
            $appointment_id = $conn->insert_id;
            header("Location: confirmation.php?id=" . $appointment_id);
            exit();
        } else {
            $_SESSION['error_message'] = "❌ Erreur lors de l'enregistrement.";
            header("Location: agenda.php?doctor_id=" . $doctor_id_post);
            exit();
        }
    } else {
        echo "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Réservation</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('img/rdv.jpg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(10px); /* Augmentation du flou */
            z-index: -1;
        }

        .header {
            display: none; /* Supprimer l'ancien header */
        }

        .reservation-container {
            position: relative;
            max-width: 600px;
            margin: 80px auto;
            padding-top: 120px;
            background: white; /* Changé pour blanc opaque */
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .logo-container {
            position: absolute;
            top: -80px; /* Changé de -100px à -80px pour descendre le logo */
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 100%;
        }

        .logo-container img {
            height: 220px; /* Augmenté de 180px à 220px */
            width: auto;
            object-fit: contain;
        }

        #rdv-info {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }

        #rdv-info p {
            font-size: 18px;
            color: #2c3e50;
            font-weight: 600;
            margin: 0;
        }

        #form-reservation {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #form-reservation label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        #form-reservation input {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        #form-reservation input:focus {
            border-color: #0077b6;
            outline: none;
        }

        .btn-rdv {
            background: #0077b6;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }

        .btn-rdv:hover {
            background: #005f8d;
        }

        .confirmation-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .details-section {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-pdf {
            background: #0077b6;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            text-align: center;
            flex: 1;
        }

        .btn-pdf:hover {
            background: #005f8d;
        }

        .btn-retour {
            background: #6c757d;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            text-align: center;
            flex: 1;
        }

        .btn-retour:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <main class="reservation-container">
        <div class="logo-container">
            <img src="img/la centrale1.png" alt="LaCentrale.ma">
        </div>
        <h2>Réserver un rendez-vous</h2>
        <div id="rdv-info">
            <?php
            if ($doctor_id && $appointment_datetime) {
                $sql = "SELECT name FROM doctors WHERE doctor_id = " . intval($doctor_id);
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $doctor = $result->fetch_assoc();
                    echo "<p>Rendez-vous avec  " . htmlspecialchars($doctor['name']) . " le " . date('d/m/Y à H:i', strtotime($appointment_datetime)) . "</p>";
                }
            } ?>
        </div>

        <!-- Ajouter avant le formulaire -->
        <?php if (isset($_GET['confirmation']) && isset($_SESSION['confirmation_data'])): ?>
            <div class="confirmation-message">
                <h2>✅ Rendez-vous confirmé avec succès</h2>
                <div class="details-section">
                    <div class="detail-row">
                        <span>Date et heure:</span>
                        <span><?php echo date('d/m/Y à H:i', strtotime($_SESSION['confirmation_data']['appointment_datetime'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Médecin:</span>
                        <span>Dr. <?php echo htmlspecialchars($_SESSION['confirmation_data']['doctor_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Patient:</span>
                        <span><?php echo htmlspecialchars($_SESSION['confirmation_data']['nom'] . ' ' . $_SESSION['confirmation_data']['prenom']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Email:</span>
                        <span><?php echo htmlspecialchars($_SESSION['confirmation_data']['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Téléphone:</span>
                        <span><?php echo htmlspecialchars($_SESSION['confirmation_data']['num']); ?></span>
                    </div>
                    
                    <div class="actions">
                        <a href="export_pdf.php?id=<?php echo $_SESSION['confirmation_data']['appointment_id']; ?>" class="btn-pdf">
                            📄 Télécharger PDF
                        </a>
                        <a href="index.php" class="btn-retour">Retour à l'accueil</a>
                    </div>
                </div>
            </div>
        <?php unset($_SESSION['confirmation_data']); ?>
        <?php else: ?>
            <!-- Afficher le formulaire de réservation existant -->
            <form id="form-reservation" method="POST">
              <label>Nom :</label>
              <input type="text" name="nom" required />

              <label>Prénom :</label>
              <input type="text" name="prenom" required />

              <label>Email :</label>
              <input type="email" name="email" required />

              <label>Téléphone :</label>
              <input type="tel" name="telephone" required />
              <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($doctor_id); ?>">
              <input type="hidden" name="appointment_datetime" value="<?php echo htmlspecialchars($appointment_datetime); ?>">

              <button type="submit" class="btn-rdv" name="submit">Confirmer la réservation</button>
            </form>
        <?php endif; ?>
    <?php
    $conn->close();
    ?>
    </main>
</body>
</html>
