<?php 
// =========================================================
// 1. รับค่า Date Range จาก Date Picker
// =========================================================
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');
$sim_error  = isset($_GET['sim_error']) && $_GET['sim_error'] == '1'; // จำลอง Error State สำหรับตรวจงาน

$db_error = false;
$error_message = '';

try {
    if ($sim_error) {
        throw new Exception("จำลองข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล");
    }
    require_once 'config/db.php';

    // --- 1.1 ข้อมูล KPI ทั่วไป (คงเดิม 100%) ---
    $total_owners = $pdo->query("SELECT COUNT(*) FROM owner")->fetchColumn();
    $total_pets = $pdo->query("SELECT COUNT(*) FROM pet")->fetchColumn();
    
    $today_apps = $pdo->query("SELECT COUNT(*) FROM appointments WHERE Date BETWEEN '$start_date' AND '$end_date'")->fetchColumn();
    
    $sql_pending_treat = "SELECT COUNT(*) FROM appointments a LEFT JOIN diagnoses d ON a.appointments_ID = d.appointment_ID WHERE (a.Date BETWEEN '$start_date' AND '$end_date') AND d.diagnoses_ID IS NULL";
    $pending_treat = $pdo->query($sql_pending_treat)->fetchColumn();

    $sql_pending_billing = "SELECT COUNT(*) FROM diagnoses d JOIN appointments a ON d.appointment_ID = a.appointments_ID LEFT JOIN receipts r ON d.appointment_ID = r.appointment_ID WHERE (a.Date BETWEEN '$start_date' AND '$end_date') AND r.receipt_ID IS NULL";
    $pending_billing = $pdo->query($sql_pending_billing)->fetchColumn();

    // --- 1.2 ข้อมูล KPI การเงิน (คงเดิม 100%) ---
    $fin_kpis = $pdo->query("SELECT 
        COALESCE(SUM(r.grand_total), 0) as total_rev, 
        COUNT(r.receipt_ID) as total_bills,
        COALESCE(AVG(r.grand_total), 0) as avg_bill,
        COALESCE(SUM(r.service_fee), 0) as total_service
        FROM receipts r
        JOIN appointments a ON r.appointment_ID = a.appointments_ID
        WHERE a.Date BETWEEN '$start_date' AND '$end_date'")->fetch(PDO::FETCH_ASSOC);

    // --- Medical Trend ---
    $med_trend_custom = $pdo->query("SELECT Date, COUNT(*) as count FROM appointments WHERE Date BETWEEN '$start_date' AND '$end_date' GROUP BY Date ORDER BY Date")->fetchAll(PDO::FETCH_ASSOC);
    $med_trend_7 = $pdo->query("SELECT Date, COUNT(*) as count FROM appointments WHERE Date BETWEEN DATE_SUB('$end_date', INTERVAL 6 DAY) AND '$end_date' GROUP BY Date ORDER BY Date")->fetchAll(PDO::FETCH_ASSOC);
    $med_trend_30 = $pdo->query("SELECT Date, COUNT(*) as count FROM appointments WHERE Date BETWEEN DATE_SUB('$end_date', INTERVAL 29 DAY) AND '$end_date' GROUP BY Date ORDER BY Date")->fetchAll(PDO::FETCH_ASSOC);
    $med_trend_365 = $pdo->query("SELECT DATE_FORMAT(Date, '%b %Y') as Date, COUNT(*) as count FROM appointments WHERE Date BETWEEN DATE_SUB('$end_date', INTERVAL 1 YEAR) AND '$end_date' GROUP BY DATE_FORMAT(Date, '%Y-%m') ORDER BY DATE_FORMAT(Date, '%Y-%m')")->fetchAll(PDO::FETCH_ASSOC);

    // --- Finance Trend ---
    $fin_trend_custom = $pdo->query("SELECT a.Date, SUM(r.grand_total) as total FROM receipts r JOIN appointments a ON r.appointment_ID = a.appointments_ID WHERE a.Date BETWEEN '$start_date' AND '$end_date' GROUP BY a.Date ORDER BY a.Date")->fetchAll(PDO::FETCH_ASSOC);
    $fin_trend_7 = $pdo->query("SELECT a.Date, SUM(r.grand_total) as total FROM receipts r JOIN appointments a ON r.appointment_ID = a.appointments_ID WHERE a.Date BETWEEN DATE_SUB('$end_date', INTERVAL 6 DAY) AND '$end_date' GROUP BY a.Date ORDER BY a.Date")->fetchAll(PDO::FETCH_ASSOC);
    $fin_trend_30 = $pdo->query("SELECT a.Date, SUM(r.grand_total) as total FROM receipts r JOIN appointments a ON r.appointment_ID = a.appointments_ID WHERE a.Date BETWEEN DATE_SUB('$end_date', INTERVAL 29 DAY) AND '$end_date' GROUP BY Date ORDER BY a.Date")->fetchAll(PDO::FETCH_ASSOC);
    $fin_trend_365 = $pdo->query("SELECT DATE_FORMAT(a.Date, '%b %Y') as Date, SUM(r.grand_total) as total FROM receipts r JOIN appointments a ON r.appointment_ID = a.appointments_ID WHERE a.Date BETWEEN DATE_SUB('$end_date', INTERVAL 1 YEAR) AND '$end_date' GROUP BY DATE_FORMAT(a.Date, '%Y-%m') ORDER BY DATE_FORMAT(a.Date, '%Y-%m')")->fetchAll(PDO::FETCH_ASSOC);

    // --- ข้อมูล Pie & Table ---
    $doc_pie = $pdo->query("SELECT d.name, COUNT(a.appointments_ID) as val FROM appointments a JOIN doctor d ON a.doctor_ID = d.doctor_ID WHERE a.Date BETWEEN '$start_date' AND '$end_date' GROUP BY d.doctor_ID")->fetchAll(PDO::FETCH_ASSOC);
    $top_diagnoses = $pdo->query("SELECT dg.diagnosisDescription as name, COUNT(*) as qty FROM diagnoses dg JOIN appointments a ON dg.appointment_ID = a.appointments_ID WHERE a.Date BETWEEN '$start_date' AND '$end_date' GROUP BY dg.diagnosisDescription ORDER BY qty DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    $pay_pie = $pdo->query("SELECT r.payment_method as name, COUNT(*) as val FROM receipts r JOIN appointments a ON r.appointment_ID = a.appointments_ID WHERE a.Date BETWEEN '$start_date' AND '$end_date' GROUP BY r.payment_method")->fetchAll(PDO::FETCH_ASSOC);
    $top_meds = $pdo->query("SELECT rd.item_name as name, SUM(rd.item_price) as qty FROM receipt_details rd JOIN receipts r ON rd.receipt_ID = r.receipt_ID JOIN appointments a ON r.appointment_ID = a.appointments_ID WHERE a.Date BETWEEN '$start_date' AND '$end_date' GROUP BY rd.item_name ORDER BY qty DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $db_error = true;
    $error_message = $e->getMessage();
    $total_owners = $total_pets = $today_apps = $pending_treat = $pending_billing = 0;
    $fin_kpis = ['total_rev' => 0, 'total_bills' => 0, 'avg_bill' => 0, 'total_service' => 0];
    $med_trend_custom = $med_trend_7 = $med_trend_30 = $med_trend_365 = $doc_pie = $top_diagnoses = [];
    $fin_trend_custom = $fin_trend_7 = $fin_trend_30 = $fin_trend_365 = $pay_pie = $top_meds = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetCare Portal | Executive Dashboard (v04 - States & Constraints)</title>
    <!-- โหลด Design Token จากขั้นที่ 1 -->
    <link rel="stylesheet" href="css/petcare-token.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Base Reset ตาม Design Tokens */
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: var(--font-family-base);
            background-color: var(--color-background);
            color: var(--color-black-1000);
            font-size: var(--font-size-md);
            line-height: 1.5;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select { font-family: inherit; }

        ul { list-style: none; margin: 0; padding: 0; }
        h1, h2, h3, h4, h5, h6 { margin: 0; }

        /* Focus state (Accessibility §15) */
        a:focus-visible, button:focus-visible, select:focus-visible, input:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }

        /* =========================================================
           LAYOUT SHELL & SIDEBAR NAVIGATION (§4 ตามต้นแบบ index.html)
           ========================================================= */
        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar / Left Navigation */
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
            padding: 8px var(--Grid-8);
            border-radius: var(--radius-md);
            font-size: var(--font-size-md);
            font-weight: 400;
            color: var(--color-black-1000);
            text-decoration: none;
            margin-bottom: 4px;
            position: relative;
            border: 1px solid transparent;
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .nav-link:hover { background: var(--color-background); }
        .nav-link[aria-current="page"], .nav-link.active {
            background: var(--color-background);
            border-color: var(--color-primary);
            font-weight: 700;
        }
        .nav-icon {
            width: 24px;
            height: 24px;
            text-align: center;
            flex-shrink: 0;
            font-size: 16px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .nav-badge {
            margin-left: auto;
            background: var(--color-danger);
            color: var(--color-black-1000);
            font-size: var(--font-size-sm);
            font-weight: 700;
            padding: 0 8px;
            border-radius: var(--chip-radius);
            line-height: 20px;
            height: 20px;
        }

        /* Main Content */
        .main {
            flex: 1;
            min-width: 0;
            padding: var(--space-lg);
        }

        /* Page Header Toolbar */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
            background-color: var(--color-surface);
            border-radius: var(--radius-md);
            padding: var(--space-md) var(--space-lg);
            border: 1px solid var(--color-gray-400);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .header-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            margin: 0;
            color: var(--color-black-1000);
        }
        .header-subtitle {
            font-size: var(--font-size-sm);
            color: var(--color-black-1000);
            opacity: .7;
            margin: 2px 0 0 0;
        }
        .header-controls {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--space-md);
        }

        /* Step 4: Error Banner State ตาม Voice & Tone ใน design.md */
        .error-banner {
            background-color: var(--color-surface);
            border: 2px solid var(--color-danger);
            border-left: 8px solid var(--color-danger);
            border-radius: var(--radius-md);
            padding: var(--space-md);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: flex-start;
            gap: var(--space-md);
        }
        .error-banner-icon {
            font-size: 28px;
            color: var(--color-danger);
            line-height: 1;
        }
        .error-banner-title {
            font-size: var(--font-size-md);
            font-weight: 700;
            margin: 0 0 4px 0;
            color: var(--color-black-1000);
        }
        .error-banner-desc {
            font-size: var(--font-size-sm);
            color: var(--color-black-1000);
            opacity: .75;
            margin: 0;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: var(--Grid);
            background-color: var(--tint-background-strong);
            padding: var(--Grid) var(--space-md);
            border-radius: var(--chip-radius);
            border: 1px solid var(--color-gray-400);
        }
        .filter-form input[type="date"] {
            border: 1px solid var(--color-gray-400);
            border-radius: var(--radius-md);
            padding: 4px var(--Grid);
            font-size: var(--font-size-sm);
            background-color: var(--color-surface);
        }
        .btn-filter {
            background-color: var(--button-primary-bg);
            color: var(--button-primary-text);
            border: none;
            border-radius: var(--chip-radius);
            padding: 6px var(--space-md);
            font-size: var(--font-size-sm);
            font-weight: 600;
            cursor: pointer;
        }

        .segmented-tabs {
            display: flex;
            background-color: var(--color-gray-400);
            border-radius: var(--radius-md);
            padding: 2px;
            gap: 2px;
        }
        .tab-btn {
            background: none;
            border: none;
            border-radius: var(--radius-md);
            padding: var(--Grid) var(--space-md);
            font-size: var(--font-size-sm);
            font-weight: 600;
            cursor: pointer;
            color: var(--color-black-1000);
        }
        .tab-btn.active {
            background-color: var(--color-surface);
            color: var(--color-primary);
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        /* R-01: KPI Summary Cards 32px */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-lg);
        }
        .kpi-card {
            background-color: var(--color-surface);
            border: 1px solid var(--color-gray-400);
            border-radius: var(--radius-md);
            padding: var(--space-md);
        }
        .kpi-label {
            font-size: var(--font-size-sm);
            color: var(--color-black-1000);
            opacity: .7;
            font-weight: 600;
            margin-bottom: var(--Grid);
        }
        .kpi-value {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--color-primary);
            margin: 0 0 4px 0;
            line-height: 1.1;
        }
        .kpi-sub {
            font-size: var(--font-size-sm);
            color: var(--color-black-1000);
            opacity: .65;
        }

        /* Chart Section */
        .chart-card {
            background-color: var(--color-surface);
            border: 1px solid var(--color-gray-400);
            border-radius: var(--radius-md);
            padding: var(--space-md);
            margin-bottom: var(--space-lg);
        }
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-md);
            flex-wrap: wrap;
            gap: var(--Grid);
        }
        .chart-title {
            font-size: var(--font-size-md);
            font-weight: 700;
            margin: 0;
        }
        .select-token {
            padding: 6px var(--space-md);
            border: 1px solid var(--color-gray-400);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            background-color: var(--color-surface);
            color: var(--color-primary);
            font-weight: 600;
        }

        /* Bottom Grid */
        .bottom-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: var(--space-lg);
        }
        @media (max-width: 1080px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .bottom-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 860px) {
            .app-shell { flex-direction: column; }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid var(--color-gray-400);
            }
            .sidebar nav ul { display: flex; flex-wrap: wrap; gap: 4px; }
            .nav-group-label { display: none; }
            .nav-link { flex: 1 1 auto; justify-content: center; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .header-controls { width: 100%; }
        }
        @media (max-width: 560px) {
            .kpi-grid { grid-template-columns: 1fr 1fr; }
            .main { padding: var(--space-md); }
            .filter-form { width: 100%; flex-wrap: wrap; }
        }

        .stat-card {
            background-color: var(--color-surface);
            border: 1px solid var(--color-gray-400);
            border-radius: var(--radius-md);
            padding: var(--space-md);
        }
        .stat-title {
            font-size: var(--font-size-md);
            font-weight: 700;
            margin: 0 0 var(--space-md) 0;
        }
        .table-token {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--font-size-sm);
        }
        .table-token th, .table-token td {
            padding: var(--Grid) var(--space-md);
            text-align: left;
            border-bottom: 1px solid var(--color-gray-400);
        }
        .table-token th {
            background-color: var(--tint-background-strong);
            font-weight: 700;
        }
        .table-token td.qty {
            text-align: right;
            font-weight: 700;
            color: var(--color-primary);
        }

        .detail-controls {
            display: flex;
            gap: var(--space-md);
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: var(--space-md);
            padding-bottom: var(--space-md);
            border-bottom: 1px solid var(--color-gray-400);
        }
        .search-field { position: relative; display: flex; align-items: center; }
        .search-field .search-input {
            padding: 6px var(--space-md) 6px 36px;
            font-weight: 400;
            width: 100%;
            border: 1px solid var(--color-gray-400);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            background-color: var(--color-surface);
        }
        .search-field .search-icon {
            position: absolute; left: 12px; opacity: 0.5; font-size: var(--font-size-sm);
        }
        .search-field .search-clear {
            position: absolute; right: 8px; border: none; background: transparent;
            font-size: var(--font-size-sm); opacity: 0.6; padding: 4px 8px; border-radius: var(--radius-md);
            display: none; cursor: pointer;
        }
        .search-field .search-clear:hover { background: var(--tint-background-strong); }

        .footer-note {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: var(--font-size-sm);
            color: var(--color-black-1000);
            opacity: .7;
            margin-top: var(--space-lg);
            padding: var(--space-md) 0;
            flex-wrap: wrap;
            gap: var(--space-md);
        }
    </style>
