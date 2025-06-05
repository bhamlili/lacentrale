<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "your_db_username"; // Replace with your database username
$password = "your_db_password"; // Replace with your database password
$dbname = "medical_appointments";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doctor_id = $_GET['doctor_id'] ?? null;
$appointment_datetime = $_GET['datetime'] ?? null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_nom = $_POST['nom'] ?? '';
    $patient_prenom = $_POST['prenom'] ?? '';
    $patient_email = $_POST['email'] ?? '';
    $patient_telephone = $_POST['telephone'] ?? '';

    $doctor_id_post = $_POST['doctor_id'] ?? null;
    $appointment_datetime_post = $_POST['appointment_datetime'] ?? null;

    if ($patient_nom && $patient_prenom && $patient_email && $patient_telephone && $doctor_id_post && $appointment_datetime_post) {
        // Check if patient already exists by email or phone
        $sql = "SELECT patient_id FROM patients WHERE contact_info = '" . $conn->real_escape_string($patient_email) . "' OR contact_info = '" . $conn->real_escape_string($patient_telephone) . "'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $patient = $result->fetch_assoc();
            $patient_id = $patient['patient_id'];
        } else {
            // Insert new patient
            $patient_name = $patient_nom . ' ' . $patient_prenom;
            $sql = "INSERT INTO patients (name, contact_info) VALUES ('" . $conn->real_escape_string($patient_name) . "', '" . $conn->real_escape_string($patient_email) . "')"; // Using email as contact_info for simplicity, you might want to store both email and phone
            if ($conn->query($sql) === TRUE) {
                $patient_id = $conn->insert_id;
            } else {
                echo "Error inserting patient: " . $conn->error;
                $conn->close();
                exit;
            }
        }

        // Insert appointment
        $sql = "INSERT INTO appointments (doctor_id, patient_id, appointment_datetime) VALUES (" . intval($doctor_id_post) . ", " . intval($patient_id) . ", '" . $conn->real_escape_string($appointment_datetime_post) . "')";
        if ($conn->query($sql) === TRUE) {
            // Redirect to confirmation page
            $_SESSION['confirmation_details'] = [
                'doctor_id' => $doctor_id_post,
                'datetime' => $appointment_datetime_post
            ];
            header("Location: /confirmation.php");
            exit;
        } else {
            echo "Error inserting appointment: " . $conn->error;
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
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <header class="header">
    <div class="logo">LaCentrale<span class="dot">.</span><span class="ma">ma</span></div>
  </header>

  <main class="reservation-container">
    <h2>Réserver un rendez-vous</h2>
    <div id="rdv-info">
        <?php
        if ($doctor_id && $appointment_datetime) {
            $sql = "SELECT name FROM doctors WHERE doctor_id = " . intval($doctor_id);
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $doctor = $result->fetch_assoc();
                echo "<p>Rendez-vous avec Dr. " . htmlspecialchars($doctor['name']) . " le " . date('d/m/Y à H:i', strtotime($appointment_datetime)) . "</p>";
            }
        } ?>
    </div>

    <form id="form-reservation" method="POST" action="/reservation.php">
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

      <button type="submit" class="btn-rdv">Confirmer la réservation</button>
    </form>

    <div id="confirmation-message" style="display:none;">
      <h3>✅ Rendez-vous confirmé !</h3>
    </div>
  </main>

  <script src="js/reservation.js"></script>
<?php
$conn->close();
?>
</body>
</html>
