<?php
include('../../includes/session.php');
include('../../includes/conn.php');
include('../../includes/functions.php');

header("Access-Control-Allow-Origin: http://ckkso0s04080wkgskwkowwso.217.65.145.182.sslip.io");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['user_id']) || empty($data['city']) || empty($data['categories']) || empty($data['subcategories'])) {
    sendJsonResponse(false, "All fields are required.");
    exit;
}


$user_id_binary = pack("H*", str_replace('-', '', $data['user_id']));

$city = sanitizeInput($data['city']);
$categories = array_slice(array_map('sanitizeInput', $data['categories']), 0, 2);
$subcategories = array_slice(array_map('sanitizeInput', $data['subcategories']), 0, 2);

try {
    $deleteStmt = $conn->prepare("DELETE FROM user_preferences WHERE user_uuid = :user_id");
    $deleteStmt->execute([":user_id" => $user_id_binary]);

    $insertStmt = $conn->prepare("
        INSERT INTO user_preferences (user_uuid, category, subcategory, city) 
        VALUES (:user_id, :category, :subcategory, :city)
    ");

    for ($i = 0; $i < max(count($categories), count($subcategories)); $i++) {
        $category = $categories[$i] ?? "Uncategorized";  
        $subcategory = $subcategories[$i] ?? "Unspecified"; 

        $insertStmt->execute([
            ":user_id" => $user_id_binary,
            ":category" => $category,
            ":subcategory" => $subcategory,
            ":city" => $city
        ]);
    }

    sendJsonResponse(true, "Preferences saved successfully!");
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    sendJsonResponse(false, "An unexpected error occurred. Please try again later.");
}
?>
