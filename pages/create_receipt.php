<?php 
require_once '../config/db.php';

$app_id = $_GET['app_id'] ?? '';

if (empty($app_id)) {
    header("Location: ../index.php");
    exit();
}

// 1. ดึงข้อมูลสัตว์เลี้ยงและเจ้าของ
$stmt = $pdo->prepare("SELECT a.appointments_ID, p.name AS pet_name, o.name AS owner_name 
                       FROM appointments a 
                       JOIN pet p ON a.pet_ID = p.pet_ID 
                       JOIN owner o ON p.owner_ID = o.owner_ID 
                       WHERE a.appointments_ID = ?");
$stmt->execute([$app_id]);
$info = $stmt->fetch();

// 2. ดึงรายการยาที่หมอสั่งและราคา (Join medication)
$stmt_meds = $pdo->prepare("SELECT m.name, m.price 
                            FROM treatment t 
                            JOIN medication m ON t.medicine_ID = m.medicine_ID 
                            WHERE t.appointment_ID = ?");
$stmt_meds->execute([$app_id]);
$med_items = $stmt_meds->fetchAll();

$total_medication = 0;
foreach($med_items as $item) {
    $total_medication += $item['price'];
}
$service_fee = 100.00; // ค่าธรรมเนียมแพทย์คงที่
$grand_total = $total_medication + $service_fee;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>การเงินและใบเสร็จ | PetCare V2</title>
    <link rel="stylesheet" href="../css/petcare-token.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: var(--font-family-base); background-color: var(--color-background); color: var(--color-black-1000); }
        .receipt-card { border: 1px solid var(--color-gray-400); border-radius: var(--radius-md); box-shadow: 0 4px 14px rgba(0,0,0,0.06); max-width: 600px; margin: auto; background-color: var(--color-surface) !important; }
        .billing-line { border-bottom: 1px dashed var(--color-gray-400); padding: var(--Grid-8) 0; }
        .total-section { background: var(--tint-primary); border-radius: var(--radius-md); padding: var(--space-md); }
        .btn-primary { background-color: var(--button-primary-bg) !important; border-color: var(--button-primary-bg) !important; color: var(--button-primary-text) !important; border-radius: var(--button-radius) !important; }
        .btn { border-radius: var(--button-radius) !important; }
        .text-primary { color: var(--color-primary) !important; }
        .form-select { border-radius: var(--radius-md) !important; border-color: var(--input-border) !important; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card receipt-card p-4 p-md-5 bg-white">
        <div class="text-center mb-4">
            <h3 class="fw-bold mb-0">ใบแจ้งชำระเงิน</h3>
            <p class="text-muted small">ID นัดหมาย: #<?= htmlspecialchars($app_id) ?></p>
        </div>

        <div class="mb-4">
            <div class="row">
                <div class="col-6"><small class="text-muted">ชื่อสัตว์เลี้ยง:</small><br><strong>🐾 <?= htmlspecialchars($info['pet_name']) ?></strong></div>
                <div class="col-6 text-end"><small class="text-muted">เจ้าของ:</small><br><strong><?= htmlspecialchars($info['owner_name']) ?></strong></div>
            </div>
        </div>

        <h6 class="fw-bold border-bottom pb-2">รายการค่าใช้จ่าย</h6>
        <div class="mb-4">
            <?php foreach($med_items as $item): ?>
                <div class="d-flex justify-content-between billing-line">
                    <span><?= htmlspecialchars($item['name']) ?></span>
                    <span>฿<?= number_format($item['price'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between billing-line">
                <span>ค่าบริการตรวจรักษา</span>
                <span>฿<?= number_format($service_fee, 2) ?></span>
            </div>
        </div>

        <div class="total-section mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">ยอดรวมสุทธิ:</span>
                <h2 class="fw-bold text-primary mb-0">฿<?= number_format($grand_total, 2) ?></h2>
            </div>
        </div>

        <form action="../api/save_receipt.php" method="POST">
            <input type="hidden" name="appointment_ID" value="<?= $app_id ?>">
            <input type="hidden" name="total_medication_price" value="<?= $total_medication ?>">
            <input type="hidden" name="service_fee" value="<?= $service_fee ?>">
            <input type="hidden" name="grand_total" value="<?= $grand_total ?>">

            <div class="mb-4">
                <label class="form-label fw-bold">ช่องทางการชำระเงิน</label>
                <select name="payment_method" class="form-select rounded-3">
                    <option value="Cash">เงินสด (Cash)</option>
                    <option value="Transfer">เงินโอน (Transfer)</option>
                    <option value="Credit Card">บัตรเครดิต (Credit Card)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow">
                ยืนยันการรับชำระเงินและปิดเคส
            </button>
        </form>
    </div>
</div>

</body>
</html>