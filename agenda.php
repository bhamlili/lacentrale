<?php
include 'config.php';
include './db_config.php';

// Get doctor ID from URL
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

if ($doctor_id > 0) {
    // Fetch doctor information
    $doctor_sql = "SELECT name, specialty FROM doctors WHERE doctor_id = ?";    $doctor_stmt = $link->prepare($doctor_sql);
    $doctor_stmt->bind_param("i", $doctor_id);
    $doctor_stmt->execute();
    $doctor_result = $doctor_stmt->get_result();
    $doctor = $doctor_result->fetch_assoc();

    // Fetch doctor's regular working hours
    $availability_sql = "SELECT day_of_week, start_time, end_time FROM doctor_availability WHERE doctor_id = ?";
    $availability_stmt = $link->prepare($availability_sql);    $availability_stmt->bind_param("i", $doctor_id);
    $availability_stmt->execute();
    $availability_result = $availability_stmt->get_result();
    $availability = [];
    while ($row = $availability_result->fetch_assoc()) {
        $availability[] = $row;
    }

    // Fetch existing appointments for this doctor
    $appointments_sql = "SELECT appointment_datetime FROM appointments WHERE doctor_id = ? AND status = 'scheduled'";    $appointments_stmt = $link->prepare($appointments_sql);
    $appointments_stmt->bind_param("i", $doctor_id);
    $appointments_stmt->execute();
    $appointments_result = $appointments_stmt->get_result();
    $existing_appointments = [];
    while ($row = $appointments_result->fetch_assoc()) {
        $existing_appointments[] = strtotime($row['appointment_datetime']);
    }

    $doctor_stmt->close();
    $availability_stmt->close();
} else {
    // Redirect if no doctor ID is provided    // Redirect if no doctor ID is provided
    header("Location: /index.php"); // Use absolute path
 header("Location: " . generate_url('index.php'));
}

// Close connection
$link->close();
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
      padding: 40px;
    }

    h1 {
      text-align: center;
      color: #2c3e50;
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

  <h1>Agenda de <?php echo htmlspecialchars($doctor['name']); ?></h1>
  <p>Spécialité: <?php echo htmlspecialchars($doctor['specialty']); ?></p>
  <h2>Choisissez une date de rendez-vous</h2>
  
  <div class="agenda-container" id="agenda"></div>


  <script>
    const jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    const mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

    const joursDisponibles = ["2025-06-03", "2025-06-05", "2025-06-07", "2025-06-10"];
    const agendaDiv = document.getElementById("agenda");

    joursDisponibles.forEach(dateStr => {
      const date = new Date(dateStr);
      const jourNom = jours[date.getDay()];
      const jourNum = date.getDate();
      const moisNom = mois[date.getMonth()];
      const annee = date.getFullYear();

      const card = document.createElement("div");
      card.className = "day-card";
      card.innerHTML = `
        <div class="jour">${jourNom}</div>
        <div class="date">${jourNum} ${moisNom} ${annee}</div>
      `;
      card.onclick = () => {
        window.location.href = `<?php echo generate_url('reservation.php'); ?>?medecin=...&jour=...&heure=...`;
      };

      agendaDiv.appendChild(card);
    });
    
  </script>
  

</body>
</html>
