<?php
require_once '../config/db.php';
$q = $_GET['q'] ?? '';

$sql = "SELECT p.pet_ID, p.name AS pet_name, o.name AS owner_name 
        FROM pet p 
        JOIN owner o ON p.owner_ID = o.owner_ID 
        WHERE p.name LIKE ? OR o.name LIKE ? 
        LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$q%", "%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));