<?php
include './db_config.php';
include './config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_complet = $_POST['name'];
    $email = $_POST['email'];
    $mot_de_passe = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $telephone = $_POST['phone'];

    $sql = "INSERT INTO patientscnx (nom_complet, email, mot_de_passe, telephone) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nom_complet, $email, $mot_de_passe, $telephone);
    
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $nom_complet;
        $message = "Compte créé avec succès! Bienvenue " . htmlspecialchars($nom_complet);
        $messageClass = "success-message";
    } else {
        $message = "Erreur lors de l'inscription: " . $conn->error;
        $messageClass = "error-message";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Patient - LaCentrale.ma</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            position: relative;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            background-attachment: fixed;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('img/patient.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: blur(8px);
            z-index: -1;
        }

        .logo img {
            height: 200px; /* Augmenté de 150px à 200px */
            width: auto;
            margin: -80px auto 20px; /* Ajusté la marge négative */
            display: block;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .signup-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            width: 350px;
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .signup-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
          margin-bottom: 15px;
          text-align: left;
        }
        .form-group label {
          display: block;
          margin-bottom: 5px;
          font-weight: bold;
          color: #555;
        }
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="text"],
        .form-group input[type="tel"] {
          width: calc(100% - 22px);
          padding: 10px;
          border: 1px solid #ddd;
          border-radius: 4px;
          font-size: 16px;
        }
        button {
          background-color: #3498db;
          color: white;
          padding: 10px 20px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          font-size: 16px;
          transition: background-color 0.3s ease;
          width: 100%;
          margin-bottom: 10px;
        }
        button:hover {
          background-color: #2980b9;
        }
        .google-signup, .gmail-signup {
          display: block;
          width: 100%;
          padding: 10px;
          border: 1px solid #ddd;
          border-radius: 4px;
          text-decoration: none;
          color: #555;
          background-color: #eee;
          margin-bottom: 10px;
          transition: background-color 0.3s ease;
        }
        .google-signup:hover, .gmail-signup:hover {
          background-color: #ddd;
        }
        .message {
          margin-bottom: 20px;
          padding: 10px;
          background-color: #dff0d8;
          color: #3c763d;
          border: 1px solid #d6e9c6;
          border-radius: 4px;
        }

        .btn-pink {
            background-color: #ff4081;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .btn-pink:hover {
            background-color: #e91e63;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        nav ul li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: -20px -20px 20px -20px;
            text-align: center;
            font-weight: bold;
            animation: slideDown 0.5s ease-out;
        }

        .error-message {
            background-color: #f44336;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: -20px -20px 20px -20px;
            text-align: center;
            font-weight: bold;
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <?php if ($message): ?>
            <div class="<?php echo $messageClass; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="logo">
            <img src="img/la centrale1.png" alt="LaCentrale.ma">
        </div>
        <h2>Inscription Patient</h2>
        
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Nom complet:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="phone">Téléphone:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <button type="submit" class="btn-pink">S'inscrire</button>
        </form>
    </div>

</body>
</html>