<?php 
require_once '../config/db.php';

// 1. ดึงเคสที่หมอตรวจเสร็จแล้ว (มีในตาราง diagnoses) แต่ยังไม่จ่ายเงิน (ไม่มีในตาราง receipts)
$sql_pending = "SELECT a.appointments_ID, a.Date, p.pet_ID, p.name AS pet_name, 
                       o.owner_ID, o.name AS owner_name, o.phoneNumber AS owner_phone,
                       d.doctor_ID, d.name AS doctor_name, diag.diagnosisDescription
                FROM appointments a
                JOIN pet p ON a.pet_ID = p.pet_ID
                JOIN owner o ON p.owner_ID = o.owner_ID
                JOIN diagnoses diag ON a.appointments_ID = diag.appointment_ID
                LEFT JOIN doctor d ON a.doctor_ID = d.doctor_ID
                LEFT JOIN receipts r ON a.appointments_ID = r.appointment_ID
                WHERE r.receipt_ID IS NULL
                ORDER BY a.Date ASC, a.appointments_ID ASC";
$pending_bills = $pdo->query($sql_pending)->fetchAll(PDO::FETCH_ASSOC);

// 2. ดึงรายการยาของแต่ละนัดหมาย (สำหรับเคสรอชำระเงิน)
$sql_meds = "SELECT t.appointment_ID, m.name AS med_name, m.price 
             FROM treatment t 
             JOIN medication m ON t.medicine_ID = m.medicine_ID";
$med_stmt = $pdo->query($sql_meds);
$all_meds_raw = $med_stmt->fetchAll(PDO::FETCH_ASSOC);

$pending_meds_map = [];
foreach ($all_meds_raw as $m) {
    $app_id = (int)$m['appointment_ID'];
    if (!isset($pending_meds_map[$app_id])) {
        $pending_meds_map[$app_id] = [];
    }
    $pending_meds_map[$app_id][] = [
        'name' => $m['med_name'],
        'price' => (float)$m['price']
    ];
}

// 3. ดึงประวัติบิลที่ชำระเงินแล้ว (Paid Receipts History)
$sql_history = "SELECT r.receipt_ID, r.appointment_ID, r.total_medication_price, r.service_fee, r.grand_total,
                       r.payment_method, r.payment_status, r.payment_date,
                       a.Date AS appointment_date, p.pet_ID, p.name AS pet_name,
                       o.owner_ID, o.name AS owner_name, o.phoneNumber AS owner_phone,
                       d.name AS doctor_name, diag.diagnosisDescription
                FROM receipts r
                JOIN appointments a ON r.appointment_ID = a.appointments_ID
                JOIN pet p ON a.pet_ID = p.pet_ID
                JOIN owner o ON p.owner_ID = o.owner_ID
                LEFT JOIN doctor d ON a.doctor_ID = d.doctor_ID
                LEFT JOIN diagnoses diag ON a.appointments_ID = diag.appointment_ID
                WHERE r.payment_status = 'Paid'
                ORDER BY r.receipt_ID DESC";
$paid_history = $pdo->query($sql_history)->fetchAll(PDO::FETCH_ASSOC);

// 4. ดึงรายการสินค้าในใบเสร็จย้อนหลัง (receipt_details) ถ้ามี
$sql_receipt_details = "SELECT receipt_ID, item_name, item_price FROM receipt_details";
$receipt_details_map = [];
try {
    $rd_stmt = $pdo->query($sql_receipt_details);
    $rd_raw = $rd_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rd_raw as $rd) {
        $rId = (int)$rd['receipt_ID'];
        if (!isset($receipt_details_map[$rId])) {
            $receipt_details_map[$rId] = [];
        }
        $receipt_details_map[$rId][] = [
            'name' => $rd['item_name'],
            'price' => (float)$rd['item_price']
        ];
    }
} catch (Exception $e) {
    // ถ้าไม่มีตาราง receipt_details จะ fallback ไปใช้ treatment
}

// จัดเตรียมข้อมูล JSON ส่งให้ Client-side Rendering
$pending_json = [];
foreach ($pending_bills as $b) {
    $aId = (int)$b['appointments_ID'];
    $medList = $pending_meds_map[$aId] ?? [];
    $totalMed = 0;
    foreach ($medList as $item) {
        $totalMed += $item['price'];
    }
    $serviceFee = 100.00; // ค่าบริการตรวจรักษามาตรฐาน
    $grandTotal = $totalMed + $serviceFee;

    $pending_json[] = [
        'app_id' => $aId,
        'date' => $b['Date'],
        'pet_name' => $b['pet_name'] ?? 'ไม่ระบุ',
        'owner_name' => $b['owner_name'] ?? 'ไม่ระบุ',
        'owner_phone' => $b['owner_phone'] ?? '-',
        'doctor_name' => $b['doctor_name'] ?? 'สัตวแพทย์เวร',
        'diagnosis' => $b['diagnosisDescription'] ?? 'ตรวจสุขภาพทั่วไป',
        'meds' => $medList,
        'total_med' => $totalMed,
        'service_fee' => $serviceFee,
        'grand_total' => $grandTotal
    ];
}

$history_json = [];
foreach ($paid_history as $h) {
    $rId = (int)$h['receipt_ID'];
    $aId = (int)$h['appointment_ID'];
    
    // รายการยาจาก receipt_details หรือ fallback treatment
    $items = $receipt_details_map[$rId] ?? ($pending_meds_map[$aId] ?? []);

    $history_json[] = [
        'receipt_id' => $rId,
        'app_id' => $aId,
        'receipt_code' => 'REC-' . str_pad($rId, 5, '0', STR_PAD_LEFT),
        'payment_date' => $h['payment_date'],
        'appointment_date' => $h['appointment_date'],
        'pet_name' => $h['pet_name'] ?? 'ไม่ระบุ',
        'owner_name' => $h['owner_name'] ?? 'ไม่ระบุ',
        'owner_phone' => $h['owner_phone'] ?? '-',
        'doctor_name' => $h['doctor_name'] ?? 'สัตวแพทย์เวร',
        'diagnosis' => $h['diagnosisDescription'] ?? '-',
        'payment_method' => $h['payment_method'] ?? 'Cash',
        'total_med' => (float)$h['total_medication_price'],
        'service_fee' => (float)$h['service_fee'],
        'grand_total' => (float)$h['grand_total'],
        'items' => $items
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>การเงินและใบเสร็จ | PetCare Portal</title>
<!-- โหลด Design Tokens จาก design.md -->
<link rel="stylesheet" href="../css/petcare-token.css">
<style>
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body {
  font-family: var(--font-family-base);
  font-size: var(--font-size-md);
  color: var(--color-black-1000);
  background: var(--color-background);
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}
h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-family-base);
  margin: 0;
}
button { font-family: inherit; cursor: pointer; }
input, select, textarea { font-family: inherit; }
a { color: inherit; text-decoration: none; }

