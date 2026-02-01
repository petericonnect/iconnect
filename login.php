<?php
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (!$email || !$password) {
        $error = "All fields required";
    } else {
        $stmt = $conn->prepare("SELECT id,password FROM users WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows===0){
            $error = "Invalid credentials";
        } else {
            $user = $result->fetch_assoc();
            if(password_verify($password,$user["password"])){
                $_SESSION["user_id"] = $user["id"];
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid credentials";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | iConnect</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<style>
    /* ================= Reset ================= */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
}

/* ================= Body & Background ================= */
body {
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* ================= Card Wrapper ================= */
.auth-background {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* ================= Auth Card ================= */
.auth-card {
    background: #fff;
    width: 400px;
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.auth-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 60px rgba(0,0,0,0.35);
}

/* ================= Logo ================= */
.auth-logo {
    font-size: 36px;
    font-weight: bold;
    color: #ff0000;
    margin-bottom: 15px;
}

/* ================= Headings ================= */
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

/* ================= Error Message ================= */
.error {
    color: red;
    margin-bottom: 15px;
}

/* ================= Input Group ================= */
.input-group {
    text-align: left;
    margin-bottom: 20px;
    position: relative;
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

/* ================= Button ================= */
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
    transition: background 0.3s, transform 0.2s;
}

button:hover {
    background: #cc0000;
    transform: translateY(-2px);
}

/* ================= Switch Link ================= */
.switch {
    margin-top: 20px;
    font-size: 14px;
}

.switch a {
    color: #ff0000;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s;
}

.switch a:hover {
    color: #cc0000;
}

/* ================= Responsive ================= */
@media (max-width: 450px) {
    .auth-card {
        width: 90%;
        padding: 30px 20px;
    }
}

</style>
<body>
<div class="auth-background">
    <div class="auth-card">
        <div class="auth-logo">iConnect</div>
        <h2>Login to Your Account</h2>
        <p class="subtitle">Welcome back! Please login to continue</p>

        <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Your email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
              <input
              type="password"
             name="password"
             placeholder="Enter password"
             autocomplete="current-password"
             required
               >

            </div>

            <button type="submit">Login</button>
        </form>

        <p class="switch">Don't have an account? <a href="register.php">Sign Up</a></p>
    </div>
</div>
</body>
</html>

