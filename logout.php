<?php
session_start();
session_destroy();
header("Location: index.php");
exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="script.js"></script>
</head>
</html>
