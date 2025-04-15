<?php
session_start();
require_once 'db_connection.php';

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
            // Update available seats
            $update_seats_query = "UPDATE events SET available_seats = available_seats - 1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_seats_query);
            mysqli_stmt_bind_param($stmt, "i", $event_id);
            mysqli_stmt_execute($stmt);

            $success = "You have successfully registered for this event!";
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
        <img src="<?php echo htmlspecialchars($event['thumbnail']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>" style="object-fit: cover;">
        <p><img src="calendar.svg" class="icon" style="width: 20px; height: 20px; vertical-align: middle;"> Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
        <p><img src="clock.svg" class="icon" style="width: 20px; height: 20px; vertical-align: middle;"> Time: <?php echo htmlspecialchars($event['event_time']); ?></p>
        <p><img src="info.svg" class="icon" style="width: 20px; height: 20px; vertical-align: middle;"> Description: <?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
        <p><img src="category.svg" class="icon" style="width: 20px; height: 20px; vertical-align: middle;"> Category: <?php echo htmlspecialchars($event['category']); ?></p>
        <p><img src="seats.svg" class="icon" style="width: 20px; height: 20px; vertical-align: middle;"> Available Seats: <?php echo htmlspecialchars($event['available_seats']); ?></p>
        
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

