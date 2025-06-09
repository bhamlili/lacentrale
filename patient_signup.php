<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Patient Signup</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }
    .signup-container {
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      width: 350px;
      text-align: center;
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
    .form-group input[type="password"] {
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
  </style>
</head>
<body>

  <div class="signup-container">
    <h2>Patient Signup</h2>
    <form action="" method="post">
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit">Sign Up</button>
    </form>
    <p>- OR -</p>
    <a href="#" class="google-signup">Sign Up with Google</a>
    <a href="#" class="gmail-signup">Sign Up with Gmail</a>
  </div>

</body>
</html>