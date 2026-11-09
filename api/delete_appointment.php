<?php
require_once '../config/db.php';
header('Content-Type: application/json');

try {
    $id = $_POST['id'] ?? '';
    // ลบนัดหมาย (ข้อมูลในตารางลูกอย่าง diagnoses/treatment จะถูกลบตามถ้าตั้ง ON DELETE CASCADE ไว้)
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointments_ID = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}