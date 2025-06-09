<?php
include './db_config.php';
include './config.php';


// Fetch doctors from the database
$sql = "SELECT doctor_id, name, specialty FROM doctors";
$result = $conn->query($sql);

$doctors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Get unique specialties for the dropdown
$specialties = array_unique(array_column($doctors, 'specialty'));
sort($specialties);

// Assuming generate_url function exists in config.php



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
<div class="header-controls">

<nav>
<ul>
<li><a href="/patient_signup.php" class="btn-pink">Nous rejoindre</a></li>
</ul>
</nav>
</div>
</header>

<section class="filter-section">
    <label for="specialtyFilter">Filtrer par spécialité:</label>
    <select id="specialtyFilter">
      <option value="all">Toutes les spécialités</option>
      <?php foreach ($specialties as $specialty): ?>
        <option value="<?php echo htmlspecialchars($specialty); ?>"><?php echo htmlspecialchars($specialty); ?></option>
      <?php endforeach; ?>
    </select>
  </section>


<main>
    <h2 class="section-title">Médecins disponibles à Fès</h2>
    <div id="doctor-list" class="doctor-list">
      <?php if (!empty($doctors)): ?>
        <?php foreach ($doctors as $doctor): ?>
          <div class="doctor-card">
<div class="professional" data-specialty="<?php echo htmlspecialchars($doctor['specialty']); ?>">
            <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>

            <p><?php echo htmlspecialchars($doctor['specialty']); ?></p>
            <a href="<?php echo generate_url('agenda.php?doctor_id=' . $doctor['doctor_id']); ?>" class="book-appointment-button">Voir le profil et prendre rendez-vous</a>
 </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Aucun médecin disponible pour le moment.</p>
      <?php endif; ?>
    </div>
  </main>
<script>
    document.getElementById('specialtyFilter').addEventListener('change', function() {
      var selectedSpecialty = this.value;
      var doctors = document.querySelectorAll('.professional');

      doctors.forEach(function(doctor) {
        var doctorSpecialty = doctor.getAttribute('data-specialty');
        if (selectedSpecialty === 'all' || doctorSpecialty === selectedSpecialty) {
          doctor.style.display = 'block';
        } else {
          doctor.style.display = 'none';
        }
      });
    });
  </script>

</body>
</html>
