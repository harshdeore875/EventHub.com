<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'eventhub';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="script.js"></script>
</head>
</html>
