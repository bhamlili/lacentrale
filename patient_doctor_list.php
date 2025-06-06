<?php
// Database connection details (replace with your actual credentials)
include '/lacentrale/config.php';
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "medical_appointments";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch doctors from the database
$sql = "SELECT doctor_id, name, specialty FROM doctors";
$result = $conn->query($sql);

$doctors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Médecins à Fès</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <header class="header">
    <div class="logo">LaCentrale<span class="dot">.</span><span class="ma">ma</span></div>
    <nav>
      <ul>
        <li><a href="#">Médecin généraliste</a></li>
 <li><a href="#">Dentiste</a></li>
 <li><a href="#">Pédiatre</a></li>
 <li><a href="#" class="btn-pink">Nous rejoindre</a></li>
      </ul>
    </nav>
  </header>

  <section class="search-section">
    <input type="text" id="searchInput" placeholder="Rechercher un médecin..." />
    <button onclick="rechercher()">Rechercher</button>
  </section>

  <main>
    <h2 class="section-title">Médecins disponibles à Fès</h2>
    <div id="doctor-list" class="doctor-list">
      <?php if (!empty($doctors)): ?>
        <?php foreach ($doctors as $doctor): ?>
          <div class="doctor-card">
            <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
            <p><?php echo htmlspecialchars($doctor['specialty']); ?></p>
            <a href="<?php echo generate_url('agenda.php?doctor_id=' . $doctor['doctor_id']); ?>">Voir le profil et prendre rendez-vous</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Aucun médecin disponible pour le moment.</p>
      <?php endif; ?>
    </div>
  </main>

  <script src="js/script.js"></script>
</body>
</html>
