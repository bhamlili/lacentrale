<?php
include 'config.php';
include './db_config.php';

// Use the database connection from db_config.php
$conn = $link; // Assign $link to $conn for consistency

// Get doctor ID from URL
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

$doctor = null;
$availability = [];

if ($doctor_id > 0) {
    // Fetch doctor information
    $doctor_sql = "SELECT name, specialty FROM doctors WHERE doctor_id = ?";    $doctor_stmt = $link->prepare($doctor_sql);
    $doctor_stmt->bind_param("i", $doctor_id);
    $doctor_stmt->execute();
    $doctor_result = $doctor_stmt->get_result();
    $doctor = $doctor_result->fetch_assoc();

    // Fetch doctor's regular working hours
    $availability_stmt = $conn->prepare("SELECT day_of_week, start_time, end_time FROM doctor_availability WHERE doctor_id = ?");    $availability_stmt->bind_param("i", $doctor_id);
    $availability_stmt->execute();
    $availability_result = $availability_stmt->get_result();
    $availability = [];
    while ($row = $availability_result->fetch_assoc()) {
        $availability[] = $row;
    }

    // Fetch existing appointments for this doctor
    $appointments_stmt = $conn->prepare("SELECT appointment_datetime FROM appointments WHERE doctor_id = ? AND status = 'scheduled'");    $appointments_stmt->bind_param("i", $doctor_id);
    $appointments_stmt->execute();
    $appointments_result = $appointments_stmt->get_result();
    $existing_appointments = [];
    while ($row = $appointments_result->fetch_assoc()) {
        $existing_appointments[] = strtotime($row['appointment_datetime']);
    }

    $doctor_stmt->close();
    $availability_stmt->close();
}

// Close connection
$conn->close();

if ($doctor_id <= 0 || !$doctor) {
    // Redirect if no doctor ID is provided    // Redirect if no doctor ID is provided
  header("Location: /index.php"); // Use absolute path
 header("Location: " . generate_url('index.php'));
}
 ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Agenda du Médecin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f6f8;
      margin: 0;
      padding: 0;
    }

    .header {
      background: transparent;
      padding: 0 40px;
      box-shadow: none;
      margin-bottom: 20px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .logo {
      position: absolute;
      left: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
    }

    .logo img {
      height: 200px;
      width: auto;
      margin-top: 0;
      object-fit: contain;
    }

    .doctor-info {
      text-align: center;
      margin: 40px 0;
    }

    h1 {
      text-align: center;
      color: #2c3e50;
      font-size: 32px;
      margin-bottom: 10px;
    }

    .specialty {
      text-align: center;
      color: #0077b6;
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 30px;
      font-family: 'Poppins', sans-serif;
    }

    .agenda-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 20px;
      margin-top: 40px;
    }

    .day-card {
      background-color: white;
      border: 2px solid #3498db;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      transition: transform 0.2s, background-color 0.2s;
    }


    .day-card:hover {
      background-color: #e3f2fd;
      transform: translateY(-4px);
    }

    .jour {
      font-size: 18px;
      font-weight: bold;
      color: #2980b9;
    }

    .date {
      font-size: 16px;
      color: #555;
      margin-top: 5px;
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="logo">
      <img src="img/la centrale1.png" alt="LaCentrale.ma">
    </div>
  </div>

<?php if ($doctor): ?>
  <div class="doctor-info">
    <h1> <?php echo htmlspecialchars($doctor['name']); ?></h1>
    <div class="specialty"><?php echo htmlspecialchars($doctor['specialty']); ?></div>
  </div>
  <h2>Choisissez une date de rendez-vous</h2>
  
  <div class="agenda-container" id="agenda"></div>


  <?php
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

    // Determine available dates based on availability (simplified example - you would need to expand this)
    $joursDisponibles = [];
    $today = new DateTime();
    // Generate dates for the next 30 days as an example
    for ($i = 0; $i < 30; $i++) {
        $date = clone $today;
        $date->modify("+{$i} days");
        $joursDisponibles[] = $date->format('Y-m-d');
    }

    foreach ($joursDisponibles as $dateStr):
      $date = new DateTime($dateStr);
      $jourNom = $jours[$date->format('w')]; // 'w' gives day of week 0-6
      $jourNum = $date->format('d');
      $moisNom = $mois[$date->format('n') - 1]; // 'n' gives month 1-12
      $annee = $date->format('Y');

      // You would need more complex logic here to check if this date is actually available
      // based on doctor_availability and existing_appointments.
      // For now, we'll just display all generated dates.
      $is_available = true; // Placeholder for actual availability check

      if ($is_available):
  ?>
        <div class="day-card" onclick="window.location.href = '<?php echo generate_url('reservation.php'); ?>?doctor_id=<?php echo $doctor_id; ?>&datetime=<?php echo urlencode($dateStr); ?>'">
          <div class="jour"><?php echo htmlspecialchars($jourNom); ?></div>
          <div class="date"><?php echo htmlspecialchars("{$jourNum} {$moisNom} {$annee}"); ?></div>
        </div>
  <?php
      endif;
    endforeach;
  ?>

<?php else: ?>
    <p>Impossible de charger l'agenda pour le médecin spécifié.</p>
<?php endif; ?>

  <script>
    // This script can now be used for client-side interactions like modal toggles
    // or other dynamic elements that don't require fetching core agenda data.
    // The hardcoded data and fetching logic have been moved to PHP.
  </script>
  

</body>
</html>
