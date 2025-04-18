<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 0); // Disable error display
error_reporting(E_ALL); // Log errors instead of displaying them

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => false, "message" => "Unauthorized access. Please login."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $qrData = $input['qr_data'] ?? '';

    error_log("QR Data Received: " . $qrData); // Log received QR data

    // Validate QR format (example: "ticket: 123 | user: 45 | event: 67")
    preg_match('/[Tt]icket:\s*(\d+)\s*\|\s*[Uu]ser:\s*(\d+)\s*\|\s*[Ee]vent:\s*(\d+)/', $qrData, $matches);
    $registrationId = $matches[1] ?? null;
    $userId = $matches[2] ?? null;
    $eventId = $matches[3] ?? null;

    if (!$registrationId || !$userId || !$eventId) {
        error_log("Invalid QR format: " . $qrData); // Log the invalid QR data for debugging
        echo json_encode(["status" => false, "message" => "Invalid QR format."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, checked_in FROM registrations WHERE id = ? AND user_id = ? AND event_id = ?");
    if (!$stmt) {
        echo json_encode(["status" => false, "message" => "Database error."]);
        exit;
    }

    $stmt->bind_param("iii", $registrationId, $userId, $eventId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if ($row['checked_in']) {
            echo json_encode(["status" => false, "message" => "Ticket already used."]);
        } else {
            $update = $conn->prepare("UPDATE registrations SET checked_in = 1 WHERE id = ?");
            $update->bind_param("i", $registrationId);
            $update->execute();

            echo json_encode(["status" => true, "message" => "Ticket scanned successfully."]);
        }
    } else {
        echo json_encode(["status" => false, "message" => "Invalid ticket or mismatched user/event."]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Invalid request method."]);
}
