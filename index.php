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
      background: #f5f5f5 url('fond-texture.jpg') repeat;
    }

    header {
      background-color: white;
      padding: 15px 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .logo {
      font-size: 24px;
      font-weight: bold;
      color: #007acc;
    }

    .logo span {
      color: #0abde3;
    }

    .container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 40px;
      margin: 60px auto;
      max-width: 900px;
      flex-wrap: wrap; /* Permet d’adapter en mobile */
    }

    .card {
      flex: 1 1 300px; /* Prend au moins 300px, s’adapte */
      max-width: 350px;
      background-color: #e0e0e0;
      padding: 40px 20px;
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
      text-align: center;
      position: relative;
      box-sizing: border-box;
    }

    .card.patient {
      background: #cce5f5;
    }

    .card.medecin {
      background: #ffd180;
    }

    .card img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid white;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      margin-bottom: 30px;
    }

    .card a {
      display: inline-block;
      padding: 15px 30px;
      font-size: 18px;
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
  <header>
    <div class="logo">LaCentrale<span>.ma</span></div>
  </header>

  <div class="container">
    <div class="card patient">
      <img src="img/patient.jpg" alt="Patient" />
      <a href="/patient_doctor_list.php">Réserver un rendez-vous</a>
    </div>

    <div class="card medecin">
      <img src="img/medecin.jpg" alt="Médecin" />
      <a href="/connexion.php">Gestion des rendez-vous</a>
    </div>
  </div>
</body>
</html>