/* Layout Shell */
.app { display: flex; min-height: 100vh; }

.sidebar {
  width: 240px;
  flex-shrink: 0;
  background: var(--color-surface);
  border-right: 1px solid var(--color-gray-400);
  padding: var(--space-lg) var(--space-md);
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  z-index: 30;
}
.brand {
  font-size: var(--font-size-lg);
  font-weight: 700;
  color: var(--color-black-1000);
  padding: 0 var(--Grid-8) var(--space-lg);
}
.brand span { color: var(--color-primary); }

.nav-group { margin: 0 0 var(--space-lg) 0; padding: 0; }
.nav-group:last-child { margin-bottom: 0; }
.nav-group ul { list-style: none; margin: 0; padding: 0; }
.nav-group-label {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .55;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .04em;
  padding: 0 var(--Grid-8);
  margin: 0 0 var(--Grid-8) 0;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: var(--Grid-8);
  padding: var(--Grid-8) var(--Grid-8);
  border-radius: var(--radius-md);
  font-size: var(--font-size-md);
  font-weight: 400;
  color: var(--color-black-1000);
  text-decoration: none;
  margin-bottom: calc(var(--Grid-8) / 2);
  position: relative;
  border: 1px solid transparent;
  transition: background .15s ease, border-color .15s ease;
}
.nav-icon {
  width: var(--space-24);
  height: var(--space-24);
  text-align: center;
  flex-shrink: 0;
  font-size: var(--space-16);
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.nav-link:hover { background: var(--color-background); }
.nav-link[aria-current="page"] {
  background: var(--color-background);
  border-color: var(--color-primary);
  font-weight: 700;
}
.nav-badge {
  margin-left: auto;
  background: var(--color-danger);
  color: var(--color-black-1000);
  font-size: var(--font-size-sm);
  font-weight: 700;
  padding: 0 var(--Grid-8);
  border-radius: var(--chip-radius);
  line-height: 20px;
  height: 20px;
}

.main-wrap { flex: 1; min-width: 0; display: flex; flex-direction: column; }

.topbar {
  display: none;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-md);
  border-bottom: 1px solid var(--color-gray-400);
  position: sticky;
  top: 0;
  background: var(--color-surface);
  z-index: 20;
}
.topbar-brand { display: flex; align-items: center; gap: var(--Grid-8); font-weight: 700; }
.hamburger-btn {
  border: 1px solid var(--color-gray-400);
  background: var(--color-surface);
  border-radius: var(--radius-md);
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
}

main {
  padding: var(--space-lg) var(--space-lg) 64px;
  max-width: 1600px;
  width: 100%;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--space-md);
  margin-bottom: var(--space-lg);
  flex-wrap: wrap;
}
.page-header h1 {
  font-size: var(--font-size-lg);
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: var(--space-md);
}
.page-header-icon {
  width: var(--thumbnail-size);
  height: var(--thumbnail-size);
  border-radius: var(--radius-md);
  background: var(--tint-success);
  color: var(--color-success);
  display: flex; align-items: center; justify-content: center;
  font-size: var(--font-size-lg);
  flex-shrink: 0;
}
.page-header p {
  margin: calc(var(--Grid-8) / 2) 0 0;
  color: var(--color-black-1000);
  opacity: .7;
  font-size: var(--font-size-sm);
}

/* Segmented Tabs */
.segmented-tabs {
  display: inline-flex;
  background: var(--color-surface);
  padding: calc(var(--Grid-8) / 2);
  border-radius: var(--chip-radius);
  border: 1px solid var(--color-gray-400);
  gap: calc(var(--Grid-8) / 2);
}
.tab-btn {
  border: none;
  background: transparent;
  padding: var(--Grid-8) var(--space-md);
  border-radius: var(--chip-radius);
  font-size: var(--font-size-sm);
  font-weight: 700;
  color: var(--color-black-1000);
  display: inline-flex;
  align-items: center;
  gap: var(--Grid-8);
  transition: all .15s ease;
}
.tab-btn.active {
  background: var(--color-primary);
  color: var(--color-white);
}
.tab-badge {
  background: var(--color-danger);
  color: var(--color-black-1000);
  border-radius: var(--chip-radius);
  padding: 1px var(--Grid-8);
  font-size: var(--font-size-sm);
}

/* Master-Detail 2 Columns */
.master-detail-grid {
  display: grid;
  grid-template-columns: 5fr 7fr;
  gap: var(--space-lg);
  align-items: flex-start;
}

/* Left Queue Column */
.queue-panel {
  background: var(--color-surface);
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
  min-height: 520px;
}
.search-box {
  position: relative;
  display: flex;
  align-items: center;
}
.search-box svg {
  position: absolute;
  left: var(--space-md);
  width: var(--font-size-md);
  height: var(--font-size-md);
  color: var(--color-gray-400);
}
.search-box input {
  width: 100%;
  padding: var(--Grid-8) var(--space-md) var(--Grid-8) 38px;
  border: 1px solid var(--input-border);
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  outline: none;
  background: var(--color-surface);
}
.search-box input:focus {
  border-color: var(--input-focus);
  box-shadow: 0 0 0 1px var(--input-focus);
}

.queue-list {
  display: flex;
  flex-direction: column;
  gap: var(--Grid-8);
  max-height: 65vh;
  overflow-y: auto;
  padding-right: calc(var(--Grid-8) / 2);
}
.queue-card {
  border: 1px solid var(--color-gray-400);
  border-left: 4px solid var(--color-primary);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  background: var(--color-surface);
  cursor: pointer;
  transition: all .2s ease;
  position: relative;
}
.queue-card:hover {
  transform: translateX(4px);
  border-color: var(--color-primary);
  box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}
.queue-card.active {
  background: var(--tint-primary);
  border-color: var(--color-primary);
  border-left-color: var(--color-primary);
  box-shadow: 0 4px 16px rgba(91,79,224,0.18);
}
.queue-card.is-paid {
  border-left-color: var(--color-success);
}
.queue-card.is-paid.active {
  background: var(--tint-success);
  border-color: var(--color-success);
  border-left-color: var(--color-success);
}

