<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$categories = [
    'Academic Events',
    'Cultural Events',
    'Technical Events',
    'Sports & Fitness Events',
    'College Fest & Annual Events',
    'Social & Awareness Events'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $event_description = $_POST['event_description'];
    $event_venue = $_POST['event_venue'];
    $category = $_POST['category'];
    $available_seats = $_POST['available_seats'];
    $user_id = $_SESSION['user_id'];

    // Handle file upload
    $thumbnail = $_FILES['thumbnail']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($thumbnail);
    move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_file);

    $query = "INSERT INTO events (event_name, event_date, event_time, event_description, event_venue, category, thumbnail, available_seats, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssi", $event_name, $event_date, $event_time, $event_description, $event_venue, $category, $target_file, $available_seats, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Failed to create event. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - EventHub</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style1.css">
    <script src="script.js"></script>
</head>
<body>
    <header>
        <nav class="container header-content">
            <div class="logo">
                <h1>EventHub</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Create Event</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="create_event.php" method="post" class="form-container" enctype="multipart/form-data">
            <div class="form-group">
                <label for="event_name">Event Name:</label>
                <input type="text" id="event_name" name="event_name" required>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date:</label>
                <input type="date" id="event_date" name="event_date" required>
            </div>
            <div class="form-group">
                <label for="event_time">Event Time:</label>
                <input type="time" id="event_time" name="event_time" required>
            </div>
            <div class="form-group">
                <label for="event_description">Event Description:</label>
                <textarea id="event_description" name="event_description" required></textarea>
            </div>
            <div class="form-group">
                <label for="event_venue">Event Venue:</label>
                <input type="text" id="event_venue" name="event_venue" required>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="thumbnail">Thumbnail:</label>
                <input type="file" id="thumbnail" name="thumbnail" required>
            </div>
            <div class="form-group">
                <label for="available_seats">Available Seats:</label>
                <input type="number" id="available_seats" name="available_seats" required>
            </div>
            <button type="submit" class="btn">Create Event</button>
        </form>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 EventHub. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>