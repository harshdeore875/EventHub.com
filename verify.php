<?php
require_once 'db_connection.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification - EventHub</title>
    <link rel="stylesheet" href="style.css"> <!-- Optional: for styling -->
</head>
<body>
    <div class="container">
        <?php
        if (isset($_GET['email']) && isset($_GET['token'])) {
            $email = $_GET['email'];
            $token = $_GET['token'];

            $query = "SELECT * FROM users WHERE email = ? AND token = ? AND is_verified = 0";
            $stmt = mysqli_prepare($conn, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $email, $token);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) > 0) {
                    $update_query = "UPDATE users SET is_verified = 1, token = NULL WHERE email = ?";
                    $update_stmt = mysqli_prepare($conn, $update_query);

                    if ($update_stmt) {
                        mysqli_stmt_bind_param($update_stmt, "s", $email);
                        mysqli_stmt_execute($update_stmt);

                        echo "<h2>Email Verified!</h2>";
                        echo "<p>Your email has been successfully verified. You can now <a href='login.php'>login</a>.</p>";
                    } else {
                        echo "<p>Something went wrong while updating your account. Please try again.</p>";
                    }
                } else {
                    echo "<p>Invalid or expired verification link.</p>";
                }
            } else {
                echo "<p>Database error. Please try again later.</p>";
            }
        } else {
            echo "<p>Invalid verification request.</p>";
        }
        ?>
    </div>
</body>
</html>
