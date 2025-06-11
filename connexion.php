<?php
session_start();

require_once __DIR__ . '/db_config.php';

$loginError = "";

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $link->prepare("SELECT doctor_id, password FROM doctors WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($doctor_id, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['doctor_id'] = $doctor_id;
            header("Location: gestion_med.php");
            exit();
        } else {
            $loginError = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } else {
        $loginError = "Nom d'utilisateur ou mot de passe incorrect.";
    }

    $stmt->close();
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Connexion Médecin - LaCentrale.ma</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
        body {
            background: url('img/medecin.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        main {
            width: 600px;
            max-width: 90%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 50px;
            border-radius: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            backdrop-filter: blur(5px);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-logo img {
            height: 220px;  /* Augmenté de 180px à 220px */
            width: auto;
            margin-bottom: 15px;
        }

        .login-title {
            font-size: 32px;
            text-align: center;
            color: #05668d;
            margin-bottom: 40px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 10px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #05668d;
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background-color: #0077b6;  /* Changé de #02c39a à #0077b6 */
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 30px;
        }

        .login-btn:hover {
            background-color: #005f8d;  /* Changé de #00a883 à #005f8d */
        }

        <?php if (!empty($loginError)): ?>
        .error-message {
            color: #f50a6e;
            margin: 20px 0;
            text-align: center;
            font-weight: 600;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <main>
        <div class="login-logo">
            <img src="img/La Centrale1.png" alt="LaCentrale.ma">
        </div>

        <form id="loginForm" method="POST" action="" novalidate>
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" placeholder="Entrez votre nom d'utilisateur" required />
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required />
            </div>

            <?php if (!empty($loginError)): ?>
                <div class="error-message"><?php echo $loginError; ?></div>
            <?php endif; ?>

            <button type="submit" name="login_submit" class="login-btn">Se connecter</button>
        </form>
    </main>
</body>
</html>
