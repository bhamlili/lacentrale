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

$loginError = "";
$signupSuccess = "";
$signupError = "";

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT doctor_id, password FROM doctors WHERE username = ?");
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

// Handle Signup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup_submit'])) {
    $name = $_POST['signup_name'];
    $specialty = $_POST['signup_specialty'];
    $contact_info = $_POST['signup_contact_info'];
    $username = $_POST['signup_username'];
    $password = $_POST['signup_password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO doctors (name, specialty, contact_info, username, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $specialty, $contact_info, $username, $hashed_password);

    if ($stmt->execute()) {
        $signupSuccess = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
    } else {
        $signupError = "Erreur lors de l'inscription : " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Connexion Médecin - LaCentrale.ma</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>

  <header class="header">
    <div class="logo">
      LaCentrale<span class="dot">.</span><span class="ma">ma</span>
    </div>
  </header>

  <main style="max-width: 400px; margin: 80px auto; background: white; padding: 40px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <h2 style="text-align:center; color:#05668d; margin-bottom: 30px; font-weight: 700;">Connexion Médecin</h2>

    <form id="loginForm" method="POST" action="" novalidate>

      <label for="username" style="font-weight: 600; color: #333;">Nom d'utilisateur</label>
      <input
        type="text"
        id="username"
        name="username"
        placeholder="Entrez votre nom d'utilisateur"
        required
        style="width: 100%; padding: 12px; margin-top: 6px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 16px;"
      />

      <label for="password" style="font-weight: 600; color: #333; margin-top: 25px; display: block;">Mot de passe</label>
      <input
        type="password"
        id="password"
        name="password"
        placeholder="Entrez votre mot de passe"
        required
        style="width: 100%; padding: 12px; margin-top: 6px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 16px;"
      />

      <?php if (!empty($loginError)): ?>
        <div style="color: #f50a6e; margin-top: 18px; text-align:center; font-weight: 600;"><?php echo $loginError; ?></div>
      <?php endif; ?>

      <button
        type="submit"
        name="login_submit"
        style="margin-top: 35px; width: 100%; background-color: #02c39a; color: white; padding: 14px; border: none; border-radius: 8px; font-weight: 700; font-size: 18px; cursor: pointer; transition: background-color 0.3s ease;"
        onmouseover="this.style.backgroundColor='#00a883'"
        onmouseout="this.style.backgroundColor='#02c39a'"
      >
        Se connecter
      </button>
    </form>

    <h2 style="text-align:center; color:#05668d; margin-top: 60px; margin-bottom: 30px; font-weight: 700;">Inscription Médecin</h2>

    <?php if (!empty($signupSuccess)): ?>
      <div style="color: #02c39a; margin-top: 18px; text-align:center; font-weight: 600;"><?php echo $signupSuccess; ?></div>
    <?php endif; ?>

    <?php if (!empty($signupError)): ?>
      <div style="color: #f50a6e; margin-top: 18px; text-align:center; font-weight: 600;"><?php echo $signupError; ?></div>
    <?php endif; ?>

    <form id="signupForm" method="POST" action="" novalidate>
      <label for="signup_name" style="font-weight: 600; color: #333;">Nom complet</label>
      <input
        type="text"
        id="signup_name"
        name="signup_name"
        placeholder="Entrez votre nom complet"
        required
        style="width: 100%; padding: 12px; margin-top: 6px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 16px;"
      />

      <label for="signup_specialty" style="font-weight: 600; color: #333; margin-top: 25px; display: block;">Spécialité</label>
      <input
        type="text"
        id="signup_specialty"
        name="signup_specialty"
        placeholder="Entrez votre spécialité"
        style="width: 100%; padding: 12px; margin-top: 6px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 16px;"
      />

      <label for="signup_contact_info" style="font-weight: 600; color: #333; margin-top: 25px; display: block;">Informations de contact</label>
      <input
        type="text"
        id="signup_contact_info"
        name="signup_contact_info"
        placeholder="Entrez vos informations de contact"
        style="width: 100%; padding: 12px; margin-top: 6px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 16px;"
      />

      <label for="signup_username" style="font-weight: 600; color: #333; margin-top: 25px; display: block;">Nom d'utilisateur</label>
      <input
        type="text"
        id="signup_username"
        name="signup_username"
        placeholder="Choisissez un nom d'utilisateur"
        required
        style="width: 100%; padding: 12px; margin-top: 6px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 16px;"
      />

      <label for="signup_password" style="font-weight: 600; color: #333; margin-top: 25px; display: block;">Mot de passe</label>
      <input
        type="password"
        id="signup_password"
        name="signup_password"
        placeholder="Choisissez un mot de passe"
        required
        style="width: 100%; padding: 12px; margin-top: 6px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 16px;"
      />

      <button
        type="submit"
        name="signup_submit"
        style="margin-top: 35px; width: 100%; background-color: #05668d; color: white; padding: 14px; border: none; border-radius: 8px; font-weight: 700; font-size: 18px; cursor: pointer; transition: background-color 0.3s ease;"
        onmouseover="this.style.backgroundColor='#034f72'"
        onmouseout="this.style.backgroundColor='#05668d'"
      >
        S'inscrire
      </button>

    </form>
  </main>

</body>
</html>
