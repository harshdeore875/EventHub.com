<?php
session_start();
require_once 'db_connection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Exception\WriterException;

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$event_id = $_GET['id'];
$query = "SELECT * FROM events WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);

if (!$event) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_email = $_SESSION['user_email'] ?? null;

    if (!$user_email) {
        header("Location: login.php");
        exit();
    }

    $check_registration_query = "SELECT * FROM registrations WHERE event_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_registration_query);
    mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);
    mysqli_stmt_execute($stmt);
    $registration_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($registration_result) == 0) {
        $query = "INSERT INTO registrations (event_id, user_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $registration_id = mysqli_insert_id($conn);

            $update_seats_query = "UPDATE events SET available_seats = available_seats - 1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_seats_query);
            mysqli_stmt_bind_param($stmt, "i", $event_id);
            mysqli_stmt_execute($stmt);

            try {
                // Generate QR Code using lowercase keys
                $qrContent = "ticket: $registration_id | user: $user_id | event: {$event['id']}";
                $qrCode = new QrCode($qrContent);
                $writer = new PngWriter();
                $qrResult = $writer->write($qrCode);

                $qrPath = __DIR__ . "/qrcodes/ticket_$registration_id.png";
                if (!file_exists(__DIR__ . '/qrcodes')) mkdir(__DIR__ . '/qrcodes', 0777, true);
                file_put_contents($qrPath, $qrResult->getString());

                // Store QR code path in the database
                $update_qr_query = "UPDATE registrations SET qr_code_path = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_qr_query);
                mysqli_stmt_bind_param($stmt, "si", $qrPath, $registration_id);
                mysqli_stmt_execute($stmt);
            } catch (WriterException $e) {
                $error = "Failed to generate QR code. Please contact support.";
                error_log("QR Code Generation Error: " . $e->getMessage());
            }

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'harshdeore865@gmail.com';
                $mail->Password = 'ttyb rikv ydpy tast';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('harshdeore865@gmail.com', 'Event Team');
                $mail->addAddress($user_email, $_SESSION['user_name']);
                $mail->addAttachment($qrPath, "ticket_$registration_id.png");

                $mail->isHTML(true);
                $mail->Subject = "Your Ticket for {$event['event_name']}";
                $mail->Body = "
                    <h2>Hello {$_SESSION['user_name']},</h2>
                    <p>Thank you for registering for <strong>{$event['event_name']}</strong>.</p>
                    <p><strong>Date:</strong> {$event['event_date']}</p>
                    <p>Scan the attached QR code at the event.</p>
                    <br><small>This ticket is valid for single use only.</small>
                ";

                $mail->send();
                $success = "You have successfully registered for this event! A ticket has been sent to your email.";
            } catch (Exception $e) {
                $error = "Registration successful, but the ticket email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Registration failed. Please try again.";
        }
    } else {
        $error = "You have already registered for this event.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['event_name']); ?> - EventHub</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style1.css">
    <script src="script.js"></script>
</head>
<body>
    <div class="container event-details">
        <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>
        <img class="eventcover" src="<?php echo htmlspecialchars($event['thumbnail']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
        <p><img src="calendar.svg" class="icon"> Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
        <p><img src="clock.svg" class="icon"> Time: <?php echo htmlspecialchars($event['event_time']); ?></p>
        <p><img src="info.svg" class="icon"> Description: <?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
        <p><img src="category.svg" class="icon"> Category: <?php echo htmlspecialchars($event['category']); ?></p>
        <p><img src="seats.svg" class="icon"> Available Seats: <?php echo htmlspecialchars($event['available_seats']); ?></p>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php elseif (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php else: ?>
                <form action="event_details.php?id=<?php echo $event_id; ?>" method="post">
                    <button type="submit" class="btn">Register for Event</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <p>Please <a href="login.php">login</a> to register for this event.</p>
        <?php endif; ?>

        <a href="index.php" class="btn">Back to Events</a>
    </div>
</body>
</html>
