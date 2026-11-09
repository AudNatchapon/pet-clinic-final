<?php
require_once '../config/db.php';
header('Content-Type: application/json');

try {
    $sql = "UPDATE appointments SET doctor_ID = ?, Date = ?, appointment_detail = ? WHERE appointments_ID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['doctor_ID'],
        $_POST['Date'],
        $_POST['appointment_detail'],
        $_POST['appointments_ID']
    ]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}