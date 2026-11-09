<?php 
require_once '../config/db.php';
require_once '../functions/common_functions.php';

$next_app_id = getNextID($pdo, 'appointments', 'appointments_ID');
// ดึงรายชื่อหมอมาใส่ใน Dropdown
$doctors = $pdo->query("SELECT * FROM doctor ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สร้างนัดหมายใหม่ | Appointment</title>
    <link rel="stylesheet" href="../css/petcare-token.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: var(--font-family-base); background-color: var(--color-background); color: var(--color-black-1000); }
        .app-card { border: 1px solid var(--color-gray-400); border-radius: var(--radius-md); box-shadow: 0 4px 14px rgba(0,0,0,0.06); overflow: hidden; background: var(--color-surface); }
        .side-info { background: var(--color-primary); color: var(--color-white); padding: var(--space-lg); }
        .form-section { background: var(--color-surface); padding: var(--space-lg); }
        .form-label { font-weight: 600; font-size: var(--font-size-sm); color: var(--color-black-1000); }
        .form-control, .form-select { border-radius: var(--radius-md); padding: var(--Grid-8) var(--space-md); border: 1px solid var(--input-border); background: var(--color-surface); font-size: var(--font-size-sm); }
        .autocomplete-results { position: absolute; width: 100%; z-index: 1000; border-radius: var(--radius-md); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border: 1px solid var(--color-gray-400); }
        .btn-primary { background-color: var(--button-primary-bg) !important; border-color: var(--button-primary-bg) !important; color: var(--button-primary-text) !important; border-radius: var(--button-radius) !important; }
        .btn { border-radius: var(--button-radius) !important; }
        .text-primary { color: var(--color-primary) !important; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <a href="../index.php" class="text-decoration-none text-muted fw-bold"><i class="bi bi-arrow-left me-2"></i>กลับหน้าหลัก</a>
        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-<?= $_GET['status'] == 'success' ? 'success' : 'danger' ?> py-2 border-0 rounded-4 shadow-sm mb-0">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card app-card shadow-lg border-0">
        <div class="row g-0">
            <div class="col-lg-4 side-info d-none d-lg-flex flex-column justify-content-center text-center">
                <div class="mb-4"><i class="bi bi-calendar-check fs-1"></i></div>
                <h2 class="fw-bold mb-3">สร้างนัดหมาย</h2>
                <p class="opacity-75">ค้นหาสัตว์เลี้ยงเพื่อลงคิวตรวจและระบุสัตวแพทย์ผู้รับผิดชอบ</p>
            </div>

            <div class="col-lg-8 form-section">
                <form action="../api/save_appointment.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-primary">รหัสนัดหมาย (Auto)</label>
                            <input type="text" class="form-control bg-light" value="<?= $next_app_id ?>" readonly>
                        </div>
                        <div class="col-md-8 position-relative">
                            <label class="form-label">ค้นหาสัตว์เลี้ยง (ระบุชื่อสัตว์หรือชื่อเจ้าของ)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" id="petSearch" class="form-control" placeholder="พิมพ์เพื่อค้นหา..." autocomplete="off" required>
                            </div>
                            <div id="petResults" class="list-group autocomplete-results" style="display:none;"></div>
                            <input type="hidden" name="pet_ID" id="pet_id_hidden" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">สัตวแพทย์ผู้ตรวจ</label>
                            <select name="doctor_ID" class="form-select" required>
                                <option value="">-- เลือกสัตวแพทย์ --</option>
                                <?php foreach($doctors as $d): ?>
                                    <option value="<?= $d['doctor_ID'] ?>">น.สพ. <?= $d['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">วันที่นัดหมาย</label>
                            <input type="date" name="Date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">อาการเบื้องต้น / เหตุผลที่มา</label>
                            <textarea name="appointment_detail" class="form-control" rows="4" placeholder="เช่น ซึม เบื่ออาหาร หรือนัดฉีดวัคซีน..." required></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-5 rounded-4 fw-bold py-3 shadow">ยืนยันการนัดหมาย / ลงคิว</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#petSearch').on('keyup', function() {
        let q = $(this).val();
        if(q.length >= 2) {
            $.get('../api/search_pet_owner.php', { q: q }, function(data) {
                let json = JSON.parse(data);
                let html = '';
                if(json.length > 0) {
                    json.forEach(item => {
                        html += `<a href="#" class="list-group-item list-group-item-action select-pet" 
                                    data-id="${item.pet_ID}" 
                                    data-display="${item.pet_name} (เจ้าของ: ${item.owner_name})">
                                    <strong>🐾 ${item.pet_name}</strong> 
                                    <span class="small text-muted float-end">เจ้าของ: ${item.owner_name}</span>
                                 </a>`;
                    });
                    $('#petResults').html(html).fadeIn();
                } else {
                    $('#petResults').html('<div class="list-group-item text-muted">ไม่พบข้อมูลสัตว์เลี้ยง</div>').fadeIn();
                }
            });
        } else { $('#petResults').fadeOut(); }
    });

    $(document).on('click', '.select-pet', function(e) {
        e.preventDefault();
        $('#petSearch').val($(this).data('display'));
        $('#pet_id_hidden').val($(this).data('id'));
        $('#petResults').fadeOut();
    });
});
</script>
</body>
</html>