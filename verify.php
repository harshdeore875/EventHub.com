<?php
require_once 'db_connection.php';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    $query = "SELECT * FROM users WHERE email = ? AND token = ? AND is_verified = 0";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $email, $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE users SET is_verified = 1, token = NULL WHERE email = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        echo "Your email has been verified! You can now <a href='login.php'>login</a>.";
    } else {
        echo "Invalid or expired verification link.";
    }
} else {
    echo "Invalid request.";
}
?>
