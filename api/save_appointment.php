<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = $_POST['pet_ID'] ?? '';
    $doctor_id = $_POST['doctor_ID'] ?? '';
    $detail = $_POST['appointment_detail'] ?? '';
    $date = $_POST['Date'] ?? '';

    if (empty($pet_id) || empty($doctor_id) || empty($date)) {
        header("Location: ../pages/appointment_form.php?status=error&msg=กรุณากรอกข้อมูลให้ครบถ้วน");
        exit();
    }

    try {
        $sql = "INSERT INTO appointments (appointment_detail, pet_ID, doctor_ID, Date) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$detail, $pet_id, $doctor_id, $date]);

        header("Location: ../pages/appointment_form.php?status=success&msg=สร้างนัดหมายเรียบร้อยแล้ว");
    } catch (Exception $e) {
        header("Location: ../pages/appointment_form.php?status=error&msg=" . urlencode($e->getMessage()));
    }
}