<?php 
require_once '../config/db.php';

// 1. ดึงประวัติการรักษาทั้งหมดพร้อมข้อมูลสัตว์เลี้ยง เจ้าของ และแพทย์
$sql = "SELECT a.appointments_ID, a.Date, p.pet_ID, p.name AS pet_name,
               o.owner_ID, o.name AS owner_name, o.phoneNumber AS owner_phone,
               d.doctor_ID, d.name AS doctor_name,
               dg.diagnoses_ID, dg.diagnosisDescription,
               r.receipt_ID, r.grand_total, r.payment_status
        FROM appointments a
        JOIN pet p ON a.pet_ID = p.pet_ID
        JOIN owner o ON p.owner_ID = o.owner_ID
        JOIN doctor d ON a.doctor_ID = d.doctor_ID
        JOIN diagnoses dg ON a.appointments_ID = dg.appointment_ID
        LEFT JOIN receipts r ON a.appointments_ID = r.appointment_ID
        ORDER BY a.Date DESC, a.appointments_ID DESC";
$history_rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 2. ดึงรายการยาทั้งหมดของแต่ละนัดหมาย
$sql_meds = "SELECT t.appointment_ID, m.medicine_ID, m.name AS med_name, m.price 
             FROM treatment t 
             JOIN medication m ON t.medicine_ID = m.medicine_ID";
$meds_stmt = $pdo->query($sql_meds);
$all_meds = $meds_stmt->fetchAll(PDO::FETCH_ASSOC);

$meds_map = [];
foreach ($all_meds as $m) {
    $aId = (int)$m['appointment_ID'];
    if (!isset($meds_map[$aId])) {
        $meds_map[$aId] = [];
    }
    $meds_map[$aId][] = [
        'id' => (int)$m['medicine_ID'],
        'name' => $m['med_name'],
        'price' => (float)$m['price']
    ];
}

// 3. เตรียม JSON สำหรับ Client-side Filtering & Instant Detail Rendering
$history_json = [];
foreach ($history_rows as $row) {
    $aId = (int)$row['appointments_ID'];
    $medList = $meds_map[$aId] ?? [];
    $totalMed = 0;
    foreach ($medList as $med) {
        $totalMed += $med['price'];
    }
    $history_json[] = [
        'app_id' => $aId,
        'date' => $row['Date'],
        'pet_id' => $row['pet_ID'],
        'pet_name' => $row['pet_name'] ?? 'ไม่ระบุ',
        'owner_id' => $row['owner_ID'],
        'owner_name' => $row['owner_name'] ?? 'ไม่ระบุ',
        'owner_phone' => $row['owner_phone'] ?? '-',
        'doctor_id' => $row['doctor_ID'],
        'doctor_name' => $row['doctor_name'] ?? 'สัตวแพทย์',
        'diagnosis_id' => $row['diagnoses_ID'],
        'diagnosis' => $row['diagnosisDescription'] ?? 'ไม่มีบันทึกอาการ',
        'meds' => $medList,
        'total_med' => $totalMed,
        'receipt_id' => $row['receipt_ID'] ? (int)$row['receipt_ID'] : null,
        'payment_status' => $row['payment_status'] ?? 'Unpaid',
        'grand_total' => $row['grand_total'] ? (float)$row['grand_total'] : null
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ประวัติการรักษา (Treatment History) | PetCare Portal</title>
<link rel="stylesheet" href="../css/petcare-token.css">
<style>
/* Reset & Base */
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body {
  font-family: var(--font-family-base);
  background-color: var(--color-background);
  color: var(--color-black-1000);
  font-size: var(--font-size-md);
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}
h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-family-base);
  margin: 0;
}
a { color: inherit; text-decoration: none; }
button, input, select, textarea { font-family: inherit; }
ul { list-style: none; margin: 0; padding: 0; }

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
  cursor: pointer;
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
  background: var(--tint-primary);
  color: var(--color-primary);
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

/* Master-Detail 2 Columns */
.master-detail-grid {
  display: grid;
  grid-template-columns: 5fr 7fr;
  gap: var(--space-lg);
  align-items: flex-start;
}

/* Left Master Column */
.queue-column {
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
}

