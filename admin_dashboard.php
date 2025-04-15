<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$events_query = "SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC";
$stmt = mysqli_prepare($conn, $events_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$events_result = mysqli_stmt_get_result($stmt);

$total_events_query = "SELECT COUNT(*) as total_events FROM events WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $total_events_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$total_events_result = mysqli_stmt_get_result($stmt);
$total_events = mysqli_fetch_assoc($total_events_result)['total_events'];

$participants_query = "SELECT COUNT(DISTINCT user_id) as total_participants FROM registrations WHERE event_id IN (SELECT id FROM events WHERE user_id = ?)";
$stmt = mysqli_prepare($conn, $participants_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$participants_result = mysqli_stmt_get_result($stmt);
$total_participants = mysqli_fetch_assoc($participants_result)['total_participants'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_event'])) {
        $event_id = $_POST['event_id'];
        
        // Delete related registrations first
        $delete_registrations_query = "DELETE FROM registrations WHERE event_id = ?";
        $stmt = mysqli_prepare($conn, $delete_registrations_query);
        mysqli_stmt_bind_param($stmt, "i", $event_id);
        mysqli_stmt_execute($stmt);
        
        // Delete the event
        $delete_event_query = "DELETE FROM events WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_event_query);
        mysqli_stmt_bind_param($stmt, "i", $event_id);
        mysqli_stmt_execute($stmt);
        
        header("Location: admin_dashboard.php");
        exit();
    } elseif (isset($_POST['edit_event'])) {
        $event_id = $_POST['event_id'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $event_venue = $_POST['event_venue'];
        $update_query = "UPDATE events SET event_date = ?, event_time = ?, event_venue = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sssi", $event_date, $event_time, $event_venue, $event_id);
        mysqli_stmt_execute($stmt);
        header("Location: admin_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EventHub</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="additional_styles.css">
    <link rel="stylesheet" href="style1.css">
    <script src="script.js"></script>
</head>
<body>
    <header>
        <nav class="container header-content">
            <div class="logo">
                <h1>EventHub Admin</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="create_event.php">Create Event</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-dashboard container">
        <h2>Admin Dashboard</h2>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3><?php echo $total_events; ?></h3>
                <p>Total Events</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_participants; ?></h3>
                <p>Total Participants</p>
            </div>
        </div>

        <h2>Event List</h2>
        <div class="event-list">
            <table>
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_time']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_venue'] ?? ''); ?></td>
                            <td>
                                <form action="admin_dashboard.php" method="post" style="display:inline;">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" name="delete_event" class="btn btn-danger">Delete</button>
                                </form>
                                <button class="btn btn-primary" onclick="showEditForm(<?php echo $event['id']; ?>)">Edit</button>
                                <button class="btn btn-secondary" onclick="showParticipants(<?php echo $event['id']; ?>)">Show Participants</button>
                            </td>
                        </tr>
                        <tr id="edit-form-<?php echo $event['id']; ?>" style="display:none;">
                            <td colspan="5">
                                <form action="admin_dashboard.php" method="post" class="form-container">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <div class="form-group">
                                        <label for="event_date">Event Date:</label>
                                        <input type="date" id="event_date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="event_time">Event Time:</label>
                                        <input type="time" id="event_time" name="event_time" value="<?php echo $event['event_time']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="event_venue">Event Venue:</label>
                                        <input type="text" id="event_venue" name="event_venue" value="<?php echo $event['event_venue'] ?? ''; ?>" required>
                                    </div>
                                    <button type="submit" name="edit_event" class="btn">Save Changes</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="participants-<?php echo $event['id']; ?>" style="display:none;">
                            <td colspan="5">
                                <h3>Participants</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $participants_query = "SELECT u.name, u.email, u.phone FROM users u JOIN registrations r ON u.id = r.user_id WHERE r.event_id = ?";
                                        $stmt = mysqli_prepare($conn, $participants_query);
                                        mysqli_stmt_bind_param($stmt, "i", $event['id']);
                                        mysqli_stmt_execute($stmt);
                                        $participants_result = mysqli_stmt_get_result($stmt);
                                        while ($participant = mysqli_fetch_assoc($participants_result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($participant['name']); ?></td>
                                                <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                                <td><?php echo htmlspecialchars($participant['phone']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 EventHub. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function showEditForm(eventId) {
            var form = document.getElementById('edit-form-' + eventId);
            if (form.style.display === 'none') {
                form.style.display = 'table-row';
            } else {
                form.style.display = 'none';
            }
        }

        function showParticipants(eventId) {
            var participants = document.getElementById('participants-' + eventId);
            if (participants.style.display === 'none') {
                participants.style.display = 'table-row';
            } else {
                participants.style.display = 'none';
            }
        }
    </script>
</body>
</html>

