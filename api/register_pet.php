<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. รับค่าที่สำคัญมาจากฟอร์ม
    $owner_id = $_POST['owner_ID'];
    $name = $_POST['name'];
    $breed_id = $_POST['breed_ID'];
    $dob = $_POST['dob']; // <--- รับค่าวันเกิด
    $gender = $_POST['gender'];

    // 2. 🌟 ฟังก์ชันคำนวณอายุ (Age Calculation)
    $calculated_age = '0 เดือน'; // ค่าเริ่มต้น
    if (!empty($dob)) {
        $birthDate = new DateTime($dob);
        $today = new DateTime('today');
        $diff = $birthDate->diff($today);
        
        // ถ้าอายุเกิน 1 ปี ให้แสดง ปี + เดือน
        if ($diff->y > 0) {
            $calculated_age = $diff->y . " ปี " . $diff->m . " เดือน"; 
        } else {
            // ถ้ายังไม่ถึงปี ให้แสดงแค่เดือน
            $calculated_age = $diff->m . " เดือน";
        }
    }

    try {
        // 3. บันทึกลงตาราง pet
        // เช็คให้แน่ใจว่าตาราง pet มีคอลัมน์ชื่อ dob, age, gender, owner_ID, breed_ID ตามนี้
        $sql = "INSERT INTO pet (owner_ID, breed_ID, name, dob, age, gender) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$owner_id, $breed_id, $name, $dob, $calculated_age, $gender]);

        // กลับไปหน้าเดิมพร้อมข้อความสำเร็จ
        header("Location: ../pages/register.php?status=success&msg=บันทึกข้อมูลสัตว์เลี้ยงเรียบร้อย");
    } catch (Exception $e) {
        header("Location: ../pages/register.php?status=error&msg=" . urlencode($e->getMessage()));
    }
}
?>