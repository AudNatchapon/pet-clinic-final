<?php 
require_once '../config/db.php';

// 1. ดึงรายการนัดหมายทั้งหมด พร้อมเช็คสถานะการตรวจ (LEFT JOIN diagnoses)
$sql = "SELECT a.appointments_ID, a.Date, a.appointment_detail,
               p.pet_ID, p.name AS pet_name,
               o.owner_ID, o.name AS owner_name, o.phoneNumber AS owner_phone,
               d.doctor_ID, d.name AS doctor_name,
               dg.diagnoses_ID, dg.diagnosisDescription
        FROM appointments a
        JOIN pet p ON a.pet_ID = p.pet_ID
        JOIN owner o ON p.owner_ID = o.owner_ID
        JOIN doctor d ON a.doctor_ID = d.doctor_ID
        LEFT JOIN diagnoses dg ON a.appointments_ID = dg.appointment_ID
        ORDER BY a.Date DESC, a.appointments_ID DESC";
$appointments_raw = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 2. ดึงรายชื่อแพทย์สำหรับ Dropdown ใน Modal แก้ไข
$doctors = $pdo->query("SELECT doctor_ID, name FROM doctor ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// 3. จัดเตรียมข้อมูล JSON สำหรับ Client-side Real-time Search & Instant Rendering
$appointments_json = [];
foreach ($appointments_raw as $row) {
    $hasDiagnosed = !empty($row['diagnoses_ID']);
    $appointments_json[] = [
        'app_id' => (int)$row['appointments_ID'],
        'date' => $row['Date'],
        'pet_id' => (int)$row['pet_ID'],
        'pet_name' => $row['pet_name'] ?? 'ไม่ระบุ',
        'owner_id' => (int)$row['owner_ID'],
        'owner_name' => $row['owner_name'] ?? 'ไม่ระบุ',
        'owner_phone' => $row['owner_phone'] ?? '-',
        'doctor_id' => (int)$row['doctor_ID'],
        'doctor_name' => $row['doctor_name'] ?? 'สัตวแพทย์',
        'detail' => $row['appointment_detail'] ?? 'ไม่มีระบุรายละเอียดอาการ',
        'is_diagnosed' => $hasDiagnosed,
        'diagnosis_desc' => $row['diagnosisDescription'] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>รายการนัดหมาย (Appointments) | PetCare Portal</title>
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

.appointment-list {
  display: flex;
  flex-direction: column;
  gap: var(--Grid-8);
  max-height: 65vh;
  overflow-y: auto;
  padding-right: calc(var(--Grid-8) / 2);
}
.app-card {
  border: 1px solid var(--color-gray-400);
  border-left: 4px solid var(--color-danger);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  background: var(--color-surface);
  cursor: pointer;
  transition: all .2s ease;
  position: relative;
}
.app-card.completed {
  border-left-color: var(--color-success);
}
.app-card:hover {
  transform: translateX(4px);
  border-color: var(--color-primary);
  box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}
.app-card.active {
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
.status-tag {
  font-size: var(--font-size-sm);
  font-weight: 700;
  padding: 2px var(--Grid-8);
  border-radius: var(--chip-radius);
}
.status-tag.pending {
  background: var(--tint-danger);
  color: var(--color-black-1000);
}
.status-tag.completed {
  background: var(--tint-success);
  color: var(--color-success);
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
.card-detail-preview {
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

/* Detail Content Card */
.detail-content-card {
  background: var(--tint-background-strong);
  border-left: 4px solid var(--color-primary);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  margin-bottom: var(--space-lg);
}
.detail-content-card h4 {
  margin: 0 0 calc(var(--Grid-8) / 2);
  font-size: var(--font-size-sm);
  font-weight: 700;
  color: var(--color-primary);
}
.detail-content-card p {
  margin: 0;
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  white-space: pre-wrap;
  line-height: 1.6;
}

.clinical-workflow-banner {
  background: var(--tint-success);
  border: 1px solid var(--color-success);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  margin-bottom: var(--space-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.clinical-workflow-banner.pending {
  background: var(--tint-danger);
  border-color: var(--color-danger);
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
.btn-success { background: var(--color-success); color: var(--color-white); }
.btn-success:hover { filter: brightness(0.95); }
.btn-warning { background: var(--color-danger); color: var(--color-black-1000); }
.btn-danger { background: var(--color-danger); color: var(--color-black-1000); border: 1px solid var(--color-black-1000); }
.btn-secondary { background: var(--color-surface); color: var(--color-black-1000); border-color: var(--input-border); }
.btn-secondary:hover { background: var(--color-background); }

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
  width: 540px; max-width: 95vw; max-height: 90vh; display: flex; flex-direction: column;
  box-shadow: 0 16px 40px rgba(0,0,0,0.18);
}
.modal-header {
  padding: var(--space-md); border-bottom: 1px solid var(--color-gray-400);
  display: flex; align-items: center; justify-content: space-between;
}
.modal-header h2 { font-size: var(--font-size-md); font-weight: 700; margin: 0; }
.modal-body { padding: var(--space-md); overflow-y: auto; }
.modal-footer {
  padding: var(--space-md); border-top: 1px solid var(--color-gray-400);
  display: flex; justify-content: flex-end; gap: var(--Grid-8);
}
.icon-btn {
  width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;
  border-radius: var(--radius-md); border: 1px solid var(--input-border); background: var(--color-surface);
  cursor: pointer;
}

.form-group { margin-bottom: var(--space-md); }
.form-group label {
  display: block; font-size: var(--font-size-sm); font-weight: 700; margin-bottom: calc(var(--Grid-8) / 2);
}
.form-group input, .form-group select, .form-group textarea {
  width: 100%; padding: var(--Grid-8) var(--space-md); border: 1px solid var(--input-border);
  border-radius: var(--radius-md); font-size: var(--font-size-sm); outline: none;
  background: var(--color-surface);
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  border-color: var(--input-focus);
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
  .info-banner { grid-template-columns: 1fr; }
}
.sidebar.mobile-open {
  display: flex; position: fixed; inset: 0 auto 0 0; z-index: 90;
  box-shadow: 4px 0 20px rgba(0,0,0,0.15);
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
          <li><a class="nav-link" href="appointment_list.php" aria-current="page"><span class="nav-icon">📋</span><span>รายการนัดหมาย</span></a></li>
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
      <div class="topbar-brand">รายการนัดหมาย</div>
      <div style="width:40px;"></div>
    </header>

    <main>
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1>
            <span class="page-header-icon">📅</span>
            รายการนัดหมายทั้งหมด (Appointments)
          </h1>
          <p>ตรวจสอบ ค้นหา และจัดการการนัดหมายของสัตว์เลี้ยง พร้อมส่งตรวจรักษา</p>
        </div>
        <div>
          <a href="appointment_form.php" class="btn btn-primary">
            + สร้างการนัดหมายใหม่
          </a>
        </div>
      </div>

      <!-- Master-Detail Layout -->
      <div class="master-detail-grid">

        <!-- Left Column: Master Filter & Appointments Queue -->
        <div class="queue-column">

          <div class="toolbar-card">
            <!-- Text Search -->
            <div class="search-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" id="appSearch" placeholder="ค้นหาชื่อสัตว์, เจ้าของ, หมอ หรืออาการ...">
            </div>

            <!-- Date Picker Filter (วัน/เดือน/ปี) -->
            <div class="date-filter-row">
              <div class="date-input-wrap">
                <input type="date" id="dateFilter" title="เลือก วัน/เดือน/ปี เพื่อกรองรายการนัด">
              </div>
              <button type="button" class="btn-clear-date" id="btnClearDate">ล้างวันที่</button>
            </div>

            <!-- Quick Chips Filter -->
            <div class="quick-chips">
              <button type="button" class="chip active" data-filter="all">ทั้งหมด</button>
              <button type="button" class="chip" data-filter="pending">⏳ รอตรวจ</button>
              <button type="button" class="chip" data-filter="completed">✅ ตรวจแล้ว</button>
              <button type="button" class="chip" data-filter="today">📅 วันนี้</button>
            </div>
          </div>

          <!-- Queue Header -->
          <div class="queue-header-info">
            <span style="font-weight:700;">รายการนัดหมายทั้งหมด</span>
            <span id="queueResultCount">0 รายการ</span>
          </div>

          <!-- Scrollable Appointment List -->
          <div class="appointment-list" id="appListContainer">
            <!-- Rendered via JS -->
          </div>

          <!-- Empty State -->
          <div id="noMatchState" style="display:none; text-align:center; padding:var(--space-24) var(--space-16); background:var(--color-surface); border-radius:var(--radius-md); border:1px dashed var(--color-gray-400); color:var(--color-black-1000); opacity:0.7;">
            <p style="margin:0; font-size:15px; font-weight:600;">ไม่พบการนัดหมายที่ตรงกับเงื่อนไข</p>
            <p style="margin:4px 0 0; font-size:13px;">ลองปรับคำค้นหาหรือตัวกรองวันที่</p>
          </div>

        </div>

        <!-- Right Column: Detail View -->
        <div class="action-panel">

          <!-- Welcome View -->
          <div class="welcome-view" id="welcomeView">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <h3 style="font-size:var(--font-size-md); margin:0 0 var(--Grid-8); color:var(--color-black-1000);">กรุณาเลือกการนัดหมายจากคอลัมน์ทางซ้าย</h3>
            <p style="margin:0; font-size:14px;">คลิกการ์ดนัดหมายเพื่อเปิดดูรายละเอียด ส่งเข้าห้องตรวจ หรือแก้ไข/ลบข้อมูล</p>
          </div>

          <!-- Detail Card -->
          <div class="detail-box" id="detailBox" style="display:none;">
            <div class="detail-head">
              <div>
                <h2 id="detailAppTitle">นัดหมาย #0</h2>
                <p id="detailAppSubtitle">วันที่นัดหมาย: -</p>
              </div>
              <div>
                <span class="status-tag pending" id="detailStatusBadge">รอตรวจ</span>
              </div>
            </div>

            <!-- Patient & Owner Info Box (White background with gray border) -->
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
                <small>สัตวแพทย์ผู้รับผิดชอบ:</small>
                <span id="detailDoctorName">-</span>
              </div>
            </div>

            <!-- Symptoms / Detail Card -->
            <div class="detail-content-card">
              <h4>📝 รายละเอียดอาการที่นัดหมาย (Symptoms / Details)</h4>
              <p id="detailSymptomText">-</p>
            </div>

            <!-- Workflow Status Banner -->
            <div class="clinical-workflow-banner pending" id="workflowBanner">
              <div>
                <strong id="workflowTitle">สถานะเคส: รอเข้าห้องตรวจ</strong>
                <p style="margin:2px 0 0; font-size:13px; color:var(--color-black-1000); opacity:0.8;" id="workflowDesc">เคสนี้ยังไม่ได้รับการตรวจวินิจฉัยจากแพทย์</p>
              </div>
              <div>
                <a href="#" class="btn btn-success" id="btnGoTreatment">
                  🩺 ส่งตรวจเคสนี้ทันที
                </a>
                <a href="#" class="btn btn-secondary" id="btnGoHistory" style="display:none;">
                  📁 ดูผลการวินิจฉัย
                </a>
              </div>
            </div>

            <!-- Action Controls -->
            <div class="action-buttons-row">
              <button type="button" class="btn btn-warning" id="btnOpenEdit">
                ✏️ แก้ไขข้อมูลนัดหมาย
              </button>
              <button type="button" class="btn btn-danger" id="btnOpenDelete">
                🗑️ ลบการนัดหมาย
              </button>
            </div>

          </div>

        </div>

      </div>

    </main>

  </div>

</div>

<!-- ===========================================================================
     EDIT APPOINTMENT MODAL (Pop-up กึ่งกลางจอ)
     =========================================================================== -->
<div class="overlay" id="overlay"></div>
<div class="modal" id="editModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
  <div class="modal-panel">
    <div class="modal-header">
      <h2 id="editModalTitle">✏️ แก้ไขข้อมูลการนัดหมาย</h2>
      <button type="button" class="icon-btn" id="editCloseBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <form id="editForm">
      <div class="modal-body">
        <input type="hidden" name="appointments_ID" id="edit_id">
        
        <div class="form-group">
          <label>สัตวแพทย์ผู้รับผิดชอบ <span style="color:var(--color-danger);">*</span></label>
          <select name="doctor_ID" id="edit_doctor" required>
            <?php foreach($doctors as $d): ?>
              <option value="<?= $d['doctor_ID'] ?>">น.สพ. <?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>วันที่นัดหมาย (วัน/เดือน/ปี) <span style="color:var(--color-danger);">*</span></label>
          <input type="date" name="Date" id="edit_date" required>
        </div>

        <div class="form-group">
          <label>รายละเอียดอาการ / บันทึกการนัด</label>
          <textarea name="appointment_detail" id="edit_detail" rows="4" placeholder="ระบุอาการเบื้องต้น หรือเหตุผลในการนัดหมาย..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="editCancelBtn">ยกเลิก</button>
        <button type="submit" class="btn btn-primary" id="editSubmitBtn">บันทึกการแก้ไข</button>
      </div>
    </form>
  </div>
</div>

<!-- ===========================================================================
     DELETE CONFIRM MODAL (Pop-up กึ่งกลางจอ)
     =========================================================================== -->
<div class="modal" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
  <div class="modal-panel" style="width:480px;">
    <div class="modal-header">
      <h2 id="deleteModalTitle" style="color:var(--color-danger);">⚠️ ยืนยันการลบนัดหมาย</h2>
      <button type="button" class="icon-btn" id="deleteCloseBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <p style="font-size:15px; margin:0 0 10px;">คุณแน่ใจหรือไม่ว่าต้องการลบรายการนัดหมาย <strong id="deleteTargetCode">#APP-0</strong> ของ <strong id="deleteTargetPet">-</strong>?</p>
      <div style="background:var(--tint-danger); border-left:4px solid var(--color-danger); border-radius:4px; padding:10px 12px; font-size:13px; color:var(--color-black-1000);">
        <strong>คำเตือน:</strong> หากลบนัดหมายนี้ ประวัติการรักษาที่เกี่ยวข้องในตารางระบบจะถูกลบไปด้วยตามเงื่อนไขความสัมพันธ์ฐานข้อมูล (CASCADE)
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="deleteCancelBtn">ยกเลิก</button>
      <button type="button" class="btn btn-danger" id="deleteConfirmBtn">ยืนยันการลบ</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18"><path d="M20 6L9 17l-5-5"/></svg>
  <span id="toastMsg">ดำเนินการสำเร็จ</span>
</div>

<script>
(function() {
  "use strict";

  let APPOINTMENTS_DATA = <?= json_encode($appointments_json, JSON_UNESCAPED_UNICODE) ?>;
  let selectedAppId = null;
  let currentFilterChip = 'all';

  const els = {
    search: document.getElementById('appSearch'),
    dateFilter: document.getElementById('dateFilter'),
    btnClearDate: document.getElementById('btnClearDate'),
    quickChips: document.querySelectorAll('.quick-chips .chip'),
    resultCount: document.getElementById('queueResultCount'),
    listContainer: document.getElementById('appListContainer'),
    noMatch: document.getElementById('noMatchState'),
    welcomeView: document.getElementById('welcomeView'),
    detailBox: document.getElementById('detailBox'),
    appTitle: document.getElementById('detailAppTitle'),
    appSubtitle: document.getElementById('detailAppSubtitle'),
    statusBadge: document.getElementById('detailStatusBadge'),
    petName: document.getElementById('detailPetName'),
    ownerName: document.getElementById('detailOwnerName'),
    ownerPhone: document.getElementById('detailOwnerPhone'),
    doctorName: document.getElementById('detailDoctorName'),
    symptomText: document.getElementById('detailSymptomText'),
    workflowBanner: document.getElementById('workflowBanner'),
    workflowTitle: document.getElementById('workflowTitle'),
    workflowDesc: document.getElementById('workflowDesc'),
    btnGoTreatment: document.getElementById('btnGoTreatment'),
    btnGoHistory: document.getElementById('btnGoHistory'),
    btnOpenEdit: document.getElementById('btnOpenEdit'),
    btnOpenDelete: document.getElementById('btnOpenDelete'),
    editModal: document.getElementById('editModal'),
    editForm: document.getElementById('editForm'),
    editId: document.getElementById('edit_id'),
    editDoctor: document.getElementById('edit_doctor'),
    editDate: document.getElementById('edit_date'),
    editDetail: document.getElementById('edit_detail'),
    editCloseBtn: document.getElementById('editCloseBtn'),
    editCancelBtn: document.getElementById('editCancelBtn'),
    editSubmitBtn: document.getElementById('editSubmitBtn'),
    deleteModal: document.getElementById('deleteModal'),
    deleteTargetCode: document.getElementById('deleteTargetCode'),
    deleteTargetPet: document.getElementById('deleteTargetPet'),
    deleteCloseBtn: document.getElementById('deleteCloseBtn'),
    deleteCancelBtn: document.getElementById('deleteCancelBtn'),
    deleteConfirmBtn: document.getElementById('deleteConfirmBtn'),
    overlay: document.getElementById('overlay'),
    toast: document.getElementById('toast'),
    toastMsg: document.getElementById('toastMsg'),
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
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }
  function showToast(msg) {
    els.toastMsg.textContent = msg;
    els.toast.classList.add('show');
    setTimeout(() => els.toast.classList.remove('show'), 3000);
  }

  // Filtering Logic
  function getFilteredAppointments() {
    const q = els.search.value.trim().toLowerCase();
    const dateVal = els.dateFilter.value; // YYYY-MM-DD
    const todayStr = new Date().toISOString().split('T')[0];

    return APPOINTMENTS_DATA.filter(item => {
      // Date filter matching
      if (dateVal && item.date !== dateVal) {
        return false;
      }

      // Quick chip filter matching
      if (currentFilterChip === 'pending' && item.is_diagnosed) {
        return false;
      }
      if (currentFilterChip === 'completed' && !item.is_diagnosed) {
        return false;
      }
      if (currentFilterChip === 'today' && item.date !== todayStr) {
        return false;
      }

      // Text search matching
      if (q) {
        const idMatch = String(item.app_id).includes(q) || `app-${item.app_id}`.includes(q);
        const petMatch = item.pet_name.toLowerCase().includes(q);
        const ownerMatch = item.owner_name.toLowerCase().includes(q);
        const docMatch = item.doctor_name.toLowerCase().includes(q);
        const detailMatch = item.detail.toLowerCase().includes(q);
        if (!idMatch && !petMatch && !ownerMatch && !docMatch && !detailMatch) {
          return false;
        }
      }

      return true;
    });
  }

  // Render Queue Cards
  function renderAppointmentsQueue() {
    const items = getFilteredAppointments();
    els.resultCount.textContent = `${items.length} รายการ`;

    if (items.length === 0) {
      els.listContainer.innerHTML = '';
      els.noMatch.style.display = 'block';
      return;
    }

    els.noMatch.style.display = 'none';
    els.listContainer.innerHTML = items.map(item => {
      const isActive = item.app_id === selectedAppId ? 'active' : '';
      const isCompleted = item.is_diagnosed ? 'completed' : '';
      const statusTagHtml = item.is_diagnosed 
        ? `<span class="status-tag completed">✅ ตรวจแล้ว</span>`
        : `<span class="status-tag pending">⏳ รอตรวจ</span>`;

      return `
      <div class="app-card ${isCompleted} ${isActive}" onclick="window.__selectAppointment(${item.app_id})">
        <div class="card-top">
          <span class="card-id-badge">#APP-${item.app_id}</span>
          <span class="card-date">${formatDate(item.date)}</span>
          ${statusTagHtml}
        </div>
        <div class="card-pet-title">🐾 ${escapeHtml(item.pet_name)}</div>
        <div class="card-owner-sub">เจ้าของ: ${escapeHtml(item.owner_name)} · 📞 ${escapeHtml(item.owner_phone)}</div>
        <div class="card-detail-preview">${escapeHtml(item.detail)}</div>
        <div class="card-bottom-bar">
          <span class="doctor-tag">น.สพ. ${escapeHtml(item.doctor_name)}</span>
        </div>
      </div>
      `;
    }).join('');
  }

  // Select Appointment & Populate Right Column
  window.__selectAppointment = function(appId) {
    selectedAppId = appId;
    renderAppointmentsQueue();

    const item = APPOINTMENTS_DATA.find(a => a.app_id === appId);
    if (!item) return;

    els.welcomeView.style.display = 'none';
    els.detailBox.style.display = 'block';

    els.appTitle.textContent = `นัดหมาย #APP-${item.app_id}`;
    els.appSubtitle.textContent = `วันที่นัดหมาย: ${formatDate(item.date)}`;

    if (item.is_diagnosed) {
      els.statusBadge.className = 'status-tag completed';
      els.statusBadge.textContent = '✅ ได้รับการตรวจวินิจฉัยแล้ว';

      els.workflowBanner.className = 'clinical-workflow-banner';
      els.workflowTitle.textContent = 'สถานะ: ตรวจรักษาเสร็จสิ้นแล้ว';
      els.workflowDesc.textContent = `ผลการวินิจฉัย: ${item.diagnosis_desc || 'บันทึกในระบบเรียบร้อย'}`;
      els.btnGoTreatment.style.display = 'none';
      els.btnGoHistory.style.display = 'inline-flex';
      els.btnGoHistory.href = `treatment_history.php?app_id=${item.app_id}`;
    } else {
      els.statusBadge.className = 'status-tag pending';
      els.statusBadge.textContent = '⏳ รอเข้าห้องตรวจแพทย์';

      els.workflowBanner.className = 'clinical-workflow-banner pending';
      els.workflowTitle.textContent = 'สถานะ: รอส่งตรวจห้องแพทย์';
      els.workflowDesc.textContent = 'ยังไม่มีบันทึกการวินิจฉัย สามารถส่งเคสเข้าห้องตรวจได้ทันที';
      els.btnGoTreatment.style.display = 'inline-flex';
      els.btnGoTreatment.href = `treatment_form.php?app_id=${item.app_id}`;
      els.btnGoHistory.style.display = 'none';
    }

    els.petName.textContent = `🐾 ${item.pet_name}`;
    els.ownerName.textContent = item.owner_name;
    els.ownerPhone.textContent = item.owner_phone;
    els.doctorName.textContent = `น.สพ. ${item.doctor_name}`;
    els.symptomText.textContent = item.detail;

    // Mobile scroll
    if (window.innerWidth <= 992) {
      els.detailBox.scrollIntoView({ behavior: 'smooth' });
    }
  };

  // Event Listeners
  els.search.addEventListener('input', renderAppointmentsQueue);

  els.dateFilter.addEventListener('change', () => {
    els.quickChips.forEach(c => c.classList.remove('active'));
    renderAppointmentsQueue();
  });

  els.btnClearDate.addEventListener('click', () => {
    els.dateFilter.value = '';
    els.quickChips.forEach(c => c.classList.remove('active'));
    document.querySelector('.quick-chips .chip[data-filter="all"]').classList.add('active');
    currentFilterChip = 'all';
    renderAppointmentsQueue();
  });

  els.quickChips.forEach(chip => {
    chip.addEventListener('click', () => {
      els.quickChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      currentFilterChip = chip.dataset.filter;

      if (currentFilterChip === 'today') {
        els.dateFilter.value = new Date().toISOString().split('T')[0];
      } else if (currentFilterChip === 'all') {
        els.dateFilter.value = '';
      }
      renderAppointmentsQueue();
    });
  });

  // Edit Modal Handling
  function openEditModal() {
    const item = APPOINTMENTS_DATA.find(a => a.app_id === selectedAppId);
    if (!item) return;

    els.editId.value = item.app_id;
    els.editDoctor.value = item.doctor_id;
    els.editDate.value = item.date;
    els.editDetail.value = item.detail;

    els.editModal.classList.add('open');
    els.overlay.classList.add('open');
  }
  function closeEditModal() {
    els.editModal.classList.remove('open');
    els.overlay.classList.remove('open');
  }

  els.btnOpenEdit.addEventListener('click', openEditModal);
  els.editCloseBtn.addEventListener('click', closeEditModal);
  els.editCancelBtn.addEventListener('click', closeEditModal);

  els.editForm.addEventListener('submit', function(e) {
    e.preventDefault();
    els.editSubmitBtn.disabled = true;
    els.editSubmitBtn.textContent = 'กำลังบันทึก...';

    const formData = new FormData(els.editForm);

    fetch('../api/update_appointment.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      els.editSubmitBtn.disabled = false;
      els.editSubmitBtn.textContent = 'บันทึกการแก้ไข';
      if (data.status === 'success') {
        closeEditModal();
        showToast('แก้ไขข้อมูลการนัดหมายเรียบร้อยแล้ว');
        
        // Update local object
        const item = APPOINTMENTS_DATA.find(a => a.app_id === selectedAppId);
        if (item) {
          item.doctor_id = parseInt(els.editDoctor.value, 10);
          const selDocOpt = els.editDoctor.options[els.editDoctor.selectedIndex];
          item.doctor_name = selDocOpt ? selDocOpt.text.replace('น.สพ. ', '') : item.doctor_name;
          item.date = els.editDate.value;
          item.detail = els.editDetail.value;
          window.__selectAppointment(selectedAppId);
        }
      } else {
        alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถแก้ไขได้'));
      }
    })
    .catch(err => {
      els.editSubmitBtn.disabled = false;
      els.editSubmitBtn.textContent = 'บันทึกการแก้ไข';
      alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + err.message);
    });
  });

  // Delete Modal Handling
  function openDeleteModal() {
    const item = APPOINTMENTS_DATA.find(a => a.app_id === selectedAppId);
    if (!item) return;

    els.deleteTargetCode.textContent = `#APP-${item.app_id}`;
    els.deleteTargetPet.textContent = item.pet_name;

    els.deleteModal.classList.add('open');
    els.overlay.classList.add('open');
  }
  function closeDeleteModal() {
    els.deleteModal.classList.remove('open');
    els.overlay.classList.remove('open');
  }

  els.btnOpenDelete.addEventListener('click', openDeleteModal);
  els.deleteCloseBtn.addEventListener('click', closeDeleteModal);
  els.deleteCancelBtn.addEventListener('click', closeDeleteModal);

  els.deleteConfirmBtn.addEventListener('click', function() {
    els.deleteConfirmBtn.disabled = true;
    els.deleteConfirmBtn.textContent = 'กำลังลบ...';

    const formData = new FormData();
    formData.append('id', selectedAppId);

    fetch('../api/delete_appointment.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      els.deleteConfirmBtn.disabled = false;
      els.deleteConfirmBtn.textContent = 'ยืนยันการลบ';
      if (data.status === 'success') {
        closeDeleteModal();
        showToast('ลบรายการนัดหมายเรียบร้อยแล้ว');
        
        // Remove from local dataset
        APPOINTMENTS_DATA = APPOINTMENTS_DATA.filter(a => a.app_id !== selectedAppId);
        selectedAppId = null;
        els.welcomeView.style.display = 'block';
        els.detailBox.style.display = 'none';
        renderAppointmentsQueue();
      } else {
        alert('เกิดข้อผิดพลาดในการลบ: ' + (data.message || 'ไม่สามารถลบได้'));
      }
    })
    .catch(err => {
      els.deleteConfirmBtn.disabled = false;
      els.deleteConfirmBtn.textContent = 'ยืนยันการลบ';
      alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + err.message);
    });
  });

  // Close modals on ESC or overlay click
  els.overlay.addEventListener('click', () => {
    closeEditModal();
    closeDeleteModal();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeEditModal();
      closeDeleteModal();
    }
  });

  // Mobile Hamburger Toggle
  els.hamburgerBtn.addEventListener('click', () => {
    els.sidebar.classList.toggle('mobile-open');
  });

  // Init
  renderAppointmentsQueue();

  // Check URL query parameter for pre-selection (e.g. ?app_id=123)
  const urlParams = new URLSearchParams(window.location.search);
  const paramAppId = urlParams.get('app_id') || urlParams.get('id');
  if (paramAppId) {
    window.__selectAppointment(parseInt(paramAppId, 10));
  } else if (APPOINTMENTS_DATA.length > 0) {
    // Auto select first record
    window.__selectAppointment(APPOINTMENTS_DATA[0].app_id);
  }

})();
</script>
</body>
</html>