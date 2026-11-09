<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // ลบ email ออกจาก SQL และการส่งค่า
        $sql = "UPDATE doctor SET name = ?, phonenumber = ? WHERE doctor_ID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'],
            $_POST['phonenumber'],
            $_POST['doctor_ID']
        ]);
        
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}