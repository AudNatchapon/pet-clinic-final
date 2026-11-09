<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $app_id = $_POST['appointment_ID'];
    $diag_desc = $_POST['diagnosisDescription'];
    $meds = $_POST['medicine_ID'] ?? [];

    try {
        $pdo->beginTransaction();

        // 1. บันทึกลงตาราง diagnoses
        $stmt_diag = $pdo->prepare("INSERT INTO diagnoses (appointment_ID, diagnosisDescription) VALUES (?, ?)");
        $stmt_diag->execute([$app_id, $diag_desc]);

        // 2. บันทึกลงตาราง treatment (วนลูปตามจำนวนยาที่สั่ง)
        $stmt_treat = $pdo->prepare("INSERT INTO treatment (medicine_ID, appointment_ID, treatment_detail) VALUES (?, ?, ?)");
        foreach ($meds as $med_id) {
            if (!empty($med_id)) {
                $stmt_treat->execute([$med_id, $app_id, $diag_desc]);
            }
        }

        $pdo->commit();

        // --- จุดที่แก้ไข: เปลี่ยนเส้นทางการ Redirect ---
        // เปลี่ยนจาก create_receipt.php เป็น treatment_history.php หรือหน้าอื่นที่ต้องการ
        header("Location: ../pages/treatment_history.php?status=success&msg=บันทึกผลการรักษาเรียบร้อยแล้ว");
        exit(); // ควรใส่ exit ทุกครั้งหลัง header เพื่อหยุดการทำงานของสคริปต์

    } catch (Exception $e) {
        $pdo->rollBack();
        // กรณี error ให้เด้งกลับไปหน้าประวัติพร้อมแจ้งเตือน
        header("Location: ../pages/treatment_history.php?status=error&msg=" . urlencode($e->getMessage()));
        exit();
    }
}