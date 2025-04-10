<?php
require_once "../../includes/conn.php"; 
require_once "../../includes/functions.php"; 

header("Access-Control-Allow-Origin: http://ckkso0s04080wkgskwkowwso.217.65.145.182.sslip.io");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");


$data = json_decode(file_get_contents("php://input"), true);
$event_id = $data['event_id'] ?? null;

if (!$event_id) {
    sendJsonResponse(false, "Event ID is required.");
}

try {
    //Get event details
    $stmt = $conn->prepare("SELECT title, event_photo FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        sendJsonResponse(false, "Event not found.");
    }

    $eventFolderName = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($event['title']));
    $eventFolderPath = "../../uploads/" . $eventFolderName . "/";

    //Delete event from database
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);

    //Delete event folder & images
    if (file_exists($eventFolderPath)) {
        $files = glob($eventFolderPath . '*');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($eventFolderPath);
    }

    sendJsonResponse(true, "Event deleted successfully!");
} catch (PDOException $e) {
    sendJsonResponse(false, "Database error: " . $e->getMessage());
}
?>
