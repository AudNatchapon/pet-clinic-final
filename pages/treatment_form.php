<?php 
require_once '../config/db.php';

// 1. ดึงรายการนัดหมายที่ "ยังไม่ได้รักษา" (ไม่มีในตาราง diagnoses)
// โดย Join ข้อมูล Pet, Owner และ Doctor มาแสดงพร้อมกัน
$sql_queue = "SELECT a.appointments_ID, a.Date, a.appointment_detail, 
                     p.name AS pet_name, o.name AS owner_name, 
                     d.name AS doctor_name
              FROM appointments a
              JOIN pet p ON a.pet_ID = p.pet_ID
              JOIN owner o ON p.owner_ID = o.owner_ID
              JOIN doctor d ON a.doctor_ID = d.doctor_ID
              LEFT JOIN diagnoses diag ON a.appointments_ID = diag.appointment_ID
              WHERE diag.diagnoses_ID IS NULL
              ORDER BY a.Date ASC, a.appointments_ID ASC";

$queue = $pdo->query($sql_queue)->fetchAll();

// 2. ดึงรายชื่อยาทั้งหมดเตรียมไว้
$medications = $pdo->query("SELECT * FROM medication ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ห้องตรวจแพทย์ | PetCare Queue</title>
    <link rel="stylesheet" href="../css/petcare-token.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: var(--font-family-base); background-color: var(--color-background); color: var(--color-black-1000); }
        .queue-card { border: 1px solid var(--color-gray-400); border-radius: var(--radius-md); transition: 0.2s; cursor: pointer; border-left: 4px solid var(--color-primary); background: var(--color-surface); }
        .queue-card:hover { transform: translateX(4px); border-left-color: var(--color-primary); background-color: var(--tint-primary); }
        .doctor-badge { background-color: var(--tint-primary); color: var(--color-primary); font-weight: 600; padding: calc(var(--Grid-8) / 2) var(--space-md); border-radius: var(--chip-radius); font-size: var(--font-size-sm); }
        .treatment-section { display: none; position: sticky; top: var(--space-lg); }
        .med-row { background: var(--tint-background-strong); border-radius: var(--radius-md); padding: var(--space-md); margin-bottom: var(--Grid-8); border: 1px solid var(--color-gray-400); }
        
        /* สไตล์สำหรับช่องค้นหา */
        .search-box {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            padding: var(--Grid-8) var(--space-md);
            border: 1px solid var(--input-border);
        }
        .search-box input {
            border: none;
            box-shadow: none;
            background: transparent;
        }
        .search-box input:focus { outline: none; box-shadow: none; }
        .btn-primary { background-color: var(--button-primary-bg) !important; border-color: var(--button-primary-bg) !important; color: var(--button-primary-text) !important; }
        .btn-success { background-color: var(--color-success) !important; border-color: var(--color-success) !important; color: var(--color-white) !important; }
        .text-primary { color: var(--color-primary) !important; }
        .bg-primary { background-color: var(--color-primary) !important; }
        .card { border-radius: var(--radius-md) !important; border-color: var(--color-gray-400) !important; background-color: var(--color-surface) !important; }
        .btn { border-radius: var(--button-radius) !important; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold mb-0">📋 คิวตรวจที่รออยู่</h3>
                <a href="../index.php" class="btn btn-sm btn-outline-secondary rounded-pill">กลับหน้าหลัก</a>
            </div>

            <div class="search-box d-flex align-items-center mb-4">
                <i class="bi bi-search text-muted me-2"></i>
                <input type="text" id="queueSearch" class="form-control" placeholder="ค้นหาชื่อสัตว์, เจ้าของ, หรือหมอ...">
            </div>

            <?php if (empty($queue)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-check-circle text-success display-4"></i>
                    <p class="mt-3 text-muted">ตรวจครบทุกเคสแล้ว!</p>
                </div>
            <?php else: ?>
                <div class="queue-container" id="queueList">
                    <?php foreach ($queue as $q): ?>
                        <div class="card queue-card p-3 mb-3 shadow-sm select-case" 
                             data-id="<?= $q['appointments_ID'] ?>"
                             data-pet="<?= htmlspecialchars($q['pet_name']) ?>"
                             data-owner="<?= htmlspecialchars($q['owner_name']) ?>"
                             data-detail="<?= htmlspecialchars($q['appointment_detail']) ?>"
                             data-doctor="<?= htmlspecialchars($q['doctor_name']) ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-primary mb-2">ID #<?= $q['appointments_ID'] ?></span>
                                    <h5 class="fw-bold mb-1 pet-name">🐾 <?= htmlspecialchars($q['pet_name']) ?></h5>
                                    <p class="small text-muted mb-2 owner-name">เจ้าของ: <?= htmlspecialchars($q['owner_name']) ?></p>
                                    <div class="doctor-badge doctor-name">
                                        <i class="bi bi-person-fill me-1"></i> น.สพ. <?= htmlspecialchars($q['doctor_name']) ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block"><?= date('d/m/Y', strtotime($q['Date'])) ?></small>
                                    <i class="bi bi-chevron-right text-muted mt-2 d-inline-block"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="noResultMsg" class="text-center py-4 text-muted" style="display: none;">
                    <i class="bi bi-search display-6 opacity-25"></i>
                    <p class="mt-2">ไม่พบคิวที่ตรงกับการค้นหา</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-7">
            <div id="welcomeView" class="text-center py-5 mt-5">
                <i class="bi bi-arrow-left-circle display-1 text-muted opacity-25"></i>
                <h4 class="mt-3 text-muted">กรุณาเลือกเคสจากรายการทางซ้าย <br>เพื่อเริ่มบันทึกการรักษา</h4>
            </div>

            <div id="treatmentFormArea" class="treatment-section">
                <div class="card border-0 shadow-lg p-4 p-md-5 rounded-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-primary mb-0"><i class="bi bi-stethoscope me-2"></i>บันทึกการตรวจ</h4>
                        <span class="badge bg-light text-dark p-2" id="target_id_display">ID: #0</span>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 mb-4">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">คนไข้:</small>
                                <strong id="target_pet_name">-</strong>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">แพทย์ผู้รับผิดชอบ:</small>
                                <strong id="target_doctor_name">-</strong>
                            </div>
                            <div class="col-12 mt-2 pt-2 border-top border-info border-opacity-10">
                                <small class="text-muted d-block">อาการเบื้องต้น:</small>
                                <span id="target_symptom">-</span>
                            </div>
                        </div>
                    </div>

                    <form action="../api/save_treatment.php" method="POST">
                        <input type="hidden" name="appointment_ID" id="hidden_app_id">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">ผลการวินิจฉัย (Diagnosis)</label>
                            <textarea name="diagnosisDescription" class="form-control rounded-4" rows="4" required placeholder="บันทึกผลการตรวจร่างกายและผลวินิจฉัย..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">รายการสั่งยา (Medications)</label>
                            <div id="medicationContainer"></div>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill mt-2" id="btnAddMed">
                                <i class="bi bi-plus-circle me-1"></i> เพิ่มรายการยา
                            </button>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill shadow-lg py-3 fw-bold mt-3">
                            บันทึกและส่งคิวไปการเงิน <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="medTemplate">
    <div class="med-row d-flex align-items-center gap-2 animate__animated animate__fadeIn">
        <div class="flex-grow-1">
            <select name="medicine_ID[]" class="form-select border-0 bg-transparent" required>
                <option value="">-- เลือกยา --</option>
                <?php foreach($medications as $med): ?>
                    <option value="<?= $med['medicine_ID'] ?>"><?= htmlspecialchars($med['name']) ?> (฿<?= number_format($med['price'], 2) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="button" class="btn btn-link text-danger remove-med p-0"><i class="bi bi-x-circle-fill fs-5"></i></button>
    </div>
</template>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 🌟 ระบบค้นหาคิวตรวจ (Live Search) 🌟
    $("#queueSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        var visibleCount = 0;
        
        $(".queue-card").filter(function() {
            // เช็คว่าข้อความในการ์ดตรงกับที่พิมพ์ไหม
            var isMatch = $(this).text().toLowerCase().indexOf(value) > -1;
            $(this).toggle(isMatch);
            if(isMatch) visibleCount++;
        });

        // แสดงข้อความหาไม่เจอ ถ้าไม่มีคิวไหนตรงเลย
        if(visibleCount === 0 && value !== "") {
            $("#noResultMsg").show();
        } else {
            $("#noResultMsg").hide();
        }
    });

    // 1. เมื่อคลิกเลือกเคสจากลิสต์
    $('.select-case').click(function() {
        // อัปเดตข้อมูลในฟอร์ม
        let id = $(this).data('id');
        $('#hidden_app_id').val(id);
        $('#target_id_display').text('ID: #' + id);
        $('#target_pet_name').text('🐾 ' + $(this).data('pet'));
        $('#target_doctor_name').text($(this).data('doctor'));
        $('#target_symptom').text($(this).data('detail'));

        // แสดงฟอร์ม
        $('#welcomeView').hide();
        $('#treatmentFormArea').fadeIn();
        
        // เคลียร์รายการยาเก่า (ถ้ามี)
        $('#medicationContainer').empty();
        
        // เลื่อนหน้าจอไปที่ฟอร์ม (สำหรับมือถือ)
        if ($(window).width() < 992) {
            $('html, body').animate({ scrollTop: $('#treatmentFormArea').offset().top }, 500);
        }
    });

    // 2. เพิ่ม/ลบรายการยา
    $('#btnAddMed').click(function() {
        let template = document.getElementById('medTemplate').content.cloneNode(true);
        $('#medicationContainer').append(template);
    });

    $(document).on('click', '.remove-med', function() {
        $(this).closest('.med-row').remove();
    });
});
</script>
</body>
</html>