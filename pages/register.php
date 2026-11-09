<?php 
require_once '../config/db.php';
require_once '../functions/common_functions.php';

// ดึงรหัสถัดไปมาแสดง
$next_owner_id = getNextID($pdo, 'owner', 'owner_ID');
$next_pet_id   = getNextID($pdo, 'pet', 'pet_ID');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลงทะเบียนใหม่ | Register</title>
    <link rel="stylesheet" href="../css/petcare-token.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: var(--font-family-base); background-color: var(--color-background); color: var(--color-black-1000); }
        .reg-card { border: 1px solid var(--color-gray-400); border-radius: var(--radius-md); box-shadow: 0 4px 14px rgba(0,0,0,0.06); overflow: hidden; background: var(--color-surface); }
        .side-info { background: var(--color-primary); color: var(--color-white); padding: var(--space-lg); }
        .form-section { background: var(--color-surface); padding: var(--space-lg); }
        .nav-pills .nav-link { border-radius: var(--chip-radius); padding: var(--Grid-8) var(--space-md); font-weight: 600; color: var(--color-black-1000); opacity: 0.7; }
        .nav-pills .nav-link.active { background-color: var(--color-primary); color: var(--color-white); box-shadow: 0 4px 12px rgba(91,79,224, 0.3); opacity: 1; }
        .form-label { font-weight: 600; font-size: var(--font-size-sm); color: var(--color-black-1000); text-transform: uppercase; }
        .form-control, .form-select { border-radius: var(--radius-md); padding: var(--Grid-8) var(--space-md); border: 1px solid var(--input-border); background-color: var(--color-surface); font-size: var(--font-size-sm); }
        .autocomplete-results { position: absolute; width: 100%; z-index: 1000; border-radius: var(--radius-md); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border: 1px solid var(--color-gray-400); }
        .btn-primary { background-color: var(--button-primary-bg) !important; border-color: var(--button-primary-bg) !important; color: var(--button-primary-text) !important; border-radius: var(--button-radius) !important; }
        .btn { border-radius: var(--button-radius) !important; }
        .text-primary { color: var(--color-primary) !important; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <a href="../index.php" class="text-decoration-none text-muted fw-bold">
            <i class="bi bi-arrow-left me-2"></i>กลับหน้าหลัก
        </a>

        <?php if (isset($_GET['status'])): ?>
            <div id="status-alert" class="alert alert-<?= ($_GET['status'] == 'success') ? 'success' : 'danger' ?> alert-dismissible fade show border-0 shadow-sm rounded-4 mb-0 py-2" role="alert" style="min-width: 300px;">
                <div class="d-flex align-items-center">
                    <?php if ($_GET['status'] == 'success'): ?>
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <?php else: ?>
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <?php endif; ?>
                    <div>
                        <strong class="me-1"><?= ($_GET['status'] == 'success') ? 'สำเร็จ!' : 'ข้อผิดพลาด!' ?></strong>
                        <span class="small"><?= htmlspecialchars($_GET['msg']) ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="card reg-card shadow-lg border-0">
        <div class="row g-0">
            <div class="col-lg-4 side-info d-none d-lg-flex flex-column justify-content-center text-center">
                <div class="mb-4"><i class="bi bi-heart-pulse fs-1"></i></div>
                <h2 class="fw-bold mb-3">Register</h2>
                <p class="opacity-75 mb-5">เพิ่มข้อมูลเข้าสู่ระบบเพื่อเริ่มบันทึกประวัติการรักษา</p>
                <div class="text-start small px-3">
                    <p><i class="bi bi-check-circle-fill me-2"></i> บันทึกข้อมูลเจ้าของก่อนเสมอ</p>
                    <p><i class="bi bi-check-circle-fill me-2"></i> ค้นหาสายพันธุ์จากฐานข้อมูลกลาง</p>
                </div>
            </div>

            <div class="col-lg-8 form-section">
                <ul class="nav nav-pills mb-5 justify-content-center" id="pills-tab">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#owner-pane"><i class="bi bi-person-fill me-2"></i>1. เจ้าของ</button>
                    </li>
                    <li class="nav-item ms-2">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pet-pane"><i class="bi bi-paw-fill me-2"></i>2. สัตว์เลี้ยง</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="owner-pane">
                        <form action="../api/register_owner.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-primary">รหัสเจ้าของ (Auto)</label>
                                    <input type="text" class="form-control" value="<?= $next_owner_id ?>" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">ชื่อ-นามสกุล</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">เบอร์โทรศัพท์</label>
                                    <input type="text" name="phonenumber" class="form-control" maxlength="10">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">อีเมล</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">ที่อยู่</label>
                                    <textarea name="address" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 mt-5 rounded-4 fw-bold py-3 shadow">บันทึกข้อมูลเจ้าของ</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="pet-pane">
                        <form action="../api/register_pet.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-success">รหัสสัตว์ (Auto)</label>
                                    <input type="text" class="form-control" value="<?= $next_pet_id ?>" readonly>
                                </div>
                                <div class="col-md-8 position-relative">
                                    <label class="form-label">ค้นหาเจ้าของ (Owner)</label>
                                    <input type="text" id="ownerSearch" class="form-control" placeholder="พิมพ์ชื่อเจ้าของ..." autocomplete="off">
                                    <div id="ownerResults" class="list-group autocomplete-results" style="display:none;"></div>
                                    <input type="hidden" name="owner_ID" id="owner_id_hidden" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">ชื่อสัตว์เลี้ยง</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label class="form-label">สายพันธุ์ (Breed)</label>
                                    <input type="text" id="breedSearch" class="form-control" placeholder="พิมพ์ชื่อสายพันธุ์..." autocomplete="off">
                                    <div id="breedResults" class="list-group autocomplete-results" style="display:none;"></div>
                                    <input type="hidden" name="breed_ID" id="breed_id_hidden" required>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">วันเกิด 🎂</label>
                                    <input type="date" name="dob" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">เพศ</label>
                                    <select name="gender" class="form-select">
                                        <option value="Male">ผู้</option>
                                        <option value="Female">เมีย</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100 mt-5 rounded-4 fw-bold py-3 shadow">บันทึกข้อมูลสัตว์เลี้ยง</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // ระบบค้นหาเจ้าของ
    $('#ownerSearch').on('keyup', function() {
        let q = $(this).val();
        if(q.length >= 2) {
            $.get('../api/search_owner.php', { q: q }, function(data) {
                let json = JSON.parse(data);
                let html = '';
                json.forEach(item => {
                    html += `<a href="#" class="list-group-item list-group-item-action select-owner" data-id="${item.owner_ID}" data-name="${item.name}">${item.name} (${item.phonenumber})</a>`;
                });
                $('#ownerResults').html(html).fadeIn();
            });
        } else { $('#ownerResults').fadeOut(); }
    });

    $(document).on('click', '.select-owner', function(e) {
        e.preventDefault();
        $('#ownerSearch').val($(this).data('name'));
        $('#owner_id_hidden').val($(this).data('id'));
        $('#ownerResults').fadeOut();
    });

    // ระบบค้นหาสายพันธุ์
    $('#breedSearch').on('keyup', function() {
        let q = $(this).val();
        if(q.length >= 1) {
            $.get('../api/search_breed.php', { q: q }, function(data) {
                let json = JSON.parse(data);
                let html = '';
                json.forEach(item => {
                    html += `<a href="#" class="list-group-item list-group-item-action select-breed" data-id="${item.breed_ID}" data-name="${item.breed_detail}">${item.breed_detail}</a>`;
                });
                $('#breedResults').html(html).fadeIn();
            });
        } else { $('#breedResults').fadeOut(); }
    });

    $(document).on('click', '.select-breed', function(e) {
        e.preventDefault();
        $('#breedSearch').val($(this).data('name'));
        $('#breed_id_hidden').val($(this).data('id'));
        $('#breedResults').fadeOut();
    });

    // ซ่อน Alert อัตโนมัติ
    if ($('#status-alert').length) {
        setTimeout(function() {
            $('#status-alert').fadeOut('slow', function() {
                $(this).remove();
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }, 3000); 
    }
});
</script>
</body>
</html>