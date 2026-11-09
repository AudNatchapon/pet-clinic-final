<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $app_id = $_POST['appointment_ID'];
    $med_total = $_POST['total_medication_price'];
    $service_fee = $_POST['service_fee'];
    $grand_total = $_POST['grand_total'];
    $method = $_POST['payment_method'];

    try {
        $pdo->beginTransaction();

        // 1. บันทึกลงตาราง receipts
        $sql_receipt = "INSERT INTO receipts (appointment_ID, total_medication_price, service_fee, grand_total, payment_method, payment_status) 
                        VALUES (?, ?, ?, ?, ?, 'Paid')";
        $stmt = $pdo->prepare($sql_receipt);
        $stmt->execute([$app_id, $med_total, $service_fee, $grand_total, $method]);
        
        $receipt_id = $pdo->lastInsertId();

        // 2. คัดลอกรายการยาลง receipt_details (เพื่อบันทึกประวัติราคา ณ วันที่ขาย)
        $sql_details = "INSERT INTO receipt_details (receipt_ID, item_name, item_price) 
                        SELECT ?, m.name, m.price 
                        FROM treatment t 
                        JOIN medication m ON t.medicine_ID = m.medicine_ID 
                        WHERE t.appointment_ID = ?";
        $stmt_details = $pdo->prepare($sql_details);
        $stmt_details->execute([$receipt_id, $app_id]);

        $pdo->commit();
        header("Location: ../index.php?status=success&msg=ชำระเงินเรียบร้อยและปิดเคสแล้ว");

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}