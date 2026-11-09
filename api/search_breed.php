<?php
require_once '../config/db.php';
$q = $_GET['q'] ?? '';
$stmt = $pdo->prepare("SELECT breed_ID, breed_detail FROM breed WHERE breed_detail LIKE ? LIMIT 5");
$stmt->execute(["%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));