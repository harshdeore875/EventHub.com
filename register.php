<?php
session_start();
require_once 'db_connection.php';
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }
    // Validate phone number
    elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Invalid phone number. It should be 10 digits.";
    }
    // Validate password length
    elseif (strlen($password) < 8) {
        $error = "Password should be at least 8 characters.";
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16)); // Generate a random token
        $is_verified = 0; // Default to not verified
        $query = "INSERT INTO users (name, email, password, phone, role, token, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssi", $name, $email, $password, $phone, $role, $token, $is_verified);

        try {
            if (mysqli_stmt_execute($stmt)) {
                // Send verification email
                $mail = new PHPMailer(true);
                try {
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'harshdeore865@gmail.com';
                    $mail->Password = 'ttyb rikv ydpy tast';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('harshdeore865@gmail.com', 'Qventix');
                    $mail->addAddress($email, $name);

                    $mail->isHTML(true);
                    $mail->Subject = 'Verify your email';
                    $verificationLink = "http://localhost/microproject2/verify.php?email=$email&token=$token";
                    $mail->Body = "Click this link to verify your email: <a href='$verificationLink'>$verificationLink</a>";

                    $mail->send();
                    $success = "Registration successful! Please check your email to verify your account.";
                } catch (Exception $e) {
                    $error = "Registration successful, but verification email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $error = "The email address is already registered. Please use a different email.";
            } else {
                $error = "An unexpected error occurred. Please try again.";
            }
        }
    }
}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Qventix</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <header> 
        <nav class="container header-content">
            <div class="logo">
                <h1>Qventix</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="active">Register</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h2>Register</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form action="register.php" method="post" class="form-container">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>