.queue-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--Grid-8);
}
.card-id-tag {
  font-size: var(--font-size-sm);
  font-weight: 700;
  padding: 2px var(--Grid-8);
  border-radius: var(--chip-radius);
  background: var(--tint-background-strong);
  color: var(--color-black-1000);
}
.card-id-tag.paid {
  background: var(--tint-success);
  color: var(--color-success);
}
.card-date {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .7;
}

.card-title {
  font-size: var(--font-size-md);
  font-weight: 700;
  color: var(--color-black-1000);
  margin-bottom: calc(var(--Grid-8) / 2);
  display: flex;
  align-items: center;
  gap: calc(var(--Grid-8) / 2);
}
.card-sub {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .75;
  margin-bottom: calc(var(--Grid-8) / 2);
}
.card-diag {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  background: var(--tint-background-strong);
  padding: calc(var(--Grid-8) / 2) var(--Grid-8);
  border-radius: var(--radius-md);
  margin-bottom: var(--Grid-8);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.card-footer-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: var(--font-size-sm);
}
.doctor-badge {
  font-size: var(--font-size-sm);
  color: var(--color-primary);
  font-weight: 600;
}
.amount-badge {
  font-size: var(--font-size-md);
  font-weight: 700;
  color: var(--color-primary);
}
.amount-badge.paid { color: var(--color-success); }

/* Right Detail & Action Column */
.action-panel {
  position: sticky;
  top: var(--space-lg);
}
.welcome-view {
  text-align: center;
  padding: 80px var(--space-lg);
  background: var(--color-surface);
  border: 1px dashed var(--color-gray-400);
  border-radius: var(--radius-md);
  color: var(--color-black-1000);
  opacity: .7;
}
.welcome-view svg {
  width: 64px; height: 64px;
  color: var(--color-gray-400);
  margin-bottom: var(--space-md);
}

.invoice-box {
  background: var(--color-surface);
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-lg);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.invoice-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid var(--color-gray-400);
  padding-bottom: var(--space-md);
  margin-bottom: var(--space-md);
}
.invoice-head h2 { font-size: var(--font-size-lg); font-weight: 700; }

.patient-banner {
  background: var(--color-surface);
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  margin-bottom: var(--space-md);
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-md);
}
.patient-field small {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .7;
  display: block;
  margin-bottom: 2px;
}
.patient-field strong {
  font-size: var(--font-size-md);
  color: var(--color-black-1000);
}
.patient-field span {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
}

.billing-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: var(--space-md);
  font-size: var(--font-size-sm);
}
.billing-table th {
  text-align: left;
  padding: var(--Grid-8) var(--space-md);
  border-bottom: 1px solid var(--color-gray-400);
  color: var(--color-black-1000);
  opacity: .8;
  font-size: var(--font-size-sm);
}
.billing-table td {
  padding: var(--Grid-8) var(--space-md);
  border-bottom: 1px dashed var(--color-gray-400);
}
.billing-table td.price {
  text-align: right;
  font-weight: 600;
}

.total-summary-box {
  background: var(--tint-primary);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  margin-bottom: var(--space-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.total-summary-box.is-paid { background: var(--tint-success); }
.total-label { font-size: var(--font-size-md); font-weight: 700; }
.total-amount {
  font-size: var(--font-size-2xl);
  font-weight: 800;
  color: var(--color-primary);
}
.total-amount.is-paid { color: var(--color-success); }

.payment-method-selector {
  margin-bottom: var(--space-lg);
}
.payment-method-selector label {
  font-size: var(--font-size-sm);
  font-weight: 700;
  margin-bottom: var(--Grid-8);
  display: block;
}
.method-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--Grid-8);
}
.method-card {
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-md) var(--Grid-8);
  text-align: center;
  cursor: pointer;
  background: var(--color-surface);
  transition: all .15s;
}
.method-card input { display: none; }
.method-card:hover { border-color: var(--color-primary); }
.method-card.active {
  border-color: var(--color-primary);
  background: var(--tint-primary);
  font-weight: 700;
  color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary);
}
.method-card span { display: block; font-size: var(--font-size-lg); margin-bottom: 4px; }
.method-card small { font-size: var(--font-size-sm); }

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: var(--Grid-8);
  border-radius: var(--button-radius);
  border: 1px solid transparent;
  padding: var(--Grid-8) var(--space-md);
  font-size: var(--font-size-sm);
  font-weight: 700;
  cursor: pointer;
  line-height: 1;
  transition: all .15s;
  justify-content: center;
}
.btn-primary { background: var(--button-primary-bg); color: var(--button-primary-text); }
.btn-primary:hover { filter: brightness(0.95); }
.btn-success { background: var(--color-success); color: var(--color-white); }
.btn-success:hover { filter: brightness(0.95); }
.btn-secondary { background: var(--color-surface); color: var(--color-black-1000); border-color: var(--input-border); }
.btn-secondary:hover { background: var(--color-background); }
.btn-block { width: 100%; }

/* Paid Seal Badge */
.paid-stamp {
  display: inline-block;
  padding: 6px var(--space-md);
  border: 2px solid var(--color-success);
  color: var(--color-success);
  font-weight: 800;
  text-transform: uppercase;
  border-radius: var(--radius-md);
  letter-spacing: 1px;
  transform: rotate(-5deg);
  font-size: var(--font-size-sm);
}

/* Modal Pop-up */
.overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.45);
  opacity: 0; pointer-events: none; transition: opacity .2s; z-index: 60;
}
.overlay.open { opacity: 1; pointer-events: auto; }
.modal {
  position: fixed; inset: 0; display: none; align-items: center; justify-content: center;
  z-index: 70; padding: var(--space-md);
}
.modal.open { display: flex; }
.modal-panel {
  background: var(--color-surface); border-radius: var(--radius-md); border: 1px solid var(--color-gray-400);
  width: 620px; max-width: 95vw; max-height: 90vh; display: flex; flex-direction: column;
  box-shadow: 0 16px 40px rgba(0,0,0,0.18);
}
.modal-header {
  padding: var(--space-md); border-bottom: 1px solid var(--color-gray-400);
  display: flex; align-items: center; justify-content: space-between;
}
.modal-header h2 { font-size: var(--font-size-md); font-weight: 700; }
.modal-body { padding: var(--space-md); overflow-y: auto; }
.modal-footer {
  padding: var(--space-md); border-top: 1px solid var(--color-gray-400);
  display: flex; justify-content: space-between; align-items: center;
}
.icon-btn {
  width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;
  border-radius: var(--radius-md); border: 1px solid var(--input-border); background: var(--color-surface);
}

/* Toast */
.toast {
  position: fixed; bottom: var(--space-lg); left: 50%;
  transform: translateX(-50%) translateY(20px); background: var(--color-surface);
  border: 1px solid var(--color-gray-400); border-left: 6px solid var(--color-success);
  border-radius: var(--radius-md); padding: var(--Grid-8) var(--space-md);
  display: flex; align-items: center; gap: var(--Grid-8); z-index: 80;
  opacity: 0; pointer-events: none; transition: all .2s ease; font-weight: 600;
  font-size: var(--font-size-sm);
  box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}
.toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* Responsive */
@media (max-width: 992px) {
  .master-detail-grid { grid-template-columns: 1fr; }
  .action-panel { position: static; }
}
@media (max-width: 768px) {
  .sidebar { display: none; }
  .topbar { display: flex; }
  main { padding: var(--space-md); }
  .patient-banner { grid-template-columns: 1fr; }
  .method-grid { grid-template-columns: 1fr; }
}
.sidebar.mobile-open {
  display: flex; position: fixed; inset: 0 auto 0 0; z-index: 90;
  box-shadow: 4px 0 20px rgba(0,0,0,0.15);
}

/* Print Styles */
@media print {
  body { background: var(--color-surface) !important; }
  .sidebar, .topbar, .page-header, .segmented-tabs, .queue-column, #paidActionSection, #paymentFormSection, .icon-btn, .toast, .overlay, .modal {
    display: none !important;
  }
  .app, .main-wrap, main {
    display: block !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  .master-detail-grid { display: block !important; }
  .action-panel { position: static !important; }
  .invoice-box {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
  }
  .patient-banner {
    background: var(--color-surface) !important;
    border: 1px solid var(--color-gray-400) !important;
  }
}
</style>
</head>
<body>

<div class="app">

  <!-- =====================================================================
       LEFT SIDEBAR NAVIGATION
       ===================================================================== -->
  <aside class="sidebar" id="sidebar">
    <div class="brand">PetCare<span>Portal</span></div>

    <nav aria-label="เมนูหลัก">
      <div class="nav-group">
        <div class="nav-group-label">ภาพรวม</div>
        <ul>
          <li><a class="nav-link" href="../index.php"><span class="nav-icon">📊</span><span>Dashboard</span></a></li>
        </ul>
      </div>

      <div class="nav-group">
        <div class="nav-group-label">งานหน้าเคาน์เตอร์</div>
        <ul>
          <li><a class="nav-link" href="register.php"><span class="nav-icon">🐕</span><span>ลงทะเบียนเจ้าของ/สัตว์</span></a></li>
          <li><a class="nav-link" href="pet_list.php"><span class="nav-icon">🐾</span><span>จัดการสัตว์เลี้ยง</span></a></li>
          <li><a class="nav-link" href="appointment_form.php"><span class="nav-icon">📅</span><span>สร้างนัดหมาย</span></a></li>
          <li><a class="nav-link" href="treatment_form.php"><span class="nav-icon">🩺</span><span>ห้องตรวจแพทย์</span></a></li>
          <li><a class="nav-link" href="billing_list.php" aria-current="page"><span class="nav-icon">💵</span><span>การเงิน/ใบเสร็จ</span></a></li>
        </ul>
      </div>

      <div class="nav-group">
        <div class="nav-group-label">ประวัติและรายการ</div>
        <ul>
          <li><a class="nav-link" href="appointment_list.php"><span class="nav-icon">📋</span><span>รายการนัดหมาย</span></a></li>
          <li><a class="nav-link" href="treatment_history.php"><span class="nav-icon">📁</span><span>ประวัติการรักษา</span></a></li>
          <li><a class="nav-link" href="owner_list.php"><span class="nav-icon">👤</span><span>ข้อมูลเจ้าของ</span></a></li>
          <li><a class="nav-link" href="doctor_list.php"><span class="nav-icon">🧑‍⚕️</span><span>ข้อมูลสัตวแพทย์</span></a></li>
        </ul>
      </div>
    </nav>
  </aside>

  <div class="main-wrap">

    <!-- Mobile Top Bar -->
    <header class="topbar">
      <button class="hamburger-btn" id="hamburgerBtn" aria-label="เปิดเมนู">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="topbar-brand">การเงิน/ใบเสร็จ</div>
      <div style="width:40px;"></div>
    </header>

    <main>
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1>
            <span class="page-header-icon">💵</span>
            การเงินและใบเสร็จ (Billing & Receipts)
          </h1>
          <p>จัดการออกใบแจ้งหนี้ รับชำระเงิน และดูประวัติบิลย้อนหลังทั้งหมดในระบบ</p>
        </div>
        
        <!-- Segmented Tabs Switcher -->
        <div class="segmented-tabs" role="tablist">
          <button type="button" class="tab-btn active" id="tabPending" data-tab="pending">
            📋 คิวรอชำระเงิน
            <span class="tab-badge" id="pendingCountBadge"><?= count($pending_json) ?></span>
          </button>
          <button type="button" class="tab-btn" id="tabHistory" data-tab="history">
            📂 ประวัติบิลที่ชำระแล้ว
            <span class="tab-badge" style="background:var(--color-gray-400); color:var(--color-black-1000);" id="historyCountBadge"><?= count($history_json) ?></span>
          </button>
        </div>
      </div>

      <!-- Master-Detail 2 Columns Layout -->
      <div class="master-detail-grid">

        <!-- Left Column: Master Queue -->
        <div class="queue-panel">
          <div class="d-flex justify-content-between align-items-center">
            <strong id="queueSectionTitle" style="font-size:var(--font-size-md);">คิวรอชำระเงิน (Pending)</strong>
            <small class="text-muted" id="queueResultSummary">พบ <?= count($pending_json) ?> เคส</small>
          </div>

          <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <input type="text" id="billingSearch" placeholder="ค้นหาชื่อสัตว์เลี้ยง, เจ้าของ หรือเลขที่เคส/บิล...">
          </div>

          <div class="queue-list" id="queueListContainer">
            <!-- Injected by JavaScript -->
          </div>
        </div>

        <!-- Right Column: Detail / Action Form -->
        <div class="action-panel">

          <!-- Welcome View (Default State) -->
          <div class="welcome-view" id="welcomeView">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <h3 style="font-weight:700; color:var(--color-black-1000); opacity:.85; margin-bottom:calc(var(--Grid-8) / 2);">กรุณาเลือกเคสจากรายการทางซ้าย</h3>
            <p style="font-size:var(--font-size-sm); margin:0;">เพื่อตรวจสอบรายการค่าใช้จ่าย ออกใบเสร็จ หรือดูประวัติการชำระเงินย้อนหลัง</p>
          </div>

          <!-- Active Payment / Invoice Box -->
          <div class="invoice-box" id="invoiceBox" style="display:none;">
            <div class="invoice-head">
              <div>
                <h2 id="invoiceTitle">ใบแจ้งชำระเงิน</h2>
                <small class="text-muted" id="invoiceSubtitle">นัดหมาย ID #0</small>
              </div>
              <div id="invoiceStatusBadge">
                <!-- Paid Stamp or Pending Badge -->
              </div>
            </div>

            <!-- Patient & Owner Info -->
            <div class="patient-banner">
              <div class="patient-field">
                <small>คนไข้ (สัตว์เลี้ยง):</small>
                <strong id="infoPetName">-</strong>
              </div>
              <div class="patient-field">
                <small>เจ้าของสัตว์เลี้ยง:</small>
                <strong id="infoOwnerName">-</strong>
              </div>
              <div class="patient-field">
                <small>เบอร์โทรศัพท์ติดต่อ:</small>
                <span id="infoOwnerPhone">-</span>
              </div>
              <div class="patient-field">
                <small>สัตวแพทย์ผู้ตรวจ:</small>
                <span id="infoDoctorName">-</span>
              </div>
              <div class="patient-field" style="grid-column: 1 / -1;">
                <small>ผลวินิจฉัย/อาการ:</small>
                <span id="infoDiagnosis">-</span>
              </div>
            </div>

            <!-- Expense Breakdown Table -->
            <h4 style="font-size:var(--font-size-sm); font-weight:700; margin-bottom:var(--Grid-8);">รายการค่าใช้จ่าย</h4>
            <table class="billing-table">
              <thead>
                <tr>
                  <th>รายการ</th>
                  <th style="text-align:right; width:120px;">ราคา (บาท)</th>
                </tr>
              </thead>
              <tbody id="billingTableBody">
                <!-- Injected -->
              </tbody>
            </table>

            <!-- Net Total Box -->
            <div class="total-summary-box" id="totalBox">
              <div>
                <div class="total-label">ยอดรวมสุทธิ (Grand Total)</div>
                <small id="totalNote" style="font-size:var(--font-size-sm); opacity:0.8;">รวมค่ายาและค่าบริการตรวจรักษาแล้ว</small>
              </div>
              <div class="total-amount" id="totalAmountDisplay">฿0.00</div>
            </div>

            <!-- Payment Form for Pending Bills -->
            <div id="paymentFormSection">
              <form id="paymentForm" action="../api/save_receipt.php" method="POST">
                <input type="hidden" name="appointment_ID" id="formAppId">
                <input type="hidden" name="total_medication_price" id="formMedTotal">
                <input type="hidden" name="service_fee" id="formServiceFee">
                <input type="hidden" name="grand_total" id="formGrandTotal">
                <input type="hidden" name="payment_method" id="formPaymentMethod" value="Cash">

                <div class="payment-method-selector">
                  <label>เลือกช่องทางการชำระเงิน <span style="color:var(--color-danger);">*</span></label>
                  <div class="method-grid">
                    <div class="method-card active" data-method="Cash">
                      <span>💵</span>
                      <small>เงินสด (Cash)</small>
                    </div>
                    <div class="method-card" data-method="Transfer">
                      <span>📱</span>
                      <small>เงินโอน (Transfer)</small>
                    </div>
                    <div class="method-card" data-method="Credit Card">
                      <span>💳</span>
                      <small>บัตรเครดิต</small>
                    </div>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="btnSubmitPayment" style="font-size:16px; padding:14px;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M20 6L9 17l-5-5"/></svg>
                  ยืนยันการรับชำระเงินและปิดเคส
                </button>
              </form>
            </div>

            <!-- Receipt Actions for Paid History -->
            <div id="paidActionSection" style="display:none; gap:10px;">
              <button type="button" class="btn btn-secondary" style="flex:1;" id="btnPrintReceipt">
                🖨️ พิมพ์ใบเสร็จ
              </button>
              <button type="button" class="btn btn-primary" style="flex:1;" id="btnOpenReceiptModal">
                🔍 ดูใบเสร็จแบบ Pop-up
              </button>
            </div>
          </div>

        </div>

      </div>
    </main>
  </div>
</div>

<!-- ===========================================================================
     RECEIPT POP-UP MODAL (กึ่งกลางจอ)
     =========================================================================== -->
<div class="overlay" id="overlay"></div>
<div class="modal" id="receiptModal" role="dialog" aria-modal="true" aria-labelledby="receiptModalTitle">
  <div class="modal-panel">
    <div class="modal-header">
      <h2 id="receiptModalTitle">🧾 ใบเสร็จรับเงิน</h2>
      <button type="button" class="icon-btn" id="receiptCloseBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <div class="modal-body" id="receiptModalBody">
      <!-- Printable receipt preview -->
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="modalPrintBtn">🖨️ สั่งพิมพ์</button>
      <button type="button" class="btn btn-primary" id="modalDoneBtn">ปิด</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18"><path d="M20 6L9 17l-5-5"/></svg>
  <span id="toastMsg">ชำระเงินเรียบร้อยแล้ว</span>
</div>

<script>
(function() {
  "use strict";

  const PENDING_LIST = <?= json_encode($pending_json, JSON_UNESCAPED_UNICODE) ?>;
  const HISTORY_LIST = <?= json_encode($history_json, JSON_UNESCAPED_UNICODE) ?>;

  let currentTab = 'pending'; // 'pending' | 'history'
  let selectedPendingId = null;
  let selectedHistoryId = null;
  let activePaymentMethod = 'Cash';

  const els = {
    tabPending: document.getElementById('tabPending'),
    tabHistory: document.getElementById('tabHistory'),
    queueSectionTitle: document.getElementById('queueSectionTitle'),
    queueResultSummary: document.getElementById('queueResultSummary'),
    queueContainer: document.getElementById('queueListContainer'),
    billingSearch: document.getElementById('billingSearch'),
    welcomeView: document.getElementById('welcomeView'),
    invoiceBox: document.getElementById('invoiceBox'),
    invoiceTitle: document.getElementById('invoiceTitle'),
    invoiceSubtitle: document.getElementById('invoiceSubtitle'),
    invoiceStatusBadge: document.getElementById('invoiceStatusBadge'),
    infoPetName: document.getElementById('infoPetName'),
    infoOwnerName: document.getElementById('infoOwnerName'),
    infoOwnerPhone: document.getElementById('infoOwnerPhone'),
    infoDoctorName: document.getElementById('infoDoctorName'),
    infoDiagnosis: document.getElementById('infoDiagnosis'),
    billingTableBody: document.getElementById('billingTableBody'),
    totalBox: document.getElementById('totalBox'),
    totalNote: document.getElementById('totalNote'),
    totalAmountDisplay: document.getElementById('totalAmountDisplay'),
    paymentFormSection: document.getElementById('paymentFormSection'),
    paidActionSection: document.getElementById('paidActionSection'),
    paymentForm: document.getElementById('paymentForm'),
    formAppId: document.getElementById('formAppId'),
    formMedTotal: document.getElementById('formMedTotal'),
    formServiceFee: document.getElementById('formServiceFee'),
    formGrandTotal: document.getElementById('formGrandTotal'),
    formPaymentMethod: document.getElementById('formPaymentMethod'),
    btnPrintReceipt: document.getElementById('btnPrintReceipt'),
    btnOpenReceiptModal: document.getElementById('btnOpenReceiptModal'),
    receiptModal: document.getElementById('receiptModal'),
    receiptModalBody: document.getElementById('receiptModalBody'),
    receiptCloseBtn: document.getElementById('receiptCloseBtn'),
    modalPrintBtn: document.getElementById('modalPrintBtn'),
    modalDoneBtn: document.getElementById('modalDoneBtn'),
    overlay: document.getElementById('overlay'),
    toast: document.getElementById('toast'),
    toastMsg: document.getElementById('toastMsg'),
    sidebar: document.getElementById('sidebar'),
    hamburgerBtn: document.getElementById('hamburgerBtn'),
    pendingCountBadge: document.getElementById('pendingCountBadge'),
    historyCountBadge: document.getElementById('historyCountBadge')
  };

  /* =========================================================================
     RENDER: QUEUE LIST (Master Column)
     ========================================================================= */
  function getFilteredList() {
    const q = els.billingSearch.value.trim().toLowerCase();
    if (currentTab === 'pending') {
      return PENDING_LIST.filter(item => {
        if (!q) return true;
        return item.pet_name.toLowerCase().includes(q) ||
               item.owner_name.toLowerCase().includes(q) ||
               String(item.app_id).includes(q) ||
               item.doctor_name.toLowerCase().includes(q) ||
               item.diagnosis.toLowerCase().includes(q);
      });
    } else {
      return HISTORY_LIST.filter(item => {
        if (!q) return true;
        return item.pet_name.toLowerCase().includes(q) ||
               item.owner_name.toLowerCase().includes(q) ||
               item.receipt_code.toLowerCase().includes(q) ||
               String(item.receipt_id).includes(q) ||
               String(item.app_id).includes(q) ||
               item.payment_method.toLowerCase().includes(q) ||
               item.payment_date.includes(q);
      });
    }
  }

  function renderQueue() {
    const list = getFilteredList();
    els.queueResultSummary.textContent = `พบ ${list.length} รายการ`;

    if (list.length === 0) {
      els.queueContainer.innerHTML = `
        <div style="text-align:center; padding:var(--space-24) var(--space-16); color:var(--color-black-1000); opacity:0.6;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="40" height="40" style="margin-bottom:var(--Grid-8);"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
          <p style="margin:0; font-size:var(--font-size-sm);">ไม่พบรายการที่ตรงกับคำค้นหา</p>
        </div>
      `;
      return;
    }

    if (currentTab === 'pending') {
      els.queueContainer.innerHTML = list.map(item => {
        const isSelected = selectedPendingId === item.app_id;
        return `
        <div class="queue-card ${isSelected ? 'active' : ''}" data-id="${item.app_id}" onclick="window.__selectPending(${item.app_id})">
          <div class="queue-card-header">
            <span class="card-id-tag">ID #${item.app_id}</span>
            <span class="card-date">📅 ${formatDate(item.date)}</span>
          </div>
          <div class="card-title">🐾 ${escapeHtml(item.pet_name)}</div>
          <div class="card-sub">👤 เจ้าของ: ${escapeHtml(item.owner_name)}</div>
          <div class="card-diag"><strong>วินิจฉัย:</strong> ${escapeHtml(item.diagnosis)}</div>
          <div class="card-footer-info">
            <span class="doctor-badge">👨‍⚕️ ${escapeHtml(item.doctor_name)}</span>
            <span class="amount-badge">฿${formatNum(item.grand_total)}</span>
          </div>
        </div>
        `;
      }).join('');
    } else {
      els.queueContainer.innerHTML = list.map(item => {
        const isSelected = selectedHistoryId === item.receipt_id;
        return `
        <div class="queue-card is-paid ${isSelected ? 'active' : ''}" data-id="${item.receipt_id}" onclick="window.__selectHistory(${item.receipt_id})">
          <div class="queue-card-header">
            <span class="card-id-tag paid">${escapeHtml(item.receipt_code)}</span>
            <span class="card-date">${formatDateTime(item.payment_date)}</span>
          </div>
          <div class="card-title">🐾 ${escapeHtml(item.pet_name)}</div>
          <div class="card-sub">👤 เจ้าของ: ${escapeHtml(item.owner_name)}</div>
          <div class="card-footer-info">
            <span class="card-id-tag">${formatMethod(item.payment_method)}</span>
            <span class="amount-badge paid">฿${formatNum(item.grand_total)}</span>
          </div>
        </div>
        `;
      }).join('');
    }
  }

  /* =========================================================================
     SELECT & RENDER DETAIL (Right Column)
     ========================================================================= */
  window.__selectPending = function(appId) {
    selectedPendingId = appId;
    selectedHistoryId = null;
    renderQueue();

    const item = PENDING_LIST.find(p => p.app_id === appId);
    if (!item) return;

    els.welcomeView.style.display = 'none';
    els.invoiceBox.style.display = 'block';

    els.invoiceTitle.textContent = 'ใบแจ้งชำระเงิน';
    els.invoiceSubtitle.textContent = `นัดหมาย ID #${item.app_id} (รอชำระเงิน)`;
    els.invoiceStatusBadge.innerHTML = `<span class="paid-stamp" style="border-color:var(--color-danger); color:var(--color-black-1000);">PENDING</span>`;

    els.infoPetName.textContent = `🐾 ${item.pet_name}`;
    els.infoOwnerName.textContent = item.owner_name;
    els.infoOwnerPhone.textContent = item.owner_phone;
    els.infoDoctorName.textContent = item.doctor_name;
    els.infoDiagnosis.textContent = item.diagnosis;

    // Itemized table
    let rowsHtml = '';
    if (item.meds && item.meds.length > 0) {
      rowsHtml += item.meds.map(m => `
        <tr>
          <td>💊 ${escapeHtml(m.name)}</td>
          <td class="price">฿${formatNum(m.price)}</td>
        </tr>
      `).join('');
    } else {
      rowsHtml += `<tr><td style="color:var(--color-black-1000); opacity:0.6;">(ไม่มีรายการสั่งยา)</td><td class="price">฿0.00</td></tr>`;
    }
    rowsHtml += `
      <tr style="background:var(--tint-primary);">
        <td>🩺 ค่าบริการตรวจรักษา</td>
        <td class="price">฿${formatNum(item.service_fee)}</td>
      </tr>
    `;
    els.billingTableBody.innerHTML = rowsHtml;

    // Totals
    els.totalBox.className = 'total-summary-box';
    els.totalAmountDisplay.className = 'total-amount';
    els.totalAmountDisplay.textContent = `฿${formatNum(item.grand_total)}`;
    els.totalNote.textContent = 'รวมค่ายาและค่าบริการตรวจรักษาแล้ว';

    // Form inputs
    els.formAppId.value = item.app_id;
    els.formMedTotal.value = item.total_med;
    els.formServiceFee.value = item.service_fee;
    els.formGrandTotal.value = item.grand_total;
    els.formPaymentMethod.value = activePaymentMethod;

    els.paymentFormSection.style.display = 'block';
    els.paidActionSection.style.display = 'none';

    // Auto scroll on mobile
    if (window.innerWidth <= 992) {
      els.invoiceBox.scrollIntoView({ behavior: 'smooth' });
    }
  };

  window.__selectHistory = function(receiptId) {
    selectedHistoryId = receiptId;
    selectedPendingId = null;
    renderQueue();

    const item = HISTORY_LIST.find(h => h.receipt_id === receiptId);
    if (!item) return;

    els.welcomeView.style.display = 'none';
    els.invoiceBox.style.display = 'block';

    els.invoiceTitle.textContent = 'ใบเสร็จรับเงิน (Official Receipt)';
    els.invoiceSubtitle.textContent = `เลขที่: ${item.receipt_code} · วันที่ชำระ: ${formatDateTime(item.payment_date)}`;
    els.invoiceStatusBadge.innerHTML = `<span class="paid-stamp">PAID / ชำระแล้ว</span>`;

    els.infoPetName.textContent = `🐾 ${item.pet_name}`;
    els.infoOwnerName.textContent = item.owner_name;
    els.infoOwnerPhone.textContent = item.owner_phone;
    els.infoDoctorName.textContent = item.doctor_name;
    els.infoDiagnosis.textContent = item.diagnosis;

    // Items table
    let rowsHtml = '';
    if (item.items && item.items.length > 0) {
      rowsHtml += item.items.map(m => `
        <tr>
          <td>💊 ${escapeHtml(m.name)}</td>
          <td class="price">฿${formatNum(m.price)}</td>
        </tr>
      `).join('');
    } else {
      rowsHtml += `<tr><td style="color:var(--color-black-1000); opacity:0.6;">(ค่ายาและเวชภัณฑ์)</td><td class="price">฿${formatNum(item.total_med)}</td></tr>`;
    }
    rowsHtml += `
      <tr style="background:var(--tint-primary);">
        <td>🩺 ค่าบริการตรวจรักษา</td>
        <td class="price">฿${formatNum(item.service_fee)}</td>
      </tr>
    `;
    els.billingTableBody.innerHTML = rowsHtml;

    // Totals
    els.totalBox.className = 'total-summary-box is-paid';
    els.totalAmountDisplay.className = 'total-amount is-paid';
    els.totalAmountDisplay.textContent = `฿${formatNum(item.grand_total)}`;
    els.totalNote.textContent = `ชำระเงินโดย: ${formatMethod(item.payment_method)} เมื่อ ${formatDateTime(item.payment_date)}`;

    els.paymentFormSection.style.display = 'none';
    els.paidActionSection.style.display = 'flex';

    if (window.innerWidth <= 992) {
      els.invoiceBox.scrollIntoView({ behavior: 'smooth' });
    }
  };

  /* =========================================================================
     TAB SWITCHING
     ========================================================================= */
  els.tabPending.addEventListener('click', () => {
    currentTab = 'pending';
    els.tabPending.classList.add('active');
    els.tabHistory.classList.remove('active');
    els.queueSectionTitle.textContent = 'คิวรอชำระเงิน (Pending)';
    els.billingSearch.placeholder = 'ค้นหาชื่อสัตว์เลี้ยง, เจ้าของ หรือเลขที่เคส...';
    renderQueue();
    if (selectedPendingId) {
      window.__selectPending(selectedPendingId);
    } else {
      els.welcomeView.style.display = 'block';
      els.invoiceBox.style.display = 'none';
    }
  });

  els.tabHistory.addEventListener('click', () => {
    currentTab = 'history';
    els.tabHistory.classList.add('active');
    els.tabPending.classList.remove('active');
    els.queueSectionTitle.textContent = 'ประวัติบิลที่ชำระแล้ว (Paid History)';
    els.billingSearch.placeholder = 'ค้นหาชื่อสัตว์, เจ้าของ, เลขที่ใบเสร็จ...';
    renderQueue();
    if (selectedHistoryId) {
      window.__selectHistory(selectedHistoryId);
    } else {
      els.welcomeView.style.display = 'block';
      els.invoiceBox.style.display = 'none';
    }
  });

  // Search input handler
  els.billingSearch.addEventListener('input', () => {
    renderQueue();
  });

  // Payment method cards selection
  document.querySelectorAll('.method-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.method-card').forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      activePaymentMethod = card.dataset.method;
      els.formPaymentMethod.value = activePaymentMethod;
    });
  });

  /* =========================================================================
     PAYMENT SUBMISSION (AJAX & Form)
     ========================================================================= */
  els.paymentForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitPayment');
    btn.disabled = true;
    btn.textContent = 'กำลังบันทึกการชำระเงิน...';

    const formData = new FormData(els.paymentForm);

    fetch('../api/save_receipt.php', {
      method: 'POST',
      body: formData
    })
    .then(() => {
      showToast('บันทึกการชำระเงินและปิดเคสเรียบร้อยแล้ว');
      setTimeout(() => {
        location.href = 'billing_list.php?status=success';
      }, 700);
    })
    .catch(err => {
      alert('เกิดข้อผิดพลาดในการบันทึก: ' + err.message);
      btn.disabled = false;
      btn.textContent = 'ยืนยันการรับชำระเงินและปิดเคส';
    });
  });

  /* =========================================================================
     RECEIPT POP-UP MODAL & PRINT
     ========================================================================= */
  function openReceiptModal(item) {
    els.receiptModalTitle.textContent = `ใบเสร็จรับเงิน: ${item.receipt_code}`;
    els.receiptModalBody.innerHTML = `
      <div style="border:1px solid var(--color-gray-400); border-radius:var(--radius-md); padding:var(--space-md); font-size:var(--font-size-sm); background:var(--color-surface);">
        <div style="text-align:center; margin-bottom:var(--space-md); border-bottom:1px solid var(--color-gray-400); padding-bottom:var(--Grid-8);">
          <h3 style="font-weight:700; color:var(--color-primary); margin:0;">PetCare Portal Hospital</h3>
          <p style="margin:2px 0 0; color:var(--color-black-1000); opacity:0.7; font-size:12px;">ใบเสร็จรับเงิน / ใบส่งสินค้า (Receipt / Tax Invoice)</p>
          <strong style="display:inline-block; margin-top:6px; font-size:15px;">${item.receipt_code}</strong>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--Grid-8); margin-bottom:var(--space-md); font-size:13px;">
          <div><strong>สัตว์เลี้ยง:</strong> ${escapeHtml(item.pet_name)}</div>
          <div><strong>วันที่ชำระ:</strong> ${formatDateTime(item.payment_date)}</div>
          <div><strong>เจ้าของ:</strong> ${escapeHtml(item.owner_name)}</div>
          <div><strong>ช่องทาง:</strong> ${formatMethod(item.payment_method)}</div>
          <div><strong>แพทย์ผู้ตรวจ:</strong> ${escapeHtml(item.doctor_name)}</div>
          <div><strong>นัดหมาย ID:</strong> #${item.app_id}</div>
        </div>

        <table style="width:100%; border-collapse:collapse; margin-bottom:var(--space-md); font-size:13px;">
          <thead>
            <tr style="border-bottom:1px solid var(--color-gray-400); background:var(--tint-primary);">
              <th style="padding:6px; text-align:left;">รายการ</th>
              <th style="padding:6px; text-align:right;">ราคา</th>
            </tr>
          </thead>
          <tbody>
            ${item.items && item.items.length > 0 ? item.items.map(it => `
              <tr>
                <td style="padding:6px; border-bottom:1px dashed var(--color-gray-400);">${escapeHtml(it.name)}</td>
                <td style="padding:6px; text-align:right; border-bottom:1px dashed var(--color-gray-400);">฿${formatNum(it.price)}</td>
              </tr>
            `).join('') : `<tr><td style="padding:6px;">ค่ายาและเวชภัณฑ์</td><td style="padding:6px; text-align:right;">฿${formatNum(item.total_med)}</td></tr>`}
            <tr>
              <td style="padding:6px; border-bottom:1px solid var(--color-gray-400);">ค่าบริการตรวจรักษา</td>
              <td style="padding:6px; text-align:right; border-bottom:1px solid var(--color-gray-400);">฿${formatNum(item.service_fee)}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th style="padding:8px 6px; text-align:left; font-size:15px;">ยอดรวมสุทธิ:</th>
              <th style="padding:8px 6px; text-align:right; font-size:16px; color:var(--color-success);">฿${formatNum(item.grand_total)}</th>
            </tr>
          </tfoot>
        </table>

        <div style="text-align:center; margin-top:20px;">
          <span class="paid-stamp">PAID / ชำระแล้ว</span>
          <p style="margin:10px 0 0; color:var(--color-black-1000); opacity:0.6; font-size:11px;">ขอบคุณที่ไว้วางใจให้เราดูแลสัตว์เลี้ยงของคุณ</p>
        </div>
      </div>
    `;

    els.receiptModal.classList.add('open');
    els.overlay.classList.add('open');
  }

  els.btnOpenReceiptModal.addEventListener('click', () => {
    const item = HISTORY_LIST.find(h => h.receipt_id === selectedHistoryId);
    if (item) openReceiptModal(item);
  });

  els.btnPrintReceipt.addEventListener('click', () => {
    window.print();
  });
  els.modalPrintBtn.addEventListener('click', () => {
    window.print();
  });

  function closeReceiptModal() {
    els.receiptModal.classList.remove('open');
    els.overlay.classList.remove('open');
  }
  els.receiptCloseBtn.addEventListener('click', closeReceiptModal);
  els.modalDoneBtn.addEventListener('click', closeReceiptModal);
  els.overlay.addEventListener('click', closeReceiptModal);

  /* =========================================================================
     HELPERS
     ========================================================================= */
  function formatDate(dStr) {
    if (!dStr) return '-';
    const parts = dStr.split('-');
    if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
    return dStr;
  }
  function formatDateTime(dtStr) {
    if (!dtStr) return '-';
    return dtStr.replace('T', ' ').substring(0, 16);
  }
  function formatNum(n) {
    return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function formatMethod(m) {
    if (m === 'Cash') return '💵 เงินสด';
    if (m === 'Transfer') return '📱 เงินโอน';
    if (m === 'Credit Card') return '💳 บัตรเครดิต';
    return m;
  }
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
  function showToast(msg) {
    els.toastMsg.textContent = msg;
    els.toast.classList.add('show');
    setTimeout(() => els.toast.classList.remove('show'), 3000);
  }

  // Mobile menu toggle
  els.hamburgerBtn.addEventListener('click', () => {
    els.sidebar.classList.toggle('mobile-open');
  });

  // Init
  renderQueue();

  // If there are pending bills, auto select the first one!
  if (PENDING_LIST.length > 0) {
    window.__selectPending(PENDING_LIST[0].app_id);
  }

})();
</script>
</body>
</html>