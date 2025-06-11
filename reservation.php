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
    $patient_telephone = $_POST['NUM'] ?? '';

    $doctor_id_post = $_POST['doctor_id'] ?? null;
    $appointment_datetime_post = $_POST['appointment_datetime'] ?? null;

    if ($patient_nom && $patient_prenom && $patient_email && $patient_telephone && $doctor_id_post && $appointment_datetime_post) {
        // Check if patient already exists with prepared statement
        $check_sql = "SELECT patient_id FROM patients WHERE NUM = ?";
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
            // Insert new patient with prepared statement
            $insert_sql = "INSERT INTO patients (name, NUM) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                die("Erreur de préparation : " . $conn->error);
            }
            $full_name = $patient_nom . ' ' . $patient_prenom;
            $insert_stmt->bind_param("ss", $full_name, $patient_telephone);
            
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
            NUM
        ) VALUES (?, ?, ?, 'scheduled', ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Erreur de préparation : " . $conn->error);
        }
        
        $stmt->bind_param(
            "iisssss", 
            $doctor_id_post, 
            $patient_id, 
            $appointment_datetime_post,
            $patient_nom,
            $patient_prenom,
            $patient_email,
            $patient_telephone
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "✅ Rendez-vous enregistré avec succès!";
            
            // Stocker les informations dans la session
            $_SESSION['patient_info'] = [
                'nom' => $patient_nom,
                'prenom' => $patient_prenom,
                'email' => $patient_email,
                'NUM' => $patient_telephone
            ];
            
            header("Location: agenda.php?doctor_id=" . $doctor_id_post . "&success=1");
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

        <div id="confirmation-message" style="display:none;">ation</button>
          <h3>✅ Rendez-vous confirmé !</h3>
        </div>

    <?php
    $conn->close();
    ?>
    </main>
</body>
</html>
