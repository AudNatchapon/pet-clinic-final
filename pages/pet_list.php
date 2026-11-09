<?php 
require_once '../config/db.php';

// รับค่าการค้นหา (คง GET parameter เดิม 100%)
$search = $_GET['search'] ?? '';

// ดึงข้อมูลสัตว์เลี้ยง พร้อมชื่อเจ้าของ เบอร์โทรศัพท์ และชื่อสายพันธุ์ พร้อมนับจำนวนประวัติการรักษา
$sql = "SELECT p.*, o.name AS owner_name, o.phoneNumber AS owner_phone, b.breed_detail,
        (SELECT COUNT(*) FROM appointments a WHERE a.pet_ID = p.pet_ID) AS appointment_count
        FROM pet p
        LEFT JOIN owner o ON p.owner_ID = o.owner_ID
        LEFT JOIN breed b ON p.breed_ID = b.breed_ID";

$params = [];
if (!empty($search)) {
    $sql .= " WHERE p.name LIKE ? OR o.name LIKE ? OR p.pet_ID LIKE ? OR b.breed_detail LIKE ?";
    $safe_search = "%$search%";
    $params = [$safe_search, $safe_search, $safe_search, $safe_search];
}
$sql .= " ORDER BY p.pet_ID DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงรายการสายพันธุ์สำหรับ Form เพิ่ม/แก้ไข
$breed_stmt = $pdo->query("SELECT * FROM breed ORDER BY breed_ID ASC");
$all_breeds = $breed_stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงรายการเจ้าของสำหรับ Form เพิ่ม/แก้ไข
$owner_stmt = $pdo->query("SELECT owner_ID, name, phoneNumber FROM owner ORDER BY name ASC");
$all_owners = $owner_stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงประวัติการรักษาทั้งหมดเพื่อเตรียมให้ History Drawer สไลด์แสดงผลได้ทันที
$hist_sql = "SELECT a.appointments_ID, a.pet_ID, a.Date, d.name AS doctor_name, 
                    a.appointment_detail, dg.diagnosisDescription
             FROM appointments a
             LEFT JOIN doctor d ON a.doctor_ID = d.doctor_ID
             LEFT JOIN diagnoses dg ON a.appointments_ID = dg.appointment_ID
             ORDER BY a.Date DESC, a.appointments_ID DESC";
$hist_stmt = $pdo->query($hist_sql);
$all_history_raw = $hist_stmt->fetchAll(PDO::FETCH_ASSOC);

$pet_history_map = [];
foreach ($all_history_raw as $h) {
    $pId = (int)$h['pet_ID'];
    if (!isset($pet_history_map[$pId])) {
        $pet_history_map[$pId] = [];
    }
    $diag = !empty($h['diagnosisDescription']) ? $h['diagnosisDescription'] : ($h['appointment_detail'] ?? 'ตรวจสุขภาพทั่วไป');
    $pet_history_map[$pId][] = [
        'id' => (int)$h['appointments_ID'],
        'date' => $h['Date'] ?? '-',
        'vet' => !empty($h['doctor_name']) ? $h['doctor_name'] : 'สัตวแพทย์เวรประจำวัน',
        'diagnosis' => $diag,
        'note' => $h['appointment_detail'] ?? '-'
    ];
}

// แปลงข้อมูลสัตว์เลี้ยงเป็นรูปแบบโครงสร้างสำหรับ JavaScript & Client-side Rendering
$client_pets = [];
foreach ($pets as $row) {
    $pId = (int)$row['pet_ID'];
    $bId = (int)($row['breed_ID'] ?? 0);
    $bName = $row['breed_detail'] ?? '';
    
    // จัดกลุ่มประเภทสัตว์ตามสายพันธุ์
    $type = 'other';
    if (($bId >= 1 && $bId <= 8) || stripos($bName, 'Retriever') !== false || stripos($bName, 'Beagle') !== false || stripos($bName, 'Pug') !== false || stripos($bName, 'Poodle') !== false || stripos($bName, 'Chihuahua') !== false || stripos($bName, 'Husky') !== false || stripos($bName, 'Shih Tzu') !== false) {
        $type = 'dog';
    } elseif (($bId >= 9 && $bId <= 15) || stripos($bName, 'แมว') !== false || stripos($bName, 'Cat') !== false || stripos($bName, 'Persian') !== false || stripos($bName, 'Fold') !== false || stripos($bName, 'Coon') !== false || stripos($bName, 'Bengal') !== false || stripos($bName, 'Sphynx') !== false) {
        $type = 'cat';
    } elseif (stripos($bName, 'กระต่าย') !== false || stripos($bName, 'Rabbit') !== false) {
        $type = 'rabbit';
    } elseif (stripos($bName, 'นก') !== false || stripos($bName, 'Bird') !== false) {
        $type = 'bird';
    }

    $genderVal = 'other';
    $rawGender = strtolower($row['gender'] ?? '');
    if ($rawGender === 'male' || $rawGender === 'ตัวผู้') {
        $genderVal = 'male';
    } elseif ($rawGender === 'female' || $rawGender === 'ตัวเมีย') {
        $genderVal = 'female';
    }

    $client_pets[] = [
        'id' => $pId,
        'code' => 'PET-' . str_pad($pId, 4, '0', STR_PAD_LEFT),
        'name' => $row['name'] ?? 'ไม่มีชื่อ',
        'type' => $type,
        'breed' => !empty($bName) ? $bName : ($bId ? "สายพันธุ์ #$bId" : 'ไม่ระบุสายพันธุ์'),
        'breed_id' => $bId,
        'owner_id' => (int)($row['owner_ID'] ?? 0),
        'owner' => !empty($row['owner_name']) ? $row['owner_name'] : 'ไม่ระบุเจ้าของ',
        'ownerPhone' => !empty($row['owner_phone']) ? $row['owner_phone'] : '-',
        'gender' => $genderVal,
        'dob' => !empty($row['dob']) ? $row['dob'] : '',
        'age' => $row['age'] ?? '',
        'history' => $pet_history_map[$pId] ?? []
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>จัดการข้อมูลสัตว์เลี้ยง | PetCare Portal</title>
<!-- โหลด Design Tokens จาก design.md -->
<link rel="stylesheet" href="../css/petcare-token.css">
<style>
/* ==========================================================================
   RESET / BASE
   ========================================================================== */
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
h1, h2, h3, h4 {
  font-family: var(--font-family-base);
  margin: 0;
}
button { font-family: inherit; cursor: pointer; }
input, select, textarea { font-family: inherit; }
a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }

.sr-only {
  position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
  overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}

/* Focus visibility — Accessibility */
a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, [tabindex]:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
  border-radius: var(--radius-md);
}

/* ==========================================================================
   LAYOUT SHELL (App Shell)
   ========================================================================== */
.app {
  display: flex;
  min-height: 100vh;
}

/* Sidebar Navigation */
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

.main-wrap {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

/* Mobile top bar */
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
.topbar-brand {
  display: flex;
  align-items: center;
  gap: var(--Grid-8);
  font-weight: 700;
  font-size: var(--font-size-md);
}
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

/* ==========================================================================
   PAGE HEADER
   ========================================================================== */
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
  flex-shrink: 0;
}
.page-header p {
  margin: calc(var(--Grid-8) / 2) 0 0;
  color: var(--color-black-1000);
  opacity: .7;
  font-size: var(--font-size-sm);
}

/* ==========================================================================
   NOTIFICATION BELL — H1 Visibility of System Status
   ========================================================================== */
.header-actions {
  display: flex;
  align-items: center;
  gap: var(--space-md);
}
.notif-wrap { position: relative; }
.notif-bell {
  position: relative;
  width: 40px; height: 40px;
  border: 1px solid var(--color-gray-400);
  background: var(--color-surface);
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  font-size: var(--font-size-md);
}
.notif-bell:hover { border-color: var(--color-primary); }
.notif-badge {
  position: absolute; top: -6px; right: -6px;
  min-width: 18px; height: 18px; padding: 0 4px;
  border-radius: var(--chip-radius);
  background: var(--color-danger);
  color: var(--color-black-1000);
  font-size: 11px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; line-height: 1;
}
.notif-badge[hidden] { display: none; }
.notif-panel {
  position: absolute; top: calc(100% + var(--Grid-8)); right: 0;
  width: 340px; max-width: calc(100vw - var(--space-md) * 2);
  background: var(--color-surface);
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  box-shadow: 0 8px 24px rgba(0,0,0,.12);
  z-index: 35; overflow: hidden;
}
.notif-panel[hidden] { display: none; }
.notif-panel-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: var(--space-md); border-bottom: 1px solid var(--color-gray-400);
}
.notif-panel-head h3 { font-size: var(--font-size-md); font-weight: 700; }
.notif-mark-all {
  border: none; background: transparent; color: var(--color-primary);
  font-size: var(--font-size-sm); font-weight: 700;
}
.notif-list { max-height: 340px; overflow-y: auto; list-style: none; margin: 0; padding: 0; }
.notif-item {
  display: flex; gap: var(--Grid-8); align-items: flex-start;
  padding: var(--space-md); border-bottom: 1px solid var(--color-background);
}
.notif-item:last-child { border-bottom: none; }
.notif-icon {
  width: 32px; height: 32px; border-radius: var(--radius-md); flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: var(--font-size-sm);
}
.notif-item--danger .notif-icon { background: var(--tint-danger); }
.notif-item--success .notif-icon { background: var(--tint-success); }
.notif-item--info .notif-icon { background: var(--tint-primary); }
.notif-title { font-size: var(--font-size-sm); font-weight: 700; }
.notif-desc { font-size: var(--font-size-sm); opacity: .7; margin-top: 2px; }
.notif-time { font-size: 11px; opacity: .5; margin-top: 4px; }

/* ==========================================================================
   BUTTONS
   ========================================================================== */
.btn {
  display: inline-flex;
  align-items: center;
  gap: var(--Grid-8);
  border-radius: var(--button-radius);
  border: 1px solid transparent;
  padding: var(--Grid-8) var(--space-md);
  font-size: var(--font-size-sm);
  font-weight: 700;
  white-space: nowrap;
  line-height: 1;
  transition: all .15s ease;
}
.btn svg { width: var(--space-16); height: var(--space-16); flex-shrink: 0; }
.btn-primary {
  background: var(--button-primary-bg);
  color: var(--button-primary-text);
}
.btn-primary:hover { filter: brightness(0.95); }
.btn-secondary {
  background: var(--color-surface);
  color: var(--color-black-1000);
  border-color: var(--input-border);
}
.btn-secondary:hover { background: var(--color-background); }
.btn-danger {
  background: var(--color-danger);
  color: var(--color-black-1000);
}
.btn-danger:hover { filter: brightness(0.95); }
.btn-block { width: 100%; justify-content: center; }
.btn-sm { padding: calc(var(--Grid-8) / 2) var(--Grid-8); font-size: var(--font-size-sm); }
.btn:disabled {
  background: var(--color-gray-400);
  color: var(--color-black-1000);
  opacity: .5;
  cursor: not-allowed;
}

.icon-btn {
  width: 36px; height: 36px;
  display: inline-flex; align-items: center; justify-content: center;
  border-radius: var(--radius-md);
  border: 1px solid var(--input-border);
  background: var(--color-surface);
  transition: background .15s;
}
.icon-btn:hover { background: var(--color-background); }
.icon-btn svg { width: var(--space-16); height: var(--space-16); }

/* ==========================================================================
   TOOLBAR — Search + Filter
   ========================================================================== */
.toolbar {
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
  margin-bottom: var(--space-lg);
}
.search-row {
  display: flex;
  gap: var(--space-md);
  flex-wrap: wrap;
}
.search-field {
  position: relative;
  flex: 1;
  min-width: 260px;
}
.search-field svg.search-icon {
  position: absolute;
  left: var(--space-md);
  top: 50%;
  transform: translateY(-50%);
  width: var(--font-size-md);
  height: var(--font-size-md);
  color: var(--color-gray-400);
  pointer-events: none;
}
.search-field input {
  width: 100%;
  padding: var(--Grid-8) var(--input-padding) var(--Grid-8) 48px;
  border: 1px solid var(--input-border);
  border-radius: var(--radius-md);
  font-size: var(--font-size-md);
  background: var(--color-surface);
  outline: none;
}
.search-field input:focus {
  border-color: var(--input-focus);
  box-shadow: 0 0 0 1px var(--input-focus);
}
.search-clear-btn {
  position: absolute;
  right: var(--Grid-8);
  top: 50%;
  transform: translateY(-50%);
  width: 32px; height: 32px;
  border-radius: var(--radius-md);
  border: none;
  background: transparent;
  display: none;
  align-items: center;
  justify-content: center;
  color: var(--color-black-1000);
  opacity: .6;
}
.search-clear-btn.visible { display: flex; }
.search-clear-btn:hover { background: var(--color-background); }
.search-spinner {
  position: absolute;
  right: var(--space-md);
  top: 50%;
  transform: translateY(-50%);
  width: 20px; height: 20px;
  display: none;
}
.search-spinner.visible { display: block; }

.filter-chip-row {
  display: flex;
  gap: var(--Grid-8);
  flex-wrap: wrap;
  align-items: center;
}
.chip {
  border: none;
  border-radius: var(--chip-radius);
  background: var(--chip-bg-default);
  color: var(--color-black-1000);
  padding: 6px var(--space-md);
  font-size: var(--font-size-sm);
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all .15s;
}
.chip[aria-pressed="true"] {
  background: var(--chip-bg-active);
  color: var(--button-primary-text);
}
.chip:hover { filter: brightness(0.96); }
.filter-reset {
  font-size: var(--font-size-sm);
  color: var(--color-primary);
  background: none;
  border: none;
  text-decoration: underline;
  padding: 6px var(--Grid-8);
}
.result-count {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .7;
}

/* ==========================================================================
   PET GRID / CARDS
   ========================================================================== */
.pet-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: var(--space-md);
}
.pet-card {
  border: 1px solid var(--color-gray-400);
  border-top: 4px solid var(--color-primary);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
  background: var(--color-surface);
  transition: box-shadow .15s ease, border-color .15s ease, transform .15s ease;
}
.pet-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 4px 14px rgba(0,0,0,0.06);
  transform: translateY(-2px);
}
.pet-card-top {
  display: flex;
  gap: var(--space-md);
  align-items: flex-start;
}
.pet-photo,
.pet-photo-placeholder {
  width: var(--thumbnail-size);
  height: var(--thumbnail-size);
  border-radius: var(--thumbnail-radius);
  flex-shrink: 0;
  object-fit: cover;
}
.pet-photo-placeholder {
  background: var(--tint-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary);
  font-size: 22px;
}
.pet-id-info { min-width: 0; flex: 1; }
.pet-name {
  font-size: var(--font-size-md);
  font-weight: 700;
  margin: 0 0 2px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.pet-code {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .7;
}
.pet-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 6px;
}
.meta-tag {
  font-size: var(--font-size-sm);
  border: 1px solid var(--color-gray-400);
  border-radius: var(--chip-radius);
  padding: 2px var(--Grid-8);
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.meta-tag-type {
  background: var(--tint-primary);
  border-color: transparent;
  color: var(--color-primary);
  font-weight: 600;
}
.meta-tag-gender {
  background: var(--color-background);
  border-color: transparent;
  color: var(--color-black-1000);
}
.history-pill {
  font-size: var(--font-size-sm);
  border-radius: var(--chip-radius);
  padding: 4px var(--Grid-8);
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  border: 1px solid transparent;
  width: fit-content;
}
.history-pill svg { width: 14px; height: 14px; flex-shrink: 0; }
.history-pill.has-history {
  background: var(--tint-success);
  color: var(--color-black-1000);
}
.history-pill.has-history svg { color: var(--color-success); }
.history-pill.no-history {
  background: var(--color-gray-400);
  color: var(--color-black-1000);
  opacity: .7;
}
.history-pill.no-history svg { color: var(--color-black-1000); opacity: .6; }

.pet-owner-row {
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  display: flex;
  align-items: center;
  gap: 6px;
}
.pet-owner-row svg { width: 14px; height: 14px; flex-shrink: 0; color: var(--color-black-1000); opacity: .6; }
.pet-card-actions {
  display: flex;
  gap: var(--Grid-8);
  margin-top: auto;
}
.pet-card-actions .btn { flex: 1; justify-content: center; }

/* ==========================================================================
   STATES — Loading / Empty / Error
   ========================================================================== */
.state-block {
  grid-column: 1 / -1;
  text-align: center;
  padding: 64px var(--space-md);
  border: 1px dashed var(--color-gray-400);
  border-radius: var(--radius-md);
  background: var(--color-surface);
}
.state-block svg { width: 44px; height: 44px; color: var(--color-gray-400); margin-bottom: var(--Grid-8); }
.state-title { font-size: var(--font-size-md); font-weight: 700; margin-bottom: 4px; }
.state-desc { font-size: var(--font-size-sm); color: var(--color-black-1000); opacity: .7; max-width: 440px; margin: 0 auto var(--space-md); }
.state-actions { display: flex; gap: var(--Grid-8); justify-content: center; flex-wrap: wrap; }

.skeleton-card {
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  background: var(--color-surface);
}
.skeleton-row { display: flex; gap: var(--space-md); }
.skeleton-box {
  background: linear-gradient(90deg, var(--color-gray-400) 25%, var(--tint-background-strong) 37%, var(--color-gray-400) 63%);
  background-size: 400% 100%;
  animation: skeleton-shine 1.4s ease infinite;
  border-radius: var(--radius-md);
}
@keyframes skeleton-shine {
  0% { background-position: 100% 50%; }
  100% { background-position: 0 50%; }
}
.skeleton-photo { width: var(--thumbnail-size); height: var(--thumbnail-size); flex-shrink: 0; }
.skeleton-lines { flex: 1; display: flex; flex-direction: column; gap: var(--Grid-8); }
.skeleton-lines div:nth-child(1) { height: var(--space-16); width: 60%; }
.skeleton-lines div:nth-child(2) { height: 12px; width: 40%; }
.skeleton-actions { display: flex; gap: var(--Grid-8); margin-top: var(--space-md); }
.skeleton-actions div { height: 36px; flex: 1; }

/* ==========================================================================
   DRAWER — Detail / History (Slide from right)
   ========================================================================== */
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.45);
  opacity: 0;
  pointer-events: none;
  transition: opacity .2s ease;
  z-index: 50;
}
.overlay.open { opacity: 1; pointer-events: auto; }

.drawer {
  position: fixed;
  top: 0;
  right: 0;
  height: 100%;
  width: 440px;
  max-width: 94vw;
  background: var(--color-surface);
  box-sizing: border-box;
  transform: translateX(100%);
  transition: transform .25s ease;
  z-index: 60;
  display: flex;
  flex-direction: column;
  border-left: 1px solid var(--color-gray-400);
  box-shadow: -4px 0 24px rgba(0,0,0,0.12);
}
.drawer.open { transform: translateX(0); }
.drawer-header {
  padding: var(--space-md);
  border-bottom: 1px solid var(--color-gray-400);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--Grid-8);
}
.drawer-header h2 { font-size: var(--font-size-md); font-weight: 700; }
.drawer-back {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--color-primary);
  background: none;
  border: none;
  padding: 4px;
}
.drawer-body {
  padding: var(--space-md);
  overflow-y: auto;
  flex: 1;
}
.drawer-footer {
  padding: var(--space-md);
  border-top: 1px solid var(--color-gray-400);
  display: flex;
  gap: var(--Grid-8);
}

.pet-context {
  display: flex;
  gap: var(--space-md);
  align-items: center;
  padding: var(--space-md);
  background: var(--color-background);
  border-radius: var(--radius-md);
  margin-bottom: var(--space-md);
}
.pet-context .pet-photo,
.pet-context .pet-photo-placeholder {
  width: 60px; height: 60px;
  font-size: 28px;
}
.pet-context-name { font-size: var(--font-size-md); font-weight: 700; margin: 0; }
.pet-context-sub { font-size: var(--font-size-sm); color: var(--color-black-1000); opacity: .7; margin-top: 2px; }

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-md);
  margin-bottom: var(--space-md);
}
.detail-field label {
  display: block;
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
  opacity: .7;
  margin-bottom: 2px;
}
.detail-field span {
  font-size: var(--font-size-md);
  font-weight: 600;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: var(--Grid-8);
}
.history-item {
  border: 1px solid var(--color-gray-400);
  border-radius: var(--radius-md);
  padding: var(--space-md);
  background: var(--color-surface);
}
.history-item-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}
.history-date { font-weight: 700; font-size: var(--font-size-sm); color: var(--color-primary); }
.history-vet { font-size: var(--font-size-sm); color: var(--color-black-1000); opacity: .7; }
.history-diagnosis { font-size: var(--font-size-md); font-weight: 600; margin: 2px 0; }
.history-note { font-size: var(--font-size-sm); color: var(--color-black-1000); opacity: .75; margin: 2px 0 0; }

/* ==========================================================================
   MODAL — เพิ่ม/แก้ไข, ยืนยันการลบ
   ========================================================================== */
.modal {
  position: fixed;
  inset: 0;
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 70;
  padding: var(--space-md);
}
.modal.open { display: flex; }
.modal-panel {
  background: var(--color-surface);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-gray-400);
  width: 500px;
  max-width: 100%;
  max-height: 92vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 16px 40px rgba(0,0,0,0.18);
}
.history-modal-panel {
  width: 640px;
  max-width: 95vw;
  max-height: 90vh;
}
.modal-header {
  padding: var(--space-md);
  border-bottom: 1px solid var(--color-gray-400);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.modal-header h2 { font-size: var(--font-size-md); font-weight: 700; }
.modal-body { padding: var(--space-md); overflow-y: auto; }
.modal-footer {
  padding: var(--space-md);
  border-top: 1px solid var(--color-gray-400);
  display: flex;
  justify-content: flex-end;
  gap: var(--Grid-8);
}
.form-field { margin-bottom: var(--space-md); }
.form-field label {
  display: block;
  font-size: var(--font-size-sm);
  font-weight: 700;
  margin-bottom: calc(var(--Grid-8) / 2);
}
.form-field input, .form-field select {
  width: 100%;
  padding: var(--Grid-8) var(--space-md);
  border: 1px solid var(--input-border);
  border-radius: var(--radius-md);
  font-size: var(--font-size-md);
  background: var(--color-surface);
}
.form-field input:focus, .form-field select:focus {
  outline: none;
  border-color: var(--input-focus);
  box-shadow: 0 0 0 1px var(--input-focus);
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md); }
.form-hint { font-size: var(--font-size-sm); color: var(--color-black-1000); opacity: .7; margin-top: 4px; display: flex; justify-content: space-between; }
.form-error { font-size: var(--font-size-sm); color: var(--color-danger); margin-top: 4px; display: none; }

.confirm-body {
  display: flex;
  gap: var(--space-md);
  align-items: flex-start;
}
.confirm-body svg { width: 32px; height: 32px; color: var(--color-danger); flex-shrink: 0; }
.confirm-body p { margin: calc(var(--Grid-8) / 2) 0 0; font-size: var(--font-size-sm); color: var(--color-black-1000); line-height: 1.5; }
.cascade-alert {
  margin-top: var(--Grid-8);
  padding: var(--Grid-8) var(--space-md);
  background-color: var(--tint-danger);
  border-left: 4px solid var(--color-danger);
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  color: var(--color-black-1000);
}

/* ==========================================================================
   TOAST
   ========================================================================== */
.toast {
  position: fixed;
  bottom: var(--space-lg);
  left: 50%;
  transform: translateX(-50%) translateY(20px);
  background: var(--color-surface);
  border: 1px solid var(--color-gray-400);
  border-left: 6px solid var(--color-success);
  border-radius: var(--radius-md);
  padding: var(--Grid-8) var(--space-md);
  display: flex;
  align-items: center;
  gap: var(--Grid-8);
  z-index: 80;
  opacity: 0;
  pointer-events: none;
  transition: opacity .2s ease, transform .2s ease;
  font-size: var(--font-size-sm);
  font-weight: 600;
  box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}
.toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
.toast svg { width: 18px; height: 18px; color: var(--color-success); flex-shrink: 0; }

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */
@media (max-width: 1024px) {
  .pet-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .sidebar { display: none; }
  .topbar { display: flex; }
  main { padding: var(--space-md); }
  .pet-grid { grid-template-columns: 1fr; }
  .detail-grid { grid-template-columns: 1fr; }
  .form-row { grid-template-columns: 1fr; }
  .drawer { width: 100%; max-width: 100%; }
  .page-header { flex-direction: column; align-items: stretch; }
  .page-header .btn { width: 100%; justify-content: center; }
}
.sidebar.mobile-open {
  display: flex;
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 90;
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
          <li><a class="nav-link" href="pet_list.php" aria-current="page"><span class="nav-icon">🐾</span><span>จัดการสัตว์เลี้ยง</span></a></li>
          <li><a class="nav-link" href="appointment_form.php"><span class="nav-icon">📅</span><span>สร้างนัดหมาย</span></a></li>
          <li><a class="nav-link" href="treatment_form.php"><span class="nav-icon">🩺</span><span>ห้องตรวจแพทย์</span></a></li>
          <li><a class="nav-link" href="billing_list.php"><span class="nav-icon">💵</span><span>การเงิน/ใบเสร็จ</span></a></li>
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
      <button class="hamburger-btn" id="hamburgerBtn" aria-label="เปิดเมนู" aria-expanded="false" aria-controls="sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="topbar-brand">จัดการสัตว์เลี้ยง</div>
      <div style="width:40px;"></div>
    </header>

    <main>
      <!-- =================================================================
           PAGE HEADER
           ================================================================= -->
      <div class="page-header">
        <div>
          <h1>
            <span class="page-header-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M12 4c-1.4 0-2.5 1.3-2.5 3S10.6 10 12 10s2.5-1.3 2.5-3S13.4 4 12 4z"/><path d="M6.5 8.5C5.7 8.5 5 9.6 5 11s.7 2.5 1.5 2.5S8 12.4 8 11s-.7-2.5-1.5-2.5z"/><path d="M17.5 8.5c-.8 0-1.5 1.1-1.5 2.5s.7 2.5 1.5 2.5S19 12.9 19 11.5s-.7-3-1.5-3z"/><path d="M9 13.5c-2.2 0-4 1.9-4 4.2S6.8 21 9 21h6c2.2 0 4-1.3 4-3.3s-1.8-4.2-4-4.2c-1.1 0-1.7.5-3 .5s-1.9-.5-3-.5z"/></svg>
            </span>
            จัดการสัตว์เลี้ยง
          </h1>
          <p>ค้นหาสัตว์เลี้ยง ดูข้อมูล และเปิดประวัติการรักษาได้จากหน้านี้โดยตรง</p>
        </div>
        <div class="header-actions">
          <button class="btn btn-primary" id="btnAddPet">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="16" height="16"><path d="M12 5v14M5 12h14"/></svg>
            เพิ่มสัตว์เลี้ยง
          </button>

          <!-- Notification Bell Widget -->
          <div class="notif-wrap">
            <button type="button" class="notif-bell" id="notifBellBtn" aria-haspopup="true" aria-expanded="false" aria-controls="notifPanel" aria-label="การแจ้งเตือน">
              <span aria-hidden="true">🔔</span>
              <span class="notif-badge" id="notifBadge">3</span>
            </button>
            <div class="notif-panel" id="notifPanel" role="menu" hidden>
              <div class="notif-panel-head">
                <h3>การแจ้งเตือนระบบ</h3>
                <button type="button" class="notif-mark-all" id="notifMarkAllBtn">อ่านทั้งหมดแล้ว</button>
              </div>
              <ul class="notif-list" id="notifList">
                <li class="notif-item notif-item--success">
                  <span class="notif-icon" aria-hidden="true">🐾</span>
                  <div>
                    <div class="notif-title">ฐานข้อมูลพร้อมใช้งาน</div>
                    <div class="notif-desc">ดึงข้อมูลสัตว์เลี้ยงทั้งหมด <?= count($client_pets) ?> ตัวเรียบร้อยแล้ว</div>
                    <div class="notif-time">ล่าสุด</div>
                  </div>
                </li>
                <li class="notif-item notif-item--info">
                  <span class="notif-icon" aria-hidden="true">💉</span>
                  <div>
                    <div class="notif-title">กำหนดฉีดวัคซีนประจำปี</div>
                    <div class="notif-desc">มีสัตว์เลี้ยงที่ต้องติดตามวัคซีน กรุณาตรวจสอบประวัติ</div>
                    <div class="notif-time">วันนี้</div>
                  </div>
                </li>
                <li class="notif-item notif-item--danger">
                  <span class="notif-icon" aria-hidden="true">⚠️</span>
                  <div>
                    <div class="notif-title">คำเตือนข้อจำกัดระบบ</div>
                    <div class="notif-desc">การลบสัตว์เลี้ยงจะมีผลลบประวัติตามกฎ CASCADE อัตโนมัติ</div>
                    <div class="notif-time">แจ้งเตือนระบบ</div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- =================================================================
           TOOLBAR — SEARCH + FILTER CHIPS
           ================================================================= -->
      <div class="toolbar">
        <div class="search-row">
          <div class="search-field">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <label for="searchInput" class="sr-only">ค้นหาชื่อสัตว์เลี้ยง เจ้าของ หรือรหัสสัตว์</label>
            <input type="text" id="searchInput" placeholder="ค้นหาชื่อสัตว์เลี้ยง, ชื่อเจ้าของ, สายพันธุ์ หรือรหัสสัตว์..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
            <svg class="search-spinner" id="searchSpinner" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="3"><circle cx="12" cy="12" r="9" stroke-opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.7s" repeatCount="indefinite"/></path></svg>
            <button class="search-clear-btn <?= !empty($search) ? 'visible' : '' ?>" id="searchClearBtn" aria-label="ล้างคำค้นหา">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
          </div>
        </div>

        <div class="filter-chip-row" id="typeFilterRow" role="group" aria-label="กรองตามประเภทสัตว์">
          <button type="button" class="chip" data-type="all" aria-pressed="true">ทั้งหมด</button>
          <button type="button" class="chip" data-type="dog" aria-pressed="false">🐕 สุนัข</button>
          <button type="button" class="chip" data-type="cat" aria-pressed="false">🐈 แมว</button>
          <button type="button" class="chip" data-type="rabbit" aria-pressed="false">🐇 กระต่าย</button>
          <button type="button" class="chip" data-type="bird" aria-pressed="false">🐦 นก</button>
          <button type="button" class="chip" data-type="other" aria-pressed="false">🐾 อื่นๆ</button>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <span class="result-count" id="resultCount" aria-live="polite">กำลังคำนวณจำนวน...</span>
          <button class="filter-reset" id="filterResetBtn" hidden>ล้างตัวกรองทั้งหมด</button>
        </div>
      </div>

      <div class="sr-only" id="statusAnnouncer" aria-live="polite"></div>

      <!-- =================================================================
           PET GRID — CARD SYSTEM
           ================================================================= -->
      <section class="pet-grid" id="petGrid" aria-label="รายการสัตว์เลี้ยง">
        <!-- Rendered dynamically by JavaScript based on database records -->
      </section>
    </main>
  </div>
</div>

<!-- ===========================================================================
     OVERLAY
     =========================================================================== -->
<div class="overlay" id="overlay"></div>

<!-- ===========================================================================
     DETAIL MODAL (Pop-up ตรงกลางจอ)
     =========================================================================== -->
<div class="modal" id="detailModal" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
  <div class="modal-panel history-modal-panel">
    <div class="modal-header">
      <h2 id="detailModalTitle" style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:20px;">🐾</span> ข้อมูลสัตว์เลี้ยง
      </h2>
      <button type="button" class="icon-btn" id="detailCloseBtn" aria-label="ปิดหน้าต่างข้อมูลสัตว์เลี้ยง">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <div class="modal-body" id="detailModalBody" style="max-height: 72vh; overflow-y: auto;"></div>
    <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
      <button type="button" class="btn btn-secondary" id="detailEditBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
        แก้ไขข้อมูล
      </button>
      <button type="button" class="btn btn-primary" id="detailHistoryBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M9 4h9a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V6"/><path d="M9 4a1 1 0 0 0-1 1v2h4V5a1 1 0 0 0-1-1H9z"/><path d="M9 11h6M9 15h6"/></svg>
        ดูประวัติการรักษา
      </button>
    </div>
  </div>
</div>

<!-- ===========================================================================
     HISTORY MODAL (Pop-up ตรงกลางจอ)
     =========================================================================== -->
<div class="modal" id="historyModal" role="dialog" aria-modal="true" aria-labelledby="historyModalTitle">
  <div class="modal-panel history-modal-panel">
    <div class="modal-header">
      <h2 id="historyModalTitle" style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:20px;">📂</span> ประวัติการรักษา
      </h2>
      <button type="button" class="icon-btn" id="historyCloseBtn" aria-label="ปิดหน้าต่างประวัติการรักษา">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <div class="modal-body" id="historyModalBody" style="max-height: 72vh; overflow-y: auto;"></div>
    <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
      <a href="#" id="historyFullLink" class="btn btn-secondary btn-sm" target="_blank">
        🔗 ดูประวัติการรักษาทั้งหมดในระบบ
      </a>
      <button type="button" class="btn btn-primary btn-sm" id="historyDoneBtn">ปิด</button>
    </div>
  </div>
</div>

<!-- ===========================================================================
     ADD / EDIT MODAL (สะท้อน DB Constraints: pet.name varchar(20), pet_ID readonly)
     =========================================================================== -->
<div class="modal" id="petFormModal" role="dialog" aria-modal="true" aria-labelledby="petFormTitle">
  <form class="modal-panel" id="petForm">
    <input type="hidden" name="pet_ID" id="formPetId">
    <div class="modal-header">
      <h2 id="petFormTitle">เพิ่มสัตว์เลี้ยงใหม่</h2>
      <button type="button" class="icon-btn" id="petFormCloseBtn" aria-label="ปิดหน้าต่าง">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <!-- Constraint: pet.pet_ID Read-only -->
      <div class="form-field" id="idDisplayGroup" style="display:none;">
        <label for="formPetIdDisplay">รหัสระบบสัตว์เลี้ยง (ID) — ไม่สามารถแก้ไขได้</label>
        <input type="text" id="formPetIdDisplay" readonly style="background:var(--tint-background-strong); color:var(--color-black-1000); font-weight:700;">
      </div>

      <!-- Constraint: pet.name varchar(20) -->
      <div class="form-field">
        <label for="formName">ชื่อสัตว์เลี้ยง <span style="color:var(--color-danger);">*</span></label>
        <input type="text" name="name" id="formName" maxlength="20" required placeholder="ความยาวไม่เกิน 20 ตัวอักษร">
        <div class="form-hint">
          <span>ความยาวตามฐานข้อมูล: ไม่เกิน 20 ตัวอักษร</span>
          <span id="formNameCounter">0/20</span>
        </div>
        <div class="form-error" id="formNameError">กรุณาระบุชื่อสัตว์เลี้ยง (ความยาว 1-20 ตัวอักษร)</div>
      </div>

      <div class="form-row">
        <div class="form-field">
          <label for="formBreedId">สายพันธุ์ (Breed)</label>
          <select name="breed_ID" id="formBreedId">
            <option value="">-- ไม่ระบุสายพันธุ์ --</option>
            <?php foreach ($all_breeds as $b): ?>
              <option value="<?= $b['breed_ID'] ?>"><?= htmlspecialchars($b['breed_detail']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-field">
          <label for="formGender">เพศ</label>
          <select name="gender" id="formGender">
            <option value="Male">ตัวผู้</option>
            <option value="Female">ตัวเมีย</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-field">
          <label for="formDob">วันเกิด</label>
          <input type="date" name="dob" id="formDob">
          <div class="form-hint"><span>ระบบจะคำนวณอายุให้อัตโนมัติ</span></div>
        </div>
        <div class="form-field">
          <label for="formOwnerId">เจ้าของสัตว์เลี้ยง <span style="color:var(--color-danger);">*</span></label>
          <select name="owner_ID" id="formOwnerId" required>
            <option value="">-- เลือกเจ้าของ --</option>
            <?php foreach ($all_owners as $ow): ?>
              <option value="<?= $ow['owner_ID'] ?>"><?= htmlspecialchars($ow['name']) ?> (<?= htmlspecialchars($ow['phoneNumber'] ?: '-') ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="petFormCancelBtn">ยกเลิก</button>
      <button type="submit" class="btn btn-primary" id="btnSavePet">บันทึกข้อมูล</button>
    </div>
  </form>
</div>

<!-- ===========================================================================
     DELETE CONFIRM MODAL (สะท้อน H5 Error Prevention & ON DELETE CASCADE)
     =========================================================================== -->
<div class="modal" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
  <div class="modal-panel">
    <div class="modal-header">
      <h2 id="deleteModalTitle" style="color:var(--color-danger);">ยืนยันการลบข้อมูลสัตว์เลี้ยง</h2>
      <button type="button" class="icon-btn" id="deleteModalCloseBtn" aria-label="ปิดหน้าต่าง">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="confirm-body">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.9 18a2 2 0 0 0 1.7 3h16.8a2 2 0 0 0 1.7-3L14.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
        <div>
          <strong id="deleteModalPetName" style="font-size:16px;">ต้องการลบข้อมูลสัตว์เลี้ยงตัวนี้ใช่หรือไม่?</strong>
          <p>การลบข้อมูลนี้เป็นการกระทำถาวรและไม่สามารถย้อนกลับได้</p>
          <div class="cascade-alert">
            <strong>⚠️ คำเตือนผลกระทบฐานข้อมูล (ON DELETE CASCADE):</strong><br>
            ตามข้อกำหนดใน <code>DB_NOTES.md</code> หากลบสัตว์เลี้ยงตัวนี้ ประวัติการนัดหมายและการรักษาทั้งหมดที่ผูกไว้จะถูกลบออกจากระบบอย่างถาวรโดยอัตโนมัติ
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="deleteCancelBtn">ยกเลิก</button>
      <button type="button" class="btn btn-danger" id="deleteConfirmBtn">ยืนยันลบข้อมูล</button>
    </div>
  </div>
</div>

<!-- ===========================================================================
     TOAST
     =========================================================================== -->
<div class="toast" id="toast" role="status">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>
  <span id="toastMsg">บันทึกข้อมูลสำเร็จแล้ว</span>
</div>

<script>
(function(){
  "use strict";

  // ข้อมูลสัตว์เลี้ยงทั้งหมดที่ส่งมาจากฝั่ง PHP/PDO
  const PETS = <?= json_encode($client_pets, JSON_UNESCAPED_UNICODE) ?>;
  const TYPE_LABEL = { dog:'สุนัข', cat:'แมว', rabbit:'กระต่าย', bird:'นก', other:'อื่นๆ' };
  const GENDER_LABEL = { male:'ตัวผู้', female:'ตัวเมีย', other:'ไม่ระบุ' };

  const ICON_PAW = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M12 4c-1.4 0-2.5 1.3-2.5 3S10.6 10 12 10s2.5-1.3 2.5-3S13.4 4 12 4z"/><path d="M6.5 8.5C5.7 8.5 5 9.6 5 11s.7 2.5 1.5 2.5S8 12.4 8 11s-.7-2.5-1.5-2.5z"/><path d="M17.5 8.5c-.8 0-1.5 1.1-1.5 2.5s.7 2.5 1.5 2.5S19 12.9 19 11.5s-.7-3-1.5-3z"/><path d="M9 13.5c-2.2 0-4 1.9-4 4.2S6.8 21 9 21h6c2.2 0 4-1.3 4-3.3s-1.8-4.2-4-4.2c-1.1 0-1.7.5-3 .5s-1.9-.5-3-.5z"/></svg>';
  const ICON_OWNER = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>';
  const ICON_SEARCH_EMPTY = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>';
  const ICON_CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>';
  const ICON_CLIPBOARD_EMPTY = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 4h9a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V6"/><path d="M9 4a1 1 0 0 0-1 1v2h4V5a1 1 0 0 0-1-1H9z"/></svg>';

  function calcAge(dobStr, ageFallback) {
    if (dobStr && dobStr !== '0000-00-00') {
      const dob = new Date(dobStr);
      const now = new Date();
      if (!isNaN(dob.getTime())) {
        let years = now.getFullYear() - dob.getFullYear();
        let months = now.getMonth() - dob.getMonth();
        if (months < 0) { years--; months += 12; }
        if (years <= 0) return (months || 1) + ' เดือน';
        return years + ' ปี ' + months + ' เดือน';
      }
    }
    if (ageFallback) {
      if (typeof ageFallback === 'number' || /^\d+$/.test(ageFallback)) {
        return ageFallback + ' ปี';
      }
      return ageFallback;
    }
    return 'ไม่ระบุอายุ';
  }

  let state = {
    search: "<?= addslashes($search) ?>",
    type: 'all',
    loading: false
  };
  let lastFocusedEl = null;
  let currentDetailId = null;
  let currentDeleteId = null;
  let isEditMode = false;

  const els = {
    grid: document.getElementById('petGrid'),
    searchInput: document.getElementById('searchInput'),
    searchClearBtn: document.getElementById('searchClearBtn'),
    searchSpinner: document.getElementById('searchSpinner'),
    typeFilterRow: document.getElementById('typeFilterRow'),
    resultCount: document.getElementById('resultCount'),
    filterResetBtn: document.getElementById('filterResetBtn'),
    statusAnnouncer: document.getElementById('statusAnnouncer'),
    overlay: document.getElementById('overlay'),
    detailModal: document.getElementById('detailModal'),
    detailModalBody: document.getElementById('detailModalBody'),
    detailModalTitle: document.getElementById('detailModalTitle'),
    detailCloseBtn: document.getElementById('detailCloseBtn'),
    detailEditBtn: document.getElementById('detailEditBtn'),
    detailHistoryBtn: document.getElementById('detailHistoryBtn'),
    historyModal: document.getElementById('historyModal'),
    historyModalBody: document.getElementById('historyModalBody'),
    historyModalTitle: document.getElementById('historyModalTitle'),
    historyCloseBtn: document.getElementById('historyCloseBtn'),
    historyDoneBtn: document.getElementById('historyDoneBtn'),
    historyFullLink: document.getElementById('historyFullLink'),
    petFormModal: document.getElementById('petFormModal'),
    petForm: document.getElementById('petForm'),
    petFormTitle: document.getElementById('petFormTitle'),
    formPetId: document.getElementById('formPetId'),
    formPetIdDisplay: document.getElementById('formPetIdDisplay'),
    idDisplayGroup: document.getElementById('idDisplayGroup'),
    formName: document.getElementById('formName'),
    formNameCounter: document.getElementById('formNameCounter'),
    formNameError: document.getElementById('formNameError'),
    formBreedId: document.getElementById('formBreedId'),
    formGender: document.getElementById('formGender'),
    formDob: document.getElementById('formDob'),
    formOwnerId: document.getElementById('formOwnerId'),
    btnSavePet: document.getElementById('btnSavePet'),
    petFormCloseBtn: document.getElementById('petFormCloseBtn'),
    petFormCancelBtn: document.getElementById('petFormCancelBtn'),
    btnAddPet: document.getElementById('btnAddPet'),
    deleteModal: document.getElementById('deleteModal'),
    deleteModalPetName: document.getElementById('deleteModalPetName'),
    deleteModalCloseBtn: document.getElementById('deleteModalCloseBtn'),
    deleteCancelBtn: document.getElementById('deleteCancelBtn'),
    deleteConfirmBtn: document.getElementById('deleteConfirmBtn'),
    toast: document.getElementById('toast'),
    toastMsg: document.getElementById('toastMsg'),
    sidebar: document.getElementById('sidebar'),
    hamburgerBtn: document.getElementById('hamburgerBtn'),
    notifBellBtn: document.getElementById('notifBellBtn'),
    notifPanel: document.getElementById('notifPanel'),
    notifMarkAllBtn: document.getElementById('notifMarkAllBtn'),
    notifBadge: document.getElementById('notifBadge'),
  };

  /* =========================================================================
     RENDER: PET CARD
     ========================================================================= */
  function renderCard(pet) {
    const hasHist = pet.history && pet.history.length > 0;
    return `
    <article class="pet-card" id="card-${pet.id}" data-id="${pet.id}">
      <div class="pet-card-top">
        <div class="pet-photo-placeholder" role="img" aria-label="รูปสัตว์เลี้ยงชื่อ ${pet.name}">
          ${ICON_PAW}
        </div>
        <div class="pet-id-info">
          <p class="pet-name">${escapeHtml(pet.name)}</p>
          <p class="pet-code">${escapeHtml(pet.code)} · ${escapeHtml(pet.breed)}</p>
          <div class="pet-meta">
            <span class="meta-tag meta-tag-type">${TYPE_LABEL[pet.type] || 'อื่นๆ'}</span>
            <span class="meta-tag meta-tag-gender">${GENDER_LABEL[pet.gender] || pet.gender}</span>
            <span class="meta-tag">${calcAge(pet.dob, pet.age)}</span>
          </div>
        </div>
      </div>
      <div class="pet-owner-row">
        ${ICON_OWNER}
        <span>${escapeHtml(pet.owner)}</span>
      </div>
      ${hasHist
        ? `<span class="history-pill has-history">${ICON_CHECK} มีประวัติการรักษา ${pet.history.length} รายการ</span>`
        : `<span class="history-pill no-history">${ICON_CLIPBOARD_EMPTY} ยังไม่มีประวัติการรักษา</span>`}
      <div class="pet-card-actions">
        <button type="button" class="btn btn-secondary" data-action="detail" data-id="${pet.id}">รายละเอียด</button>
        <button type="button" class="btn btn-primary" data-action="history" data-id="${pet.id}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M9 4h9a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V6"/><path d="M9 4a1 1 0 0 0-1 1v2h4V5a1 1 0 0 0-1-1H9z"/><path d="M9 11h6M9 15h6"/></svg>
          ประวัติการรักษา
        </button>
      </div>
    </article>`;
  }

  function renderSkeleton() {
    let html = '';
    for (let i = 0; i < 6; i++) {
      html += `
      <div class="skeleton-card" aria-hidden="true">
        <div class="skeleton-row">
          <div class="skeleton-box skeleton-photo"></div>
          <div class="skeleton-lines"><div class="skeleton-box"></div><div class="skeleton-box"></div></div>
        </div>
        <div class="skeleton-actions"><div class="skeleton-box"></div><div class="skeleton-box"></div></div>
      </div>`;
    }
    return html;
  }

  function renderEmpty() {
    return `
    <div class="state-block">
      ${ICON_SEARCH_EMPTY}
      <p class="state-title">ไม่พบสัตว์เลี้ยงที่ค้นหา</p>
      <p class="state-desc">ลองตรวจสอบคำสะกด หรือกดปุ่มด้านล่างเพื่อล้างตัวกรองและแสดงรายการทั้งหมด หากยังไม่มีในระบบ สามารถเพิ่มสัตว์เลี้ยงใหม่ได้ทันที</p>
      <div class="state-actions">
        <button type="button" class="btn btn-secondary" id="emptyClearBtn">ล้างคำค้นหาและตัวกรอง</button>
        <button type="button" class="btn btn-primary" id="emptyAddBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="16" height="16"><path d="M12 5v14M5 12h14"/></svg>
          เพิ่มสัตว์เลี้ยงใหม่
        </button>
      </div>
    </div>`;
  }

  function getFiltered() {
    const q = state.search.trim().toLowerCase();
    return PETS.filter(p => {
      const matchType = state.type === 'all' || p.type === state.type;
      const matchSearch = !q ||
        p.name.toLowerCase().includes(q) ||
        p.owner.toLowerCase().includes(q) ||
        p.code.toLowerCase().includes(q) ||
        p.breed.toLowerCase().includes(q);
      return matchType && matchSearch;
    });
  }

  function renderList() {
    if (state.loading) {
      els.grid.innerHTML = renderSkeleton();
      els.resultCount.textContent = 'กำลังค้นหา...';
      return;
    }

    const list = getFiltered();
    els.resultCount.textContent = `พบ ${list.length} รายการ (จากทั้งหมด ${PETS.length} รายการ)`;
    els.filterResetBtn.hidden = !(state.search || state.type !== 'all');

    if (list.length === 0) {
      els.grid.innerHTML = renderEmpty();
      document.getElementById('emptyClearBtn').addEventListener('click', resetAllFilters);
      document.getElementById('emptyAddBtn').addEventListener('click', openAddModal);
      return;
    }

    els.grid.innerHTML = list.map(renderCard).join('');
    els.grid.querySelectorAll('[data-action="detail"]').forEach(btn => {
      btn.addEventListener('click', () => openDetail(Number(btn.dataset.id)));
    });
    els.grid.querySelectorAll('[data-action="history"]').forEach(btn => {
      btn.addEventListener('click', () => openHistory(Number(btn.dataset.id)));
    });
    els.statusAnnouncer.textContent = `พบสัตว์เลี้ยง ${list.length} รายการ`;
  }

  function resetAllFilters() {
    state.search = '';
    state.type = 'all';
    els.searchInput.value = '';
    els.searchClearBtn.classList.remove('visible');
    els.typeFilterRow.querySelectorAll('.chip').forEach(c => {
      c.setAttribute('aria-pressed', c.dataset.type === 'all');
    });
    renderList();
  }
  els.filterResetBtn.addEventListener('click', resetAllFilters);

  // Chips click handler
  els.typeFilterRow.querySelectorAll('.chip').forEach(btn => {
    btn.addEventListener('click', () => {
      state.type = btn.dataset.type;
      els.typeFilterRow.querySelectorAll('.chip').forEach(c => c.setAttribute('aria-pressed', 'false'));
      btn.setAttribute('aria-pressed', 'true');
      renderList();
    });
  });

  // Search input handler
  let searchTimer = null;
  els.searchInput.addEventListener('input', () => {
    state.search = els.searchInput.value;
    els.searchClearBtn.classList.toggle('visible', !!state.search);
    clearTimeout(searchTimer);
    els.searchSpinner.classList.add('visible');
    searchTimer = setTimeout(() => {
      els.searchSpinner.classList.remove('visible');
      renderList();
    }, 280);
  });
  els.searchClearBtn.addEventListener('click', () => {
    els.searchInput.value = '';
    state.search = '';
    els.searchClearBtn.classList.remove('visible');
    renderList();
    els.searchInput.focus();
  });

  /* =========================================================================
     DETAIL MODAL (Pop-up ตรงกลางจอ)
     ========================================================================= */
  function openDetail(petId) {
    const pet = PETS.find(p => p.id === petId);
    if (!pet) return;
    currentDetailId = petId;
    lastFocusedEl = document.activeElement;

    els.detailModalTitle.innerHTML = `<span style="font-size:20px;">🐾</span> ข้อมูลสัตว์เลี้ยง: ${escapeHtml(pet.name)}`;

    els.detailModalBody.innerHTML = `
      <div class="pet-context" style="margin-bottom:16px;">
        <div class="pet-photo-placeholder" role="img">${ICON_PAW}</div>
        <div>
          <p class="pet-context-name">${escapeHtml(pet.name)}</p>
          <p class="pet-context-sub">${escapeHtml(pet.code)} · เจ้าของ: ${escapeHtml(pet.owner)}</p>
        </div>
      </div>
      <div class="detail-grid">
        <div class="detail-field"><label>ประเภทสัตว์</label><span>${TYPE_LABEL[pet.type] || 'อื่นๆ'}</span></div>
        <div class="detail-field"><label>สายพันธุ์</label><span>${escapeHtml(pet.breed)}</span></div>
        <div class="detail-field"><label>เพศ</label><span>${GENDER_LABEL[pet.gender] || pet.gender}</span></div>
        <div class="detail-field"><label>อายุ</label><span>${calcAge(pet.dob, pet.age)}</span></div>
        <div class="detail-field"><label>วันเกิด</label><span>${pet.dob || 'ไม่ระบุ'}</span></div>
        <div class="detail-field"><label>รหัสสัตว์เลี้ยง</label><span>#${pet.id} (${escapeHtml(pet.code)})</span></div>
        <div class="detail-field"><label>ชื่อเจ้าของ</label><span>${escapeHtml(pet.owner)}</span></div>
        <div class="detail-field"><label>เบอร์ติดต่อเจ้าของ</label><span>${escapeHtml(pet.ownerPhone)}</span></div>
      </div>
      <button type="button" class="btn btn-danger btn-block" id="detailDeleteBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
        ลบข้อมูลสัตว์เลี้ยงตัวนี้ (ON DELETE CASCADE)
      </button>
    `;

    document.getElementById('detailDeleteBtn').addEventListener('click', () => {
      openDeleteConfirm(pet);
    });

    closeModals();
    openModal(els.detailModal);
  }

  els.detailCloseBtn.addEventListener('click', closeModals);
  els.detailEditBtn.addEventListener('click', () => {
    const pet = PETS.find(p => p.id === currentDetailId);
    if (pet) openEditModal(pet);
  });
  els.detailHistoryBtn.addEventListener('click', () => {
    if (currentDetailId) openHistory(currentDetailId);
  });

  /* =========================================================================
     HISTORY MODAL (Pop-up ตรงกลางจอ)
     ========================================================================= */
  function openHistory(petId) {
    const pet = PETS.find(p => p.id === petId);
    if (!pet) return;
    currentDetailId = petId;
    lastFocusedEl = document.activeElement;

    els.historyModalTitle.innerHTML = `<span style="font-size:20px;">📂</span> ประวัติการรักษา: ${escapeHtml(pet.name)}`;
    els.historyFullLink.href = `treatment_history.php?search=${encodeURIComponent(pet.name)}`;

    els.historyModalBody.innerHTML = `
      <div class="pet-context" style="margin-bottom:16px;">
        <div class="pet-photo-placeholder" role="img">${ICON_PAW}</div>
        <div>
          <p class="pet-context-name">${escapeHtml(pet.name)}</p>
          <p class="pet-context-sub">${escapeHtml(pet.code)} · เจ้าของ: ${escapeHtml(pet.owner)} · สายพันธุ์: ${escapeHtml(pet.breed)}</p>
        </div>
      </div>
      <div id="historyContentZone"></div>
    `;

    const zone = document.getElementById('historyContentZone');
    if (!pet.history || pet.history.length === 0) {
      zone.innerHTML = `
      <div class="state-block" style="padding:32px 16px; border:1px dashed var(--color-gray-400); border-radius:var(--radius-md);">
        ${ICON_CLIPBOARD_EMPTY}
        <p class="state-title">ยังไม่มีประวัติการรักษา</p>
        <p class="state-desc">สัตว์เลี้ยงตัวนี้ยังไม่เคยมีบันทึกการรักษาในระบบ สามารถสร้างนัดหมายหรือห้องตรวจแพทย์ได้</p>
        <div class="state-actions">
          <a href="appointment_form.php?pet_id=${pet.id}" class="btn btn-primary btn-sm">+ สร้างนัดหมายแพทย์</a>
        </div>
      </div>`;
    } else {
      zone.innerHTML = `
      <div class="history-list">
        ${pet.history.map(h => `
          <div class="history-item" style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
            <div class="history-item-top">
              <span class="history-date">📅 ${escapeHtml(h.date)}</span>
              <span class="history-vet">👨‍⚕️ ${escapeHtml(h.vet)}</span>
            </div>
            <p class="history-diagnosis" style="color:var(--color-primary); font-weight:700;">🩺 ${escapeHtml(h.diagnosis)}</p>
            ${h.note && h.note !== '-' ? `<p class="history-note"><strong>บันทึกเพิ่มเติม:</strong> ${escapeHtml(h.note)}</p>` : ''}
          </div>
        `).join('')}
      </div>`;
    }

    closeDrawers();
    openModal(els.historyModal);
  }

  els.historyCloseBtn.addEventListener('click', closeModals);
  els.historyDoneBtn.addEventListener('click', closeModals);

  function showDrawer(drawer) {
    document.querySelectorAll('.drawer').forEach(d => { d.hidden = true; d.classList.remove('open'); });
    drawer.hidden = false;
    void drawer.offsetWidth;
    drawer.classList.add('open');
    els.overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawers() {
    document.querySelectorAll('.drawer.open').forEach(d => {
      d.classList.remove('open');
      setTimeout(() => { d.hidden = true; }, 200);
    });
    els.overlay.classList.remove('open');
    document.body.style.overflow = '';
    if (lastFocusedEl) lastFocusedEl.focus();
  }

  /* =========================================================================
     ADD / EDIT MODAL (Enforces DB Constraints: pet.name varchar(20), pet_ID readonly)
     ========================================================================= */
  function openAddModal() {
    isEditMode = false;
    els.petFormTitle.textContent = 'เพิ่มสัตว์เลี้ยงใหม่';
    els.petForm.reset();
    els.formPetId.value = '';
    els.idDisplayGroup.style.display = 'none';
    els.formNameError.style.display = 'none';
    els.btnSavePet.disabled = false;
    updateNameCounter('');
    openModal(els.petFormModal);
  }

  function openEditModal(pet) {
    isEditMode = true;
    els.petFormTitle.textContent = `แก้ไขข้อมูล: ${pet.name}`;
    els.formPetId.value = pet.id;
    els.formPetIdDisplay.value = `#${pet.id} (${pet.code})`;
    els.idDisplayGroup.style.display = 'block';
    els.formName.value = pet.name;
    els.formBreedId.value = pet.breed_id || '';
    els.formGender.value = pet.gender === 'female' ? 'Female' : 'Male';
    els.formDob.value = pet.dob || '';
    els.formOwnerId.value = pet.owner_id || '';
    els.formNameError.style.display = 'none';
    els.btnSavePet.disabled = false;
    updateNameCounter(pet.name);

    closeDrawers();
    openModal(els.petFormModal);
  }

  function updateNameCounter(val) {
    const len = val.trim().length;
    els.formNameCounter.textContent = `${len}/20`;
    if (len === 0) {
      els.formNameError.textContent = 'กรุณาระบุชื่อสัตว์เลี้ยง ไม่สามารถเว้นว่างได้';
      els.formNameError.style.display = 'block';
      els.btnSavePet.disabled = true;
    } else if (len > 20) {
      els.formNameError.textContent = 'ชื่อสัตว์เลี้ยงเกิน 20 ตัวอักษร (ข้อจำกัดฐานข้อมูล)';
      els.formNameError.style.display = 'block';
      els.btnSavePet.disabled = true;
    } else {
      els.formNameError.style.display = 'none';
      els.btnSavePet.disabled = false;
    }
  }

  els.formName.addEventListener('input', () => updateNameCounter(els.formName.value));

  els.btnAddPet.addEventListener('click', openAddModal);
  els.petFormCloseBtn.addEventListener('click', closeModals);
  els.petFormCancelBtn.addEventListener('click', closeModals);

  // Form Submit Handler
  els.petForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const nameVal = els.formName.value.trim();
    if (nameVal.length === 0 || nameVal.length > 20) {
      updateNameCounter(nameVal);
      return;
    }

    const formData = new FormData(els.petForm);

    if (isEditMode) {
      // อัปเดตข้อมูลสัตว์เลี้ยงเดิมผ่าน API
      fetch('../api/update_pet.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          const pet = PETS.find(p => p.id === Number(els.formPetId.value));
          if (pet) {
            pet.name = nameVal;
            pet.dob = els.formDob.value;
            pet.gender = els.formGender.value.toLowerCase() === 'female' ? 'female' : 'male';
            if (els.formBreedId.value) {
              const opt = els.formBreedId.options[els.formBreedId.selectedIndex];
              pet.breed = opt ? opt.text : pet.breed;
              pet.breed_id = Number(els.formBreedId.value);
            }
            if (els.formOwnerId.value) {
              const oOpt = els.formOwnerId.options[els.formOwnerId.selectedIndex];
              pet.owner = oOpt ? oOpt.text.split('(')[0].trim() : pet.owner;
              pet.owner_id = Number(els.formOwnerId.value);
            }
          }
          showToast('บันทึกการแก้ไขข้อมูลสัตว์เลี้ยงสำเร็จแล้ว');
          closeModals();
          renderList();
        } else {
          alert('เกิดข้อผิดพลาด: ' + (res.message || 'ไม่สามารถบันทึกได้'));
        }
      })
      .catch(err => {
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + err.message);
      });
    } else {
      // ลงทะเบียนสัตว์เลี้ยงใหม่ผ่าน API
      fetch('../api/register_pet.php', {
        method: 'POST',
        body: formData
      })
      .then(() => {
        showToast('ลงทะเบียนสัตว์เลี้ยงใหม่สำเร็จแล้ว');
        closeModals();
        setTimeout(() => location.reload(), 700);
      })
      .catch(err => {
        alert('เกิดข้อผิดพลาดในการบันทึก: ' + err.message);
      });
    }
  });

  /* =========================================================================
     DELETE CONFIRM (ON DELETE CASCADE)
     ========================================================================= */
  function openDeleteConfirm(pet) {
    currentDeleteId = pet.id;
    els.deleteModalPetName.textContent = `ต้องการลบข้อมูล "${pet.name}" (รหัส #${pet.id}) ใช่หรือไม่?`;
    closeDrawers();
    openModal(els.deleteModal);
  }

  els.deleteModalCloseBtn.addEventListener('click', closeModals);
  els.deleteCancelBtn.addEventListener('click', closeModals);

  els.deleteConfirmBtn.addEventListener('click', () => {
    if (!currentDeleteId) return;
    const petId = currentDeleteId;
    const pet = PETS.find(p => p.id === petId);
    const petName = pet ? pet.name : 'สัตว์เลี้ยง';

    const formData = new FormData();
    formData.append('id', petId);

    fetch('../api/delete_pet.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(res => {
      if (res.status === 'success') {
        const idx = PETS.findIndex(p => p.id === petId);
        if (idx > -1) PETS.splice(idx, 1);
        showToast(`ลบข้อมูล "${petName}" และประวัติที่เกี่ยวข้องเรียบร้อยแล้ว`);
        closeModals();
        renderList();
      } else {
        alert('ไม่สามารถลบข้อมูลได้: ' + (res.message || 'ข้อผิดพลาดระบบ'));
      }
    })
    .catch(err => {
      alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + err.message);
    });
  });

  function openModal(modal) {
    modal.classList.add('open');
    els.overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    const firstInput = modal.querySelector('input:not([readonly]), select, button');
    if (firstInput) firstInput.focus();
  }

  function closeModals() {
    document.querySelectorAll('.modal.open').forEach(m => m.classList.remove('open'));
    if (!document.querySelector('.drawer.open')) {
      els.overlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  els.overlay.addEventListener('click', () => {
    closeDrawers();
    closeModals();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeDrawers();
      closeModals();
    }
  });

  /* =========================================================================
     TOAST
     ========================================================================= */
  let toastTimer = null;
  function showToast(msg) {
    els.toastMsg.textContent = msg;
    els.toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => els.toast.classList.remove('show'), 3200);
  }

  /* =========================================================================
     MOBILE SIDEBAR & NOTIFICATIONS
     ========================================================================= */
  els.hamburgerBtn.addEventListener('click', () => {
    const isOpen = els.sidebar.classList.toggle('mobile-open');
    els.hamburgerBtn.setAttribute('aria-expanded', String(isOpen));
    els.overlay.classList.toggle('open', isOpen);
  });

  function initNotifications() {
    function closePanel() {
      els.notifPanel.hidden = true;
      els.notifBellBtn.setAttribute('aria-expanded', 'false');
    }
    function togglePanel() {
      const isOpen = !els.notifPanel.hidden;
      if (isOpen) { closePanel(); return; }
      els.notifPanel.hidden = false;
      els.notifBellBtn.setAttribute('aria-expanded', 'true');
    }
    els.notifBellBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      togglePanel();
    });
    document.addEventListener('click', (e) => {
      if (!els.notifPanel.hidden && !els.notifPanel.contains(e.target) && e.target !== els.notifBellBtn) {
        closePanel();
      }
    });
    els.notifMarkAllBtn.addEventListener('click', () => {
      els.notifBadge.hidden = true;
      els.notifBadge.textContent = '0';
    });
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

  // เริ่มต้นทำงาน
  renderList();
  initNotifications();

})();
</script>
</body>
</html>