.toolbar-card {
  background: var(--color-surface);
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.search-box {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: var(--space-md);
}
.search-box svg {
  position: absolute;
  left: var(--space-md);
  width: var(--font-size-md);
  height: var(--font-size-md);
  color: var(--color-gray-400);
  pointer-events: none;
}
.search-box input {
  width: 100%;
  padding: var(--Grid-8) var(--space-md) var(--Grid-8) 42px;
  border-radius: var(--radius-md);
  border: 1px solid var(--input-border);
  font-size: var(--font-size-sm);
  outline: none;
  background: var(--color-surface);
}
.search-box input:focus {
  border-color: var(--input-focus);
  box-shadow: 0 0 0 1px var(--input-focus);
}

.date-filter-row {
  display: flex;
  align-items: center;
  gap: var(--Grid-8);
  flex-wrap: wrap;
}
.date-input-wrap {
  position: relative;
  flex: 1;
  min-width: 150px;
}
.date-input-wrap input[type="date"] {
  width: 100%;
  padding: var(--Grid-8) var(--space-md);
  border-radius: var(--radius-md);
  border: 1px solid var(--input-border);
  font-size: var(--font-size-sm);
  outline: none;
  background: var(--color-surface);
}
.date-input-wrap input[type="date"]:focus {
  border-color: var(--input-focus);
}
.btn-clear-date {
  padding: var(--Grid-8) var(--space-md);
  border: 1px solid var(--color-gray-400);
  background: var(--color-surface);
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  cursor: pointer;
  white-space: nowrap;
  transition: all .15s;
}
.btn-clear-date:hover { background: var(--color-background); }

.quick-chips {
  display: flex;
  gap: calc(var(--Grid-8) / 2);
  margin-top: var(--Grid-8);
  flex-wrap: wrap;
}
.chip {
  padding: calc(var(--Grid-8) / 2) var(--space-md);
  border-radius: var(--chip-radius);
  border: 1px solid var(--color-gray-400);
  background: var(--color-surface);
  font-size: var(--font-size-sm);
  font-weight: 600;
  cursor: pointer;
  transition: all .15s;
}
.chip:hover { border-color: var(--color-primary); }
.chip.active {
  background: var(--color-primary);
  color: var(--color-white);
  border-color: var(--color-primary);
}

.queue-header-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .7;
  padding: 0 calc(var(--Grid-8) / 2);
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: var(--Grid-8);
  max-height: 65vh;
  overflow-y: auto;
  padding-right: calc(var(--Grid-8) / 2);
}
.history-card {
  border: 1px solid var(--color-gray-400);
  border-left: 4px solid var(--color-primary);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  background: var(--color-surface);
  cursor: pointer;
  transition: all .2s ease;
  position: relative;
}
.history-card:hover {
  transform: translateX(4px);
  border-color: var(--color-primary);
  box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}
.history-card.active {
  background: var(--tint-primary);
  border-color: var(--color-primary);
  border-left-color: var(--color-primary);
  box-shadow: 0 4px 16px rgba(91,79,224,0.18);
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: calc(var(--Grid-8) / 2);
}
.card-id-badge {
  font-size: var(--font-size-sm);
  font-weight: 700;
  padding: 2px var(--Grid-8);
  border-radius: var(--chip-radius);
  background: var(--tint-background-strong);
  color: var(--color-black-1000);
}
.card-date {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .7;
  font-weight: 600;
}

.card-pet-title {
  font-size: var(--font-size-md);
  font-weight: 700;
  color: var(--color-black-1000);
  margin-bottom: calc(var(--Grid-8) / 2);
  display: flex;
  align-items: center;
  gap: calc(var(--Grid-8) / 2);
}
.card-owner-sub {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .75;
  margin-bottom: calc(var(--Grid-8) / 2);
}
.card-diag-preview {
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
  line-height: 1.4;
}
.card-bottom-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: var(--font-size-sm);
}
.doctor-tag { color: var(--color-primary); font-weight: 600; }
.meds-count-badge {
  background: var(--tint-primary);
  color: var(--color-primary);
  padding: 2px var(--Grid-8);
  border-radius: var(--chip-radius);
  font-weight: 600;
  font-size: var(--font-size-sm);
}

/* Right Detail Column */
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

.detail-box {
  background: var(--color-surface);
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-lg);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.detail-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 2px solid var(--color-gray-400);
  padding-bottom: var(--space-md);
  margin-bottom: var(--space-md);
}
.detail-head h2 { font-size: var(--font-size-lg); font-weight: 700; margin: 0; }
.detail-head p {
  margin: calc(var(--Grid-8) / 2) 0 0;
  color: var(--color-black-1000);
  opacity: .7;
  font-size: var(--font-size-sm);
}

/* Info Banner: Pure White with border */
.info-banner {
  background: var(--color-surface);
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  margin-bottom: var(--space-md);
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-md);
}
.info-field small {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .7;
  display: block;
  margin-bottom: 2px;
}
.info-field strong {
  font-size: var(--font-size-md);
  color: var(--color-black-1000);
}
.info-field span {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
}

/* Diagnosis Box */
.diagnosis-full-card {
  background: var(--tint-background-strong);
  border-left: 4px solid var(--color-primary);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  margin-bottom: var(--space-lg);
}
.diagnosis-full-card h4 {
  margin: 0 0 calc(var(--Grid-8) / 2);
  font-size: var(--font-size-sm);
  font-weight: 700;
  color: var(--color-primary);
}
.diagnosis-full-card p {
  margin: 0;
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  white-space: pre-wrap;
  line-height: 1.6;
}

/* Medication Table */
.med-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: var(--space-lg);
  font-size: var(--font-size-sm);
}
.med-table th {
  text-align: left;
  padding: var(--Grid-8) var(--space-md);
  border-bottom: 1px solid var(--color-gray-400);
  color: var(--color-black-1000);
  opacity: .8;
  font-size: var(--font-size-sm);
}
.med-table td {
  padding: var(--Grid-8) var(--space-md);
  border-bottom: 1px dashed var(--color-gray-400);
}
.med-table td.price {
  text-align: right;
  font-weight: 600;
}

.total-pill {
  background: var(--tint-primary);
  border-radius: var(--radius-md);
  padding: var(--Grid-8) var(--space-md);
  margin-bottom: var(--space-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.total-pill strong { font-size: var(--font-size-md); }
.total-pill .amount {
  font-size: var(--font-size-lg);
  font-weight: 800;
  color: var(--color-primary);
}

/* Action Buttons */
.action-buttons-row {
  display: flex;
  gap: var(--Grid-8);
  flex-wrap: wrap;
}
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
.btn-secondary { background: var(--color-surface); color: var(--color-black-1000); border-color: var(--input-border); }
.btn-secondary:hover { background: var(--color-background); }

/* Responsive */
@media (max-width: 992px) {
  .master-detail-grid { grid-template-columns: 1fr; }
  .action-panel { position: static; }
}
@media (max-width: 768px) {
  .sidebar { display: none; }
  .topbar { display: flex; }
  main { padding: var(--space-md); }
  .info-banner { grid-template-columns: 1fr; }
}
.sidebar.mobile-open {
  display: flex; position: fixed; inset: 0 auto 0 0; z-index: 90;
  box-shadow: 4px 0 20px rgba(0,0,0,0.15);
}

/* Print Styles */
@media print {
  body { background: var(--color-surface) !important; }
  .sidebar, .topbar, .page-header, .queue-column, .action-buttons-row {
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
  .detail-box {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
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
          <li><a class="nav-link" href="billing_list.php"><span class="nav-icon">💵</span><span>การเงิน/ใบเสร็จ</span></a></li>
        </ul>
      </div>

      <div class="nav-group">
        <div class="nav-group-label">ประวัติและรายการ</div>
        <ul>
          <li><a class="nav-link" href="appointment_list.php"><span class="nav-icon">📋</span><span>รายการนัดหมาย</span></a></li>
          <li><a class="nav-link" href="treatment_history.php" aria-current="page"><span class="nav-icon">📁</span><span>ประวัติการรักษา</span></a></li>
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
      <div class="topbar-brand">ประวัติการรักษา</div>
      <div style="width:40px;"></div>
    </header>

    <main>
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1>
            <span class="page-header-icon">📁</span>
            ประวัติการรักษา (Treatment History)
          </h1>
          <p>ตรวจสอบผลการตรวจวินิจฉัยและรายการยาที่สั่งจ่ายย้อนหลังแบบเจาะจงเคส</p>
        </div>
        <div>
          <a href="../index.php" class="btn btn-secondary">
            ← กลับหน้า Dashboard
          </a>
        </div>
      </div>

      <!-- Master-Detail Layout -->
      <div class="master-detail-grid">

        <!-- Left Column: Master Filter & Queue -->
        <div class="queue-column">

          <div class="toolbar-card">
            <!-- Text Search -->
            <div class="search-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" id="historySearch" placeholder="ค้นหาชื่อสัตว์, เจ้าของ, สัตวแพทย์ หรือผลวินิจฉัย...">
            </div>

            <!-- Date Picker Filter (วัน/เดือน/ปี) -->
            <div class="date-filter-row">
              <div class="date-input-wrap">
                <input type="date" id="dateFilter" title="เลือก วัน/เดือน/ปี เพื่อกรองประวัติ">
              </div>
              <button type="button" class="btn-clear-date" id="btnClearDate">ล้างวันที่</button>
            </div>

            <!-- Quick Chips -->
            <div class="quick-chips">
              <button type="button" class="chip active" data-preset="all">ทั้งหมด</button>
              <button type="button" class="chip" data-preset="today">วันนี้</button>
              <button type="button" class="chip" data-preset="week">7 วันล่าสุด</button>
              <button type="button" class="chip" data-preset="month">เดือนนี้</button>
            </div>
          </div>

          <!-- Queue Header -->
          <div class="queue-header-info">
            <span style="font-weight:700;">รายการประวัติทั้งหมด</span>
            <span id="queueResultCount">0 รายการ</span>
          </div>

          <!-- Scrollable History List -->
          <div class="history-list" id="historyListContainer">
            <!-- Rendered by JS -->
          </div>

          <!-- Empty State when filter has no match -->
          <div id="noMatchState" style="display:none; text-align:center; padding:var(--space-24) var(--space-16); background:var(--color-surface); border-radius:var(--radius-md); border:1px dashed var(--color-gray-400); color:var(--color-black-1000); opacity:0.7;">
            <p style="margin:0; font-size:15px; font-weight:600;">ไม่พบประวัติการรักษาที่ตรงกับเงื่อนไข</p>
            <p style="margin:4px 0 0; font-size:13px;">ลองล้างคำค้นหาหรือตัวกรองวันที่</p>
          </div>

        </div>

        <!-- Right Column: Detail View -->
        <div class="action-panel">

          <!-- Initial Welcome View -->
          <div class="welcome-view" id="welcomeView">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <h3 style="font-size:var(--font-size-md); margin:0 0 var(--Grid-8); color:var(--color-black-1000);">กรุณาเลือกประวัติจากคอลัมน์ทางซ้าย</h3>
            <p style="margin:0; font-size:14px;">คลิกการ์ดประวัติการรักษาเพื่อเปิดดูผลวินิจฉัยฉบับเต็มและรายการยาทั้งหมด</p>
          </div>

          <!-- Detail Card -->
          <div class="detail-box" id="detailBox" style="display:none;">
            <div class="detail-head">
              <div>
                <h2 id="detailCaseTitle">บันทึกการรักษา #0</h2>
                <p id="detailCaseSubtitle">วันที่เข้ารับการตรวจ: -</p>
              </div>
              <div>
                <span class="card-id-badge" style="background:var(--tint-primary); color:var(--color-primary); font-size:var(--font-size-sm); padding:4px 10px;" id="detailStatusBadge">ตรวจเสร็จสิ้น</span>
              </div>
            </div>

            <!-- Patient & Owner Info Box (White background with border) -->
            <div class="info-banner">
              <div class="info-field">
                <small>คนไข้ (สัตว์เลี้ยง):</small>
                <strong id="detailPetName">-</strong>
              </div>
              <div class="info-field">
                <small>เจ้าของสัตว์เลี้ยง:</small>
                <strong id="detailOwnerName">-</strong>
              </div>
              <div class="info-field">
                <small>เบอร์โทรติดต่อ:</small>
                <span id="detailOwnerPhone">-</span>
              </div>
              <div class="info-field">
                <small>สัตวแพทย์ผู้ตรวจ:</small>
                <span id="detailDoctorName">-</span>
              </div>
            </div>

            <!-- Diagnosis Section -->
            <div class="diagnosis-full-card">
              <h4>🩺 ผลการตรวจร่างกายและการวินิจฉัย (Diagnosis)</h4>
              <p id="detailDiagnosisText">-</p>
            </div>

            <!-- Prescribed Medications Table -->
            <h4 style="font-size:15px; font-weight:700; margin-bottom:8px;">💊 รายการยาและเวชภัณฑ์ที่สั่งจ่าย</h4>
            <table class="med-table">
              <thead>
                <tr>
                  <th>ชื่อยา / เวชภัณฑ์</th>
                  <th style="text-align:right; width:120px;">ราคา (บาท)</th>
                </tr>
              </thead>
              <tbody id="detailMedTableBody">
                <!-- Injected via JS -->
              </tbody>
            </table>

            <!-- Total Pill -->
            <div class="total-pill">
              <strong>รวมค่ายาทั้งหมด:</strong>
              <span class="amount" id="detailTotalMedDisplay">฿0.00</span>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons-row">
              <button type="button" class="btn btn-secondary" id="btnPrintRecord">
                🖨️ พิมพ์ประวัติการรักษา
              </button>
              <a href="#" class="btn btn-primary" id="btnGoBilling" style="display:none;">
                💵 ไปยังหน้าการเงิน/ใบเสร็จ
              </a>
            </div>

          </div>

        </div>

      </div>

    </main>

  </div>

</div>

<script>
(function() {
  "use strict";

  const HISTORY_DATA = <?= json_encode($history_json, JSON_UNESCAPED_UNICODE) ?>;
  let selectedAppId = null;

  const els = {
    search: document.getElementById('historySearch'),
    dateFilter: document.getElementById('dateFilter'),
    btnClearDate: document.getElementById('btnClearDate'),
    quickChips: document.querySelectorAll('.quick-chips .chip'),
    resultCount: document.getElementById('queueResultCount'),
    listContainer: document.getElementById('historyListContainer'),
    noMatch: document.getElementById('noMatchState'),
    welcomeView: document.getElementById('welcomeView'),
    detailBox: document.getElementById('detailBox'),
    caseTitle: document.getElementById('detailCaseTitle'),
    caseSubtitle: document.getElementById('detailCaseSubtitle'),
    petName: document.getElementById('detailPetName'),
    ownerName: document.getElementById('detailOwnerName'),
    ownerPhone: document.getElementById('detailOwnerPhone'),
    doctorName: document.getElementById('detailDoctorName'),
    diagnosisText: document.getElementById('detailDiagnosisText'),
    medTableBody: document.getElementById('detailMedTableBody'),
    totalMedDisplay: document.getElementById('detailTotalMedDisplay'),
    btnPrintRecord: document.getElementById('btnPrintRecord'),
    btnGoBilling: document.getElementById('btnGoBilling'),
    hamburgerBtn: document.getElementById('hamburgerBtn'),
    sidebar: document.getElementById('sidebar')
  };

  // Helper Functions
  function formatDate(dStr) {
    if (!dStr) return '-';
    const parts = dStr.split('-');
    if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
    return dStr;
  }
  function formatNum(n) {
    return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  // Filter Logic
  function getFilteredHistory() {
    const q = els.search.value.trim().toLowerCase();
    const dateVal = els.dateFilter.value; // YYYY-MM-DD

    return HISTORY_DATA.filter(item => {
      // Date filter matching
      if (dateVal && item.date !== dateVal) {
        return false;
      }

      // Text search matching
      if (q) {
        const idMatch = String(item.app_id).includes(q) || `app-${item.app_id}`.includes(q);
        const petMatch = item.pet_name.toLowerCase().includes(q);
        const ownerMatch = item.owner_name.toLowerCase().includes(q);
        const docMatch = item.doctor_name.toLowerCase().includes(q);
        const diagMatch = item.diagnosis.toLowerCase().includes(q);
        if (!idMatch && !petMatch && !ownerMatch && !docMatch && !diagMatch) {
          return false;
        }
      }

      return true;
    });
  }

  // Render Left Column Cards
  function renderHistoryQueue() {
    const items = getFilteredHistory();
    els.resultCount.textContent = `${items.length} รายการ`;

    if (items.length === 0) {
      els.listContainer.innerHTML = '';
      els.noMatch.style.display = 'block';
      return;
    }

    els.noMatch.style.display = 'none';
    els.listContainer.innerHTML = items.map(item => {
      const isActive = item.app_id === selectedAppId ? 'active' : '';
      const medsCount = item.meds ? item.meds.length : 0;
      const medsBadgeText = medsCount > 0 ? `💊 ยา ${medsCount} รายการ` : `(ไม่มียา)`;

      return `
      <div class="history-card ${isActive}" onclick="window.__selectCase(${item.app_id})">
        <div class="card-top">
          <span class="card-id-badge">#APP-${item.app_id}</span>
          <span class="card-date">${formatDate(item.date)}</span>
        </div>
        <div class="card-pet-title">🐾 ${escapeHtml(item.pet_name)}</div>
        <div class="card-owner-sub">เจ้าของ: ${escapeHtml(item.owner_name)}</div>
        <div class="card-diag-preview">${escapeHtml(item.diagnosis)}</div>
        <div class="card-bottom-bar">
          <span class="doctor-tag">น.สพ. ${escapeHtml(item.doctor_name)}</span>
          <span class="meds-count-badge">${medsBadgeText}</span>
        </div>
      </div>
      `;
    }).join('');
  }

  // Select Case & Render Right Column
  window.__selectCase = function(appId) {
    selectedAppId = appId;
    renderHistoryQueue();

    const item = HISTORY_DATA.find(h => h.app_id === appId);
    if (!item) return;

    els.welcomeView.style.display = 'none';
    els.detailBox.style.display = 'block';

    els.caseTitle.textContent = `บันทึกการรักษา #APP-${item.app_id}`;
    els.caseSubtitle.textContent = `วันที่เข้ารับการตรวจ: ${formatDate(item.date)}`;

    els.petName.textContent = `🐾 ${item.pet_name}`;
    els.ownerName.textContent = item.owner_name;
    els.ownerPhone.textContent = item.owner_phone;
    els.doctorName.textContent = `น.สพ. ${item.doctor_name}`;
    els.diagnosisText.textContent = item.diagnosis;

    // Prescribed Medications Table
    let tableHtml = '';
    if (item.meds && item.meds.length > 0) {
      tableHtml = item.meds.map(m => `
        <tr>
          <td>💊 ${escapeHtml(m.name)}</td>
          <td class="price">฿${formatNum(m.price)}</td>
        </tr>
      `).join('');
    } else {
      tableHtml = `<tr><td colspan="2" style="color:var(--color-black-1000); opacity:0.6; text-align:center; padding:var(--space-16);">ไม่มีรายการสั่งยาในเคสนี้</td></tr>`;
    }
    els.medTableBody.innerHTML = tableHtml;
    els.totalMedDisplay.textContent = `฿${formatNum(item.total_med)}`;

    // Billing shortcut link
    if (item.receipt_id) {
      els.btnGoBilling.style.display = 'inline-flex';
      els.btnGoBilling.href = 'billing_list.php';
      els.btnGoBilling.textContent = `💵 ดูใบเสร็จ (ชำระแล้ว ฿${formatNum(item.grand_total)})`;
    } else {
      els.btnGoBilling.style.display = 'none';
    }

    // Auto scroll on mobile
    if (window.innerWidth <= 992) {
      els.detailBox.scrollIntoView({ behavior: 'smooth' });
    }
  };

  // Event Listeners
  els.search.addEventListener('input', () => {
    renderHistoryQueue();
  });

  els.dateFilter.addEventListener('change', () => {
    // Reset quick chips active state if custom date picked
    els.quickChips.forEach(c => c.classList.remove('active'));
    renderHistoryQueue();
  });

  els.btnClearDate.addEventListener('click', () => {
    els.dateFilter.value = '';
    els.quickChips.forEach(c => c.classList.remove('active'));
    document.querySelector('.quick-chips .chip[data-preset="all"]').classList.add('active');
    renderHistoryQueue();
  });

  // Quick Date Chips
  els.quickChips.forEach(chip => {
    chip.addEventListener('click', () => {
      els.quickChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');

      const preset = chip.dataset.preset;
      const today = new Date();
      const formatYmd = d => d.toISOString().split('T')[0];

      if (preset === 'all') {
        els.dateFilter.value = '';
      } else if (preset === 'today') {
        els.dateFilter.value = formatYmd(today);
      } else if (preset === 'week') {
        // Find if there are cases within last 7 days or set to today
        els.dateFilter.value = formatYmd(today);
      } else if (preset === 'month') {
        els.dateFilter.value = formatYmd(today);
      }
      renderHistoryQueue();
    });
  });

  // Print Record
  els.btnPrintRecord.addEventListener('click', () => {
    window.print();
  });

  // Mobile Hamburger Toggle
  els.hamburgerBtn.addEventListener('click', () => {
    els.sidebar.classList.toggle('mobile-open');
  });

  // Init
  renderHistoryQueue();

  // Check URL query parameter for pre-selection (e.g. ?app_id=123)
  const urlParams = new URLSearchParams(window.location.search);
  const paramAppId = urlParams.get('app_id') || urlParams.get('id');
  if (paramAppId) {
    window.__selectCase(parseInt(paramAppId, 10));
  } else if (HISTORY_DATA.length > 0) {
    // Auto select first record
    window.__selectCase(HISTORY_DATA[0].app_id);
  }

})();
</script>
</body>
</html>