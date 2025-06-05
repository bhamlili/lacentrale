<?php
if (isset($_GET['doctor_name']) && isset($_GET['date']) && isset($_GET['time'])) {
    $doctorName = htmlspecialchars($_GET['doctor_name']);
    $date = htmlspecialchars($_GET['date']);
    $time = htmlspecialchars($_GET['time']);
} else {
    // Redirect or display an error if details are missing
    header('Location: /path/to/your/project/index.php'); // Adjust the path
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Confirmation</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <header class="header">
    <div class="logo">LaCentrale<span class="dot">.</span><span class="ma">ma</span></div>
  </header>

  <main class="confirmation-container">
    <h2>✅ Votre rendez-vous est confirmé</h2>
    <div id="confirmation-details" class="confirmation-box">
      <p>Votre rendez-vous avec Dr. <?php echo $doctorName; ?> le <?php echo $date; ?> à <?php echo $time; ?> est confirmé.</p>
    </div>
    <a href="index.html" class="btn-rdv">Retour à l'accueil</a>
  </main>

  <script src="js/confirmation.js"></script>
</body>
</html>
