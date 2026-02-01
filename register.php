<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (!$username || !$email || !$password) {
        $error = "All fields are required!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "User already exists";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
            $stmt2->bind_param("sss", $username, $email, $hashed);
            $stmt2->execute();
            $_SESSION["user_id"] = $stmt2->insert_id;
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | iConnect</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<style>
    /* Reset */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
}

body {
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Card container */
.auth-background {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* Auth card */
.auth-card {
    background: #fff;
    width: 400px;
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    text-align: center;
}

/* Logo */
.auth-logo {
    font-size: 36px;
    font-weight: bold;
    color: #ff0000;
    margin-bottom: 15px;
}

/* Headings */
.auth-card h2 {
    margin-bottom: 5px;
    font-size: 24px;
    color: #333;
}

.subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 20px;
}

/* Error */
.error {
    color: red;
    margin-bottom: 15px;
}

/* Input group with box */
.input-group {
    text-align: left;
    margin-bottom: 20px;
}

.input-group label {
    font-size: 13px;
    color: #555;
    margin-bottom: 5px;
    display: block;
}

.input-group input {
    width: 100%;
    padding: 12px 15px;
    border-radius: 12px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: all 0.3s;
}

.input-group input:focus {
    border-color: #ff0000;
    outline: none;
    box-shadow: 0 0 8px rgba(255,0,0,0.2);
}

/* Button */
button {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: none;
    background: #ff0000;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background: #cc0000;
}

/* Switch */
.switch {
    margin-top: 20px;
    font-size: 14px;
}

.switch a {
    color: #ff0000;
    text-decoration: none;
    font-weight: bold;
}

</style>
<body>
<div class="auth-background">
    <div class="auth-card">
        <div class="auth-logo">iConnect</div>
        <h2>Create Your Account</h2>
        <p class="subtitle">Connect with videos you love</p>

        <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input
                type="text"
                name="username"
                 placeholder="Choose a username"
                 autocomplete="username"
                 autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
             required
            >
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Your email" required>
            </div>

             <div class="input-group">
              <label>Password</label>
             <input
             type="password"
             name="password"
             placeholder="Create password"
             autocomplete="new-password"
             required
>

            </div>

            <button type="submit">Sign Up</button>
        </form>

        <p class="switch">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>
</body>
</html>
