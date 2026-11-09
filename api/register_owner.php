<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. รับค่าจากฟอร์ม
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phonenumber'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    // 2. ตรวจสอบข้อมูลเบื้องต้น
    if (empty($name)) {
        header("Location: ../pages/register.php?status=error&msg=กรุณาระบุชื่อเจ้าของ");
        exit();
    }

    // 3. เริ่มกระบวนการบันทึก
    try {
        $sql = "INSERT INTO owner (name, phonenumber, email, address) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $phone, $email, $address]);

        // บันทึกสำเร็จ
        header("Location: ../pages/register.php?status=success&msg=บันทึกข้อมูลเจ้าของเรียบร้อยแล้ว");
        exit();

    } catch (Exception $e) {
        // หากเกิด Error ใน Database
        header("Location: ../pages/register.php?status=error&msg=" . urlencode($e->getMessage()));
        exit();
    }
} // <--- ปีกกาปิดของ if ($_SERVER...) ตัวสุดท้ายต้องอยู่ตรงนี้
?>