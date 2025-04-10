<?php
include_once("includes/conn.php");

$stmt = $conn->query("SELECT * FROM users LIMIT 5");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($results);
echo "</pre>";
?>