</head>
<body>

<div class="app-shell">

    <!-- =========================================================
         LEFT NAVIGATION (§4 เมนูด้านข้างตามต้นแบบ index.html)
         ========================================================= -->
    <aside class="sidebar">
        <div class="brand">PetCare<span>Portal</span></div>

        <nav aria-label="เมนูหลัก">
            <div class="nav-group">
                <div class="nav-group-label">ภาพรวม</div>
                <ul>
                    <li><a class="nav-link active" href="index.php" aria-current="page"><span class="nav-icon">📊</span><span>Dashboard</span></a></li>
                </ul>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">งานหน้าเคาน์เตอร์</div>
                <ul>
                    <li><a class="nav-link" href="pages/register.php"><span class="nav-icon">🐕</span><span>ลงทะเบียนเจ้าของ/สัตว์</span></a></li>
                    <li><a class="nav-link" href="pages/pet_list.php"><span class="nav-icon">🐾</span><span>จัดการสัตว์เลี้ยง</span></a></li>
                    <li><a class="nav-link" href="pages/appointment_form.php"><span class="nav-icon">📅</span><span>สร้างนัดหมาย</span></a></li>
                    <li><a class="nav-link" href="pages/treatment_form.php"><span class="nav-icon">🩺</span><span>ห้องตรวจแพทย์</span><?php if($pending_treat > 0 && $start_date == date('Y-m-d') && $end_date == date('Y-m-d')): ?><span class="nav-badge" id="navBadgeTreat"><?= $pending_treat ?></span><?php endif; ?></a></li>
                    <li><a class="nav-link" href="pages/billing_list.php"><span class="nav-icon">💵</span><span>การเงิน/ใบเสร็จ</span><?php if($pending_billing > 0 && $start_date == date('Y-m-d') && $end_date == date('Y-m-d')): ?><span class="nav-badge" id="navBadgeBilling"><?= $pending_billing ?></span><?php endif; ?></a></li>
                </ul>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">ประวัติและรายการ</div>
                <ul>
                    <li><a class="nav-link" href="pages/appointment_list.php"><span class="nav-icon">📋</span><span>รายการนัดหมาย</span></a></li>
                    <li><a class="nav-link" href="pages/treatment_history.php"><span class="nav-icon">📁</span><span>ประวัติการรักษา</span></a></li>
                    <li><a class="nav-link" href="pages/owner_list.php"><span class="nav-icon">👤</span><span>ข้อมูลเจ้าของ</span></a></li>
                    <li><a class="nav-link" href="pages/doctor_list.php"><span class="nav-icon">🧑‍⚕️</span><span>ข้อมูลสัตวแพทย์</span></a></li>
                </ul>
            </div>
        </nav>
    </aside>

    <!-- =========================================================
         MAIN CONTENT
         ========================================================= -->
    <main class="main">

        <!-- Step 4: Error State Banner (แสดงเมื่อเกิดข้อผิดพลาดในการโหลดข้อมูลจริง) -->
        <?php if ($db_error): ?>
        <div class="error-banner" role="alert">
            <div class="error-banner-icon">⚠️</div>
            <div>
                <h4 class="error-banner-title">ไม่สามารถโหลดข้อมูลแดชบอร์ดได้</h4>
                <p class="error-banner-desc">ระบบเชื่อมต่อฐานข้อมูลไม่สำเร็จในขณะนี้ กรุณาลองรีเฟรชหน้านี้อีกครั้ง หรือติดต่อผู้ดูแลระบบหากยังพบปัญหาอย่างต่อเนื่อง</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Page Header Toolbar -->
        <div class="page-header">
            <div>
                <h1 class="header-title">ภาพรวมข้อมูลคลินิก</h1>
                <p class="header-subtitle">ระบบบริหารจัดการโรงพยาบาลสัตว์ — Executive Dashboard</p>
            </div>
            <div class="header-controls">
                <!-- Form กรองช่วงวันที่ (คง GET parameter เดิม) -->
                <form method="GET" class="filter-form">
                    <span>ช่วงวันที่:</span>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
                    <span>ถึง</span>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
                    <button type="submit" class="btn-filter">กรองข้อมูล</button>
                </form>

                <!-- แถบสลับมุมมองสถิติ -->
                <div class="segmented-tabs" role="tablist">
                    <button type="button" class="tab-btn active" id="tabMedical" onclick="switchDash('medical', event)">🩺 สถิติการทำงาน</button>
                    <button type="button" class="tab-btn" id="tabFinance" onclick="switchDash('finance', event)">💰 ภาพรวมรายได้</button>
                </div>

                <!-- ปุ่มทดสอบ Error State สำหรับตรวจงาน -->
                <div>
                    <?php if($sim_error): ?>
                        <a href="index.php" class="btn-filter" style="background-color: var(--color-success);">กลับสู่โหมดปกติ</a>
                    <?php else: ?>
                        <a href="index.php?sim_error=1" class="btn-filter" style="background-color: var(--color-danger); color: var(--color-black-1000);">🧪 ทดสอบ Error State</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- R-01 ส่วนบน: KPI Summary Cards 32px -->
        <section class="kpi-grid" aria-label="สรุปสถิติสำคัญ">
            <div class="kpi-card">
                <div class="kpi-label" id="kpi1-title">-</div>
                <div class="kpi-value" id="kpi1-val">-</div>
                <div class="kpi-sub" id="kpi1-sub">-</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label" id="kpi2-title">-</div>
                <div class="kpi-value" id="kpi2-val">-</div>
                <div class="kpi-sub" id="kpi2-sub">-</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label" id="kpi3-title">-</div>
                <div class="kpi-value" id="kpi3-val">-</div>
                <div class="kpi-sub" id="kpi3-sub">-</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label" id="kpi4-title">-</div>
                <div class="kpi-value" id="kpi4-val">-</div>
                <div class="kpi-sub" id="kpi4-sub">-</div>
            </div>
        </section>

        <!-- R-01 ส่วนกลาง: กราฟแนวโน้ม (Chart.js) -->
        <section class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title" id="trend-title">แนวโน้มปริมาณคิวตรวจ</h3>
                <div>
                    <label for="trendRange" style="font-size: var(--font-size-sm); margin-right: 4px;">ช่วงเวลา:</label>
                    <select class="select-token" id="trendRange" onchange="updateTrendChart()">
                        <option value="custom">📅 ตามช่วงวันที่เลือก</option>
                        <option value="7">7 วันย้อนหลัง</option>
                        <option value="30">1 เดือนย้อนหลัง</option>
                        <option value="365">1 ปี (รายเดือน)</option>
                    </select>
                </div>
            </div>
            <div style="height: 280px;">
                <canvas id="trendChart"></canvas>
            </div>
        </section>

        <!-- R-01 ส่วนล่าง: Dropdown เลือกข้อมูลที่ต้องการดู (แยกโรค/ภาระงานสัตวแพทย์ ออกจากกันตาม R-01) พร้อมระบบค้นหา -->
        <section class="stat-card" style="border: 1px solid var(--color-gray-400);">
            <div class="detail-controls">
                <div>
                    <label class="field-label" for="detailSelector">เลือกข้อมูลที่ต้องการดู</label>
                    <select class="select-token" id="detailSelector" onchange="renderDetail()"></select>
                </div>
                <div id="vetSearchWrap" style="display: none; flex: 1; min-width: 240px;">
                    <label class="field-label" for="vetSearch">ค้นหาชื่อสัตวแพทย์</label>
                    <div class="search-field">
                        <span class="search-icon" aria-hidden="true">🔍</span>
                        <input type="text" class="search-input" id="vetSearch" placeholder="พิมพ์ชื่อสัตวแพทย์…" autocomplete="off" oninput="renderDetail()">
                        <button type="button" class="search-clear" id="vetSearchClear" onclick="clearVetSearch()">ล้าง ✕</button>
                    </div>
                </div>
            </div>

            <div class="bottom-grid" style="margin-top: var(--space-md);">
                <div class="stat-card" style="border: 1px solid var(--color-gray-400); box-shadow: none;">
                    <h3 class="stat-title" id="table-title" style="margin-bottom: var(--space-md);">-</h3>
                    <table class="table-token">
                        <thead>
                            <tr>
                                <th style="width: 60px;">อันดับ</th>
                                <th id="table-col">-</th>
                                <th style="text-align: right;" id="table-col-qty">-</th>
                            </tr>
                        </thead>
                        <tbody id="table-body"></tbody>
                    </table>
                </div>
                <div class="stat-card" style="border: 1px solid var(--color-gray-400); box-shadow: none;">
                    <h3 class="stat-title" id="pie-title" style="text-align: center; margin-bottom: var(--space-md);">-</h3>
                    <div style="height: 240px; display: flex; justify-content: center; align-items: center; position: relative;">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer-note">
            <span>PetCare Portal System © 2026 | v05 Final Integrated</span>
            <span>Heuristic: R-01, H1, H5, H8, H9</span>
        </footer>
    </main>
</div>

<script>
    const dbData = {
        medical: {
            kpi_pets: "<?= number_format($total_pets) ?>", 
            kpi_today: "<?= number_format($today_apps) ?>",
            kpi_pending: "<?= number_format($pending_treat) ?>", 
            kpi_billing: "<?= number_format($pending_billing) ?>",
            trend: {
                'custom': <?= json_encode($med_trend_custom) ?>,
                '7': <?= json_encode($med_trend_7) ?>,
                '30': <?= json_encode($med_trend_30) ?>,
                '365': <?= json_encode($med_trend_365) ?>
            },
            details: {
                workload: {
                    label: 'ภาระงานสัตวแพทย์',
                    tableTitle: 'ภาระงานสัตวแพทย์ (จำนวนเคส)',
                    col: 'สัตวแพทย์',
                    colQty: 'จำนวนเคส',
                    pieTitle: 'ภาระงานสัตวแพทย์ (สัดส่วน)',
                    prefix: '',
                    nameKey: 'name',
                    valKey: 'val',
                    searchable: true,
                    data: <?= json_encode($doc_pie) ?>
                },
                diagnoses: {
                    label: 'ข้อมูลโรคที่พบบ่อย',
                    tableTitle: '5 อันดับโรคที่พบบ่อยสุด (ช่วงที่เลือก)',
                    col: 'ผลการวินิจฉัย',
                    colQty: 'จำนวน (เคส)',
                    pieTitle: 'สถิติโรคที่พบบ่อย (สัดส่วน)',
                    prefix: '',
                    nameKey: 'name',
                    valKey: 'qty',
                    searchable: false,
                    data: <?= json_encode($top_diagnoses) ?>
                }
            }
        },
        finance: {
            kpi_rev: "<?= number_format($fin_kpis['total_rev'], 2) ?>", 
            kpi_bills: "<?= number_format($fin_kpis['total_bills']) ?>",
            kpi_avg: "<?= number_format($fin_kpis['avg_bill'], 2) ?>", 
            kpi_service: "<?= number_format($fin_kpis['total_service'], 2) ?>",
            trend: {
                'custom': <?= json_encode($fin_trend_custom) ?>,
                '7': <?= json_encode($fin_trend_7) ?>,
                '30': <?= json_encode($fin_trend_30) ?>,
                '365': <?= json_encode($fin_trend_365) ?>
            },
            details: {
                payment: {
                    label: 'ช่องทางการชำระเงิน',
                    tableTitle: 'ช่องทางการชำระเงิน (จำนวนบิล)',
                    col: 'ช่องทาง',
                    colQty: 'จำนวนบิล',
                    pieTitle: 'ช่องทางการชำระเงิน (สัดส่วน)',
                    prefix: '',
                    nameKey: 'name',
                    valKey: 'val',
                    searchable: false,
                    data: <?= json_encode($pay_pie) ?>
                },
                topmeds: {
                    label: 'เวชภัณฑ์และยาที่สร้างรายได้',
                    tableTitle: '5 อันดับยาที่สร้างรายได้สูงสุด (ช่วงที่เลือก)',
                    col: 'ชื่อเวชภัณฑ์/ยา',
                    colQty: 'ยอดขายรวม (บาท)',
                    pieTitle: 'เวชภัณฑ์/ยา (สัดส่วน)',
                    prefix: '฿',
                    nameKey: 'name',
                    valKey: 'qty',
                    searchable: false,
                    data: <?= json_encode($top_meds) ?>
                }
            }
        }
    };

    function getTokenColors() {
        const root = getComputedStyle(document.documentElement);
        return {
            primary: root.getPropertyValue('--color-primary').trim(),
            success: root.getPropertyValue('--color-success').trim(),
            warning: root.getPropertyValue('--color-danger').trim(),
            secondary: root.getPropertyValue('--color-background').trim(),
            dark: root.getPropertyValue('--color-black-1000').trim(),
            gray: root.getPropertyValue('--color-gray-400').trim(),
            white: root.getPropertyValue('--color-white').trim()
        };
    }
    let tokenColors = {};
    let chartColors = [];
    
    let trendChartObj, pieChartObj;
    let currentDashType = 'medical';
    let currentDetailKey = 'workload';
    let selectedSliceIndex = null;
    let hoveredSliceIndex = null;

    window.onload = function() {
        tokenColors = getTokenColors();
        chartColors = [tokenColors.primary, tokenColors.secondary, tokenColors.success, tokenColors.warning, tokenColors.gray];
        initCharts();
        switchDash('medical'); 
    };

    function initCharts() {
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        trendChartObj = new Chart(ctxTrend, {
            type: 'line', 
            data: { labels: [], datasets: [] },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: tokenColors.gray } }, 
                    x: { grid: { display: false } } 
                }, 
                elements: { line: { tension: 0.3 } } 
            }
        });

        // Plugin วาดตัวเลขตรงกลางวงกลมโดนัทอย่างแม่นยำ (แสดงตัวเลขตามหัวข้อที่เลือก/ชี้ หรือยอดรวมทั้งหมด)
        const centerTextPlugin = {
        id: 'centerTextPlugin',
        afterDraw(chart) {
            if (chart.config.type !== 'doughnut') return;
            const { ctx, chartArea } = chart;
            if (!chartArea) return;

            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = (chartArea.top + chartArea.bottom) / 2;

            const dataset = chart.data.datasets[0];
            if (!dataset || !dataset.data || dataset.data.length === 0) return;
            if (chart.data.labels[0] === 'ไม่มีข้อมูล') return;

            const total = dataset.data.reduce((sum, val) => sum + Number(val || 0), 0);
            const prefix = chart.detailPrefix || '';

            // ตรวจสอบว่ามีการชี้ (Hover) หรือคลิกเลือก (Select) หัวข้อใดอยู่หรือไม่
            const activeIdx = (hoveredSliceIndex !== null) ? hoveredSliceIndex : selectedSliceIndex;
            let displayVal = total;
            let displayLabel = 'รวมทั้งหมด';
            let displayColor = tokenColors.primary;

            if (activeIdx !== null && activeIdx >= 0 && activeIdx < dataset.data.length) {
                displayVal = dataset.data[activeIdx];
                const rawLabel = chart.data.labels[activeIdx] || '';
                const pct = total > 0 ? ((Number(displayVal) / total) * 100).toFixed(1) + '%' : '';
                displayLabel = `${rawLabel} (${pct})`;
                if (dataset.backgroundColor && dataset.backgroundColor[activeIdx]) {
                    displayColor = (dataset.backgroundColor[activeIdx] === tokenColors.gray) ? tokenColors.dark : dataset.backgroundColor[activeIdx];
                }
            }

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            // 1. วาดตัวเลขตามหัวข้อที่เลือก (หรือผลรวมทั้งหมด)
            ctx.font = 'bold 22px -apple-system, "Segoe UI", "Noto Sans Thai", "Leelawadee UI", sans-serif';
            ctx.fillStyle = displayColor;
            const formattedNum = prefix + Number(displayVal).toLocaleString();
            ctx.fillText(formattedNum, centerX, centerY - 9);

            // 2. วาดคำอธิบาย/ชื่อหัวข้อใต้ตัวเลข (ตัดทอนข้อความอัตโนมัติหากยาวเกินเพื่อป้องกันการล้นวงกลม)
            ctx.font = '11px -apple-system, "Segoe UI", "Noto Sans Thai", "Leelawadee UI", sans-serif';
            ctx.fillStyle = tokenColors.dark;
            let trimmedLabel = displayLabel;
            if (ctx.measureText(trimmedLabel).width > 125) {
                while (ctx.measureText(trimmedLabel + '…').width > 125 && trimmedLabel.length > 0) {
                    trimmedLabel = trimmedLabel.slice(0, -1);
                }
                trimmedLabel += '…';
            }
            ctx.fillText(trimmedLabel, centerX, centerY + 12);

            ctx.restore();
        }
    };

    const ctxPie = document.getElementById('pieChart').getContext('2d');
    pieChartObj = new Chart(ctxPie, {
        type: 'doughnut', 
        data: { labels: [], datasets: [] },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            cutout: '65%', 
            plugins: { 
                legend: { 
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: { size: 12 },
                        // แสดงตัวเลขกำกับท้ายชื่อแต่ละหัวข้อใน Legend
                        generateLabels(chart) {
                            const original = Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart);
                            const dataset = chart.data.datasets[0];
                            if (!dataset || !dataset.data || chart.data.labels[0] === 'ไม่มีข้อมูล') return original;
                            const prefix = chart.detailPrefix || '';
                            return original.map((item, idx) => {
                                const val = dataset.data[idx];
                                if (val !== undefined) {
                                    item.text = `${item.text} (${prefix}${Number(val).toLocaleString()})`;
                                }
                                return item;
                            });
                        }
                    },
                    // นำเมาส์ชี้ที่ Legend เพื่อแสดงตัวเลขตรงกลางวงกลมตามหัวข้อนั้น
                    onHover(e, legendItem, legend) {
                        hoveredSliceIndex = legendItem.index;
                        legend.chart.draw();
                    },
                    onLeave(e, legendItem, legend) {
                        hoveredSliceIndex = null;
                        legend.chart.draw();
                    }
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const val = context.parsed || 0;
                            const prefix = context.chart.detailPrefix || '';
                            const total = context.dataset.data.reduce((a, b) => a + Number(b || 0), 0);
                            const pct = total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '';
                            return ` ${context.label}: ${prefix}${Number(val).toLocaleString()} (${pct})`;
                        }
                    }
                }
            },
            // เมื่อเลื่อนเมาส์ชี้ที่เสี้ยววงกลม (Slice) ให้อัปเดตตัวเลขตรงกลางตามหัวข้อที่ชี้
            onHover(event, activeElements) {
                if (activeElements && activeElements.length > 0) {
                    hoveredSliceIndex = activeElements[0].index;
                } else {
                    hoveredSliceIndex = null;
                }
                pieChartObj.draw();
            },
            // เมื่อคลิกที่เสี้ยววงกลม ให้เลือกค้างไว้ (Toggle Selection)
            onClick(event, activeElements) {
                if (activeElements && activeElements.length > 0) {
                    const clicked = activeElements[0].index;
                    selectedSliceIndex = (selectedSliceIndex === clicked) ? null : clicked;
                } else {
                    selectedSliceIndex = null;
                }
                pieChartObj.draw();
            }
        },
        plugins: [centerTextPlugin]
    });

        const pieCanvasEl = document.getElementById('pieChart');
        if (pieCanvasEl) {
            pieCanvasEl.addEventListener('mouseleave', () => {
                hoveredSliceIndex = null;
                if (pieChartObj) pieChartObj.draw();
            });
        }
    }

    function switchDash(type, event) {
        currentDashType = type;
        
        document.querySelectorAll('.segmented-tabs .tab-btn').forEach(btn => btn.classList.remove('active'));
        if(event) {
            event.target.classList.add('active');
        } else {
            const btnId = (type === 'medical') ? 'tabMedical' : 'tabFinance';
            const btn = document.getElementById(btnId);
            if (btn) btn.classList.add('active');
        }
        
        document.getElementById('trendRange').value = 'custom';

        // 1. นำข้อมูลตัวเลือกใส่ลงใน Dropdown (#detailSelector)
        const details = dbData[type].details;
        const selector = document.getElementById('detailSelector');
        selector.innerHTML = '';
        Object.keys(details).forEach(key => {
            const opt = document.createElement('option');
            opt.value = key;
            opt.textContent = details[key].label;
            selector.appendChild(opt);
        });
        currentDetailKey = Object.keys(details)[0];
        selector.value = currentDetailKey;

        // 2. รีเซ็ตช่องค้นหาชื่อสัตวแพทย์
        const vetSearch = document.getElementById('vetSearch');
        if (vetSearch) vetSearch.value = '';
        const vetClear = document.getElementById('vetSearchClear');
        if (vetClear) vetClear.style.display = 'none';

        // 3. ปรับค่าการแสดงผล KPI ตามมุมมอง
        if (type === 'medical') {
            const d = dbData.medical;
            updateKPI('kpi1', 'สัตว์เลี้ยงในระบบรวม', d.kpi_pets, 'ตัว (สะสมทั้งหมด)');
            updateKPI('kpi2', 'นัดหมายช่วงที่เลือก', d.kpi_today, 'เคส');
            updateKPI('kpi3', 'ค้างตรวจช่วงที่เลือก', d.kpi_pending, 'เคส');
            updateKPI('kpi4', 'ค้างชำระช่วงที่เลือก', d.kpi_billing, 'บิล');
            document.getElementById('trend-title').innerText = "แนวโน้มปริมาณคิวตรวจ";
        } else if (type === 'finance') {
            const d = dbData.finance;
            updateKPI('kpi1', 'รายได้ทั้งหมด (ช่วงที่เลือก)', '฿' + d.kpi_rev, 'บาท');
            updateKPI('kpi2', 'จำนวนใบเสร็จ', d.kpi_bills, 'บิล');
            updateKPI('kpi3', 'ยอดเฉลี่ยต่อบิล', '฿' + d.kpi_avg, 'บาท/บิล');
            updateKPI('kpi4', 'ค่าบริการแพทย์', '฿' + d.kpi_service, 'บาท');
            document.getElementById('trend-title').innerText = "แนวโน้มรายได้ทางการแพทย์";
        }

        updateTrendChart();
        renderDetail();
    }

    function updateTrendChart() {
        const range = document.getElementById('trendRange').value;
        const d = dbData[currentDashType];
        
        let labelKey = 'Date';
        let dataKey = currentDashType === 'medical' ? 'count' : 'total';
        let datasetLabel = currentDashType === 'medical' ? 'จำนวนเคส' : 'รายได้ (บาท)';
        let color = currentDashType === 'medical' ? tokenColors.primary : tokenColors.success;
        let bgColor = currentDashType === 'medical' ? 'rgba(91, 79, 224, 0.12)' : 'rgba(95, 167, 119, 0.15)';

        let chartData = d.trend[range] || [];
        
        trendChartObj.config.type = (range === '365') ? 'bar' : 'line';
        trendChartObj.data.labels = chartData.map(item => item[labelKey] || 'N/A');
        trendChartObj.data.datasets = [{ 
            label: datasetLabel, 
            data: chartData.map(item => item[dataKey] || 0), 
            borderColor: color, 
            backgroundColor: (range === '365') ? color : bgColor, 
            fill: true, 
            borderWidth: 2.5 
        }];
        trendChartObj.update();
    }

    function updateKPI(id, title, val, sub) {
        document.getElementById(id+'-title').innerText = title; 
        document.getElementById(id+'-val').innerText = val;
        document.getElementById(id+'-sub').innerText = sub;
    }

    /* R-01: เรนเดอร์ข้อมูลตารางและกราฟวงกลมตาม Dropdown ที่ผู้ใช้เลือก */
    function renderDetail() {
        selectedSliceIndex = null;
        hoveredSliceIndex = null;

        const selector = document.getElementById('detailSelector');
        if (!selector) return;
        currentDetailKey = selector.value;
        const detail = dbData[currentDashType]?.details?.[currentDetailKey];
        if (!detail) return;

        document.getElementById('table-title').innerText = detail.tableTitle;
        document.getElementById('table-col').innerText = detail.col;
        document.getElementById('table-col-qty').innerText = detail.colQty;
        document.getElementById('pie-title').innerText = detail.pieTitle;

        // สลับการแสดงช่องค้นหาเฉพาะกรณีที่ searchable: true (เช่น ภาระงานสัตวแพทย์)
        const searchWrap = document.getElementById('vetSearchWrap');
        if (searchWrap) {
            searchWrap.style.display = detail.searchable ? 'block' : 'none';
        }

        let rows = detail.data || [];
        if (detail.searchable) {
            const q = (document.getElementById('vetSearch')?.value || '').trim().toLowerCase();
            const clearBtn = document.getElementById('vetSearchClear');
            if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';
            if (q) {
                rows = rows.filter(item => (item[detail.nameKey] || '').toLowerCase().includes(q));
            }
        }

        renderTable(rows, detail.nameKey, detail.valKey, detail.prefix, detail.searchable);
        renderPie(rows, detail.nameKey, detail.valKey, detail.prefix);
    }

    function clearVetSearch() {
        const input = document.getElementById('vetSearch');
        if (input) input.value = '';
        renderDetail();
    }

    function highlightSlice(idx) {
        hoveredSliceIndex = idx;
        if (pieChartObj) pieChartObj.draw();
    }

    function unhighlightSlice() {
        hoveredSliceIndex = null;
        if (pieChartObj) pieChartObj.draw();
    }

    function toggleSlice(idx) {
        selectedSliceIndex = (selectedSliceIndex === idx) ? null : idx;
        if (pieChartObj) pieChartObj.draw();
    }

    function renderTable(rows, nameKey, valKey, prefix, searchable) {
        const tbody = document.getElementById('table-body');
        if (!tbody) return;

        if (!rows || rows.length === 0) {
            const q = document.getElementById('vetSearch')?.value.trim();
            if (searchable && q) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:var(--color-black-1000); opacity:0.6; padding:var(--space-24);">ไม่พบสัตวแพทย์ที่ค้นหา (ลองตรวจสอบการสะกดชื่อ หรือกดล้างการค้นหา)</td></tr>';
            } else {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:var(--color-black-1000); opacity:0.6; padding:var(--space-24);">ไม่พบข้อมูลในช่วงเวลาที่เลือก กรุณาเลือกช่วงเวลาใหม่เพื่อดูสถิติ</td></tr>';
            }
            return;
        }

        let html = '';
        rows.forEach((item, index) => {
            const rank = index + 1;
            const icon = rank <= 3 ? '🏆' : `#${rank}`;
            let avatar = '';
            if (searchable && item[nameKey]) {
                const initials = item[nameKey].toString().trim().split(/\s+/).map(s => s[0]).slice(0, 2).join('');
                avatar = `<span style="display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:var(--color-primary); color:var(--color-white); font-size:11px; font-weight:bold; margin-right:6px;">${initials || '-'}</span>`;
            }
            const rawVal = Number(item[valKey] || 0);
            const valFormatted = prefix ? prefix + rawVal.toLocaleString() : rawVal.toLocaleString();
            html += `<tr onmouseenter="highlightSlice(${index})" onmouseleave="unhighlightSlice()" onclick="toggleSlice(${index})" style="cursor: pointer;" title="คลิกเพื่อดูสถิติหัวข้อนี้">
                <td style="font-weight: 600;">${icon}</td>
                <td><div style="display:flex; align-items:center;">${avatar}<strong>${item[nameKey] ?? 'ไม่ระบุ'}</strong></div></td>
                <td class="qty" style="text-align: right; font-weight: 600;">${valFormatted}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    function renderPie(rows, labelKey, dataKey, prefix) {
        if (!pieChartObj) return;

        pieChartObj.detailPrefix = prefix || '';

        if (!rows || rows.length === 0) {
            pieChartObj.data.labels = ['ไม่มีข้อมูล'];
            pieChartObj.data.datasets = [{ data: [1], backgroundColor: [tokenColors.gray], borderWidth: 0 }];
            pieChartObj.update();
            return;
        }

        // 1. เรียงลำดับจากค่ามากไปหาน้อย
        const sorted = [...rows].sort((a, b) => Number(b[dataKey] || 0) - Number(a[dataKey] || 0));

        // 2. แสดง Top 5 และรวมข้อมูลที่เกินเป็น 'อื่น ๆ'
        let processed = [];
        if (sorted.length > 5) {
            const top5 = sorted.slice(0, 5);
            const others = sorted.slice(5);
            const otherSum = others.reduce((sum, item) => sum + Number(item[dataKey] || 0), 0);
            processed = [...top5];
            if (otherSum > 0) {
                let otherItem = {};
                otherItem[labelKey] = 'อื่น ๆ';
                otherItem[dataKey] = otherSum;
                processed.push(otherItem);
            }
        } else {
            processed = sorted;
        }

        const colorsList = [tokenColors.primary, tokenColors.secondary, tokenColors.success, tokenColors.warning, tokenColors.gray];
        pieChartObj.data.labels = processed.map(item => item[labelKey] || 'ไม่ระบุ');
        pieChartObj.data.datasets = [{ 
            data: processed.map(item => Number(item[dataKey] || 0)), 
            backgroundColor: processed.map((item, idx) => item[labelKey] === 'อื่น ๆ' ? tokenColors.gray : colorsList[idx % colorsList.length]), 
            borderWidth: 2,
            borderColor: tokenColors.white
        }]; 
        pieChartObj.update();
    }
</script>

</body>
</html>
