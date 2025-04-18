<?php
session_start();
require_once 'db_connection.php';

$categories = [
    'Academic Events',
    'Cultural Events',
    'Technical Events',
    'Sports & Fitness Events',
    'College Fest & Annual Events',
    'Social & Awareness Events'
];

$selected_category = $_GET['category'] ?? '';

$query = "SELECT * FROM events";
if ($selected_category) {
    $query .= " WHERE category = ?";
}
$query .= " ORDER BY event_date DESC LIMIT 10"; // Updated limit to 10

$stmt = mysqli_prepare($conn, $query);
if ($selected_category) {
    mysqli_stmt_bind_param($stmt, "s", $selected_category);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub - Online Event Registration</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <header>
        <nav class="container header-content">
            <div class="logo">
                <h1>EventHub</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                        <li><a href="create_event.php">Create Event</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">
        <section class="hero">
            <div class="container">
                <h1>Discover Amazing Events</h1>
                <p>Join us for unforgettable experiences</p>
            </div>
        </section>

        <section class="events container">
            <h2 class="section-title">Upcoming Events</h2>
            <div class="filter-bar">
                <form action="index.php" method="get">
                    <div class="category-boxes">
                        <div class="category-box">
                            <input type="radio" id="all" name="category" value="" <?php if ($selected_category == '') echo 'checked'; ?> onchange="this.form.submit()">
                            <label for="all">All Categories</label>
                        </div>
                        <?php foreach ($categories as $category): ?>
                            <div class="category-box">
                                <input type="radio" id="<?php echo $category; ?>" name="category" value="<?php echo $category; ?>" <?php if ($selected_category == $category) echo 'checked'; ?> onchange="this.form.submit()">
                                <label for="<?php echo $category; ?>"><?php echo $category; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
            <div class="event-grid">
                <?php while ($event = mysqli_fetch_assoc($result)): ?>
                    <div class="event-card">
                        <img class="event-image" src="<?php echo htmlspecialchars($event['thumbnail']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>" width="400">
                        <div class="event-card-content">
                            <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                            <p>Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
                            <p>Time: <?php echo htmlspecialchars($event['event_time']); ?></p>
                            <p>Category: <?php echo htmlspecialchars($event['category']); ?></p>
                            <p>Available Seats: <?php echo htmlspecialchars($event['available_seats']); ?></p>
                            <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn">Book Now</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- <section class="qr-scanner container">
            <h2>Scan Your Event Ticket</h2>
            <div id="reader"></div>
            <div id="result"></div>

            <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
            <script>
                function onScanSuccess(qrMessage) {
                    document.getElementById('result').innerHTML = "Verifying ticket...";

                    fetch('verify_ticket.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ qr_data: qrMessage }) // QR data sent as JSON
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text(); // Get raw response
                    })
                    .then(text => {
                        try {
                            const data = JSON.parse(text); // Try to parse it
                            const color = data.status ? 'green' : 'red';
                            document.getElementById('result').innerHTML =
                                `<span style='color: ${color};'><strong>${data.message}</strong></span>`;
                        } catch (err) {
                            console.error("Server returned invalid JSON:", text);
                            document.getElementById('result').innerHTML =
                                `<span style='color: red;'>Error: Invalid server response.</span>`;
                        }
                    })
                    .catch(err => {
                        console.error("Fetch error:", err);
                        document.getElementById('result').innerHTML =
                            `<span style='color: red;'>Error verifying ticket. Please try again.</span>`;
                    });

                    html5QrcodeScanner.clear(); // Stop scanning
                }

                function onScanFailure(error) {
                    console.warn(`QR Code scan error: ${error}`);
                }

                let html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader",
                    { fps: 10, qrbox: 250 },
                    false
                );
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            </script>
        </section> -->
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 EventHub. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

