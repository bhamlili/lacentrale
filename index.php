<?php
include 'config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Accueil - LaCentrale.ma</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      font-family: 'Cairo', sans-serif;
      background: #e8f4f8;
      padding-top: 20px;
    }

    .topbar {
      background: white;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      padding: 10px 30px;
      margin-bottom: 40px;
      display: flex;
      align-items: center;
    }

    .topbar img {
      height: 60px;
      width: auto;
    }

    .container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 40px;
      margin: 30px auto;
      max-width: 900px;
      flex-wrap: wrap;
    }

    .card {
      flex: 1 1 300px;
      max-width: 350px;
      background-color: #f0f9ff;
      padding: 30px 20px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      border: 2px solid #007acc;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .card.patient {
      background: #f0f9ff;
      border-color: #007acc;
    }

    .card.medecin {
      background: #f0f9ff;
      border-color: #007acc;
    }

    .card img {
      width: 140px;
      height: 140px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid white;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      margin-bottom: 30px;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

    .card a {
      display: block;
      padding: 15px 30px;
      font-size: 18px;
      margin: 20px auto 0;
      width: fit-content;
      min-width: 200px;
      background: #007acc;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      transition: background 0.3s;
      font-weight: 600;
    }

    .card a:hover {
      background: #005f9e;
    }

    /* Responsive pour petits écrans */
    @media (max-width: 700px) {
      .container {
        flex-direction: column;
        align-items: center;
      }

      .card {
        max-width: 90%;
        margin-bottom: 30px;
      }
    }
  </style>
</head>
<body>
  <div class="topbar">
    <img src="img/La Centrale1.png" alt="La Centrale Logo" />
  </div>

  <div class="container">
    <div class="card patient">
      <img src="img/patient.jpg" alt="Patient" />
      <a href="<?php echo generate_url('patient_doctor_list.php'); ?>">Réserver un rendez-vous</a>
    </div>

    <div class="card medecin">
      <img src="img/medecin.jpg" alt="Médecin" />
      <a href="<?php echo generate_url('connexion.php'); ?>">Gestion des rendez-vous</a>
    </div>
  </div>
</body>
</html>
