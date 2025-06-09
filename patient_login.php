php
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Patient Login</title>
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
    .login-container {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 300px;
        text-align: center;
    }
    .login-container h2 {
        margin-bottom: 20px;
        color: #333;
    }
    .login-form input[type="email"],
    .login-form input[type="password"] {
        width: calc(100% - 22px);
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .login-form button {
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-bottom: 10px;
    }
    .login-form button:hover {
        background-color: #0056b3;
    }
    .login-form .google-btn,
    .login-form .gmail-btn {
        background-color: #dd4b39; /* Google red */
    }
    .login-form .google-btn:hover,
    .login-form .gmail-btn:hover {
        background-color: #c23321;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Patient Login</h2>
    <form class="login-form" action="#" method="post">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <button type="button" class="google-btn">Login with Google</button>
      <button type="button" class="gmail-btn">Login with Gmail</button>
    </form>
  </div>
</body>
</html>