<?php
require_once '../config/db.php';
$id = $_GET['id'] ?? '';

$sql = "SELECT m.name, m.price 
        FROM treatment t 
        JOIN medication m ON t.medicine_ID = m.medicine_ID 
        WHERE t.appointment_ID = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));