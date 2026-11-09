<?php
require_once '../config/db.php';
$q = $_GET['q'] ?? '';
$stmt = $pdo->prepare("SELECT owner_ID, name, phonenumber FROM owner WHERE name LIKE ? LIMIT 5");
$stmt->execute(["%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));