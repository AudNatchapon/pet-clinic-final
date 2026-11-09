<?php 
require_once '../config/db.php';

// รับค่าการค้นหา
$search = $_GET['search'] ?? '';

// ดึงข้อมูลสัตวแพทย์
$sql = "SELECT * FROM doctor";
$params = [];

if (!empty($search)) {
    // แก้ไข: เปลี่ยนให้ตรงกับชื่อคอลัมน์ใน DB (phoneNumber)
    $sql .= " WHERE name LIKE ? OR phoneNumber LIKE ?";
    $safe_search = "%$search%";
    $params = [$safe_search, $safe_search];
}
$sql .= " ORDER BY doctor_ID DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการข้อมูลสัตวแพทย์ | PetCare V2</title>
    <link rel="stylesheet" href="../css/petcare-token.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: var(--font-family-base); background-color: var(--color-background); color: var(--color-black-1000); }
        h2 { font-family: var(--font-family-base); font-size: var(--font-size-lg); }
        .list-card { border: 1px solid var(--color-gray-400); border-radius: var(--radius-md); box-shadow: 0 4px 14px rgba(0,0,0,0.06); background: var(--color-surface); }
        .btn-action { width: 36px; height: 36px; border-radius: var(--radius-md); display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--color-gray-400); transition: 0.2s; }
        .btn-action:hover { transform: translateY(-2px); }
        .btn-primary { background-color: var(--button-primary-bg) !important; border-color: var(--button-primary-bg) !important; color: var(--button-primary-text) !important; border-radius: var(--button-radius) !important; }
        .btn-success { background-color: var(--color-success) !important; border-color: var(--color-success) !important; color: var(--color-white) !important; border-radius: var(--button-radius) !important; }
        .btn { border-radius: var(--button-radius) !important; }
        .text-primary { color: var(--color-primary) !important; }
        .form-control { border-radius: var(--radius-md) !important; border-color: var(--input-border) !important; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-person-badge-fill text-primary me-2"></i>จัดการข้อมูลสัตวแพทย์</h2>
            <p class="text-muted">เพิ่ม ค้นหา แก้ไข และลบรายชื่อคุณหมอในระบบ</p>
        </div>
        <a href="../index.php" class="btn btn-outline-secondary rounded-pill px-4">กลับหน้าหลัก</a>
    </div>

    <div class="card list-card p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-9">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control rounded-pill px-4" placeholder="ค้นหาชื่อคุณหมอ, เบอร์โทรศัพท์..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4">ค้นหา</button>
                </form>
            </div>
            <div class="col-md-3 text-md-end">
                <button class="btn btn-success rounded-pill fw-bold w-100" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle me-1"></i> เพิ่มสัตวแพทย์
                </button>
            </div>
        </div>
    </div>

    <div class="card list-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>ชื่อ-นามสกุล (สพ. / น.สพ. / สพ.ญ.)</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($doctors)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">ไม่พบข้อมูล</td></tr>
                    <?php else: ?>
                        <?php foreach ($doctors as $row): ?>
                        <tr id="row-<?= $row['doctor_ID'] ?>">
                            <td class="text-muted">#<?= $row['doctor_ID'] ?></td>
                            <td class="fw-bold text-primary">
                                <i class="bi bi-person-circle text-muted me-2"></i><?= htmlspecialchars($row['name']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['phoneNumber'] ?? '-') ?></td>
                            <td class="text-center">
                                <button class="btn-action bg-warning text-dark me-1 btn-edit" 
                                        data-id="<?= $row['doctor_ID'] ?>"
                                        data-name="<?= htmlspecialchars($row['name']) ?>"
                                        data-phone="<?= htmlspecialchars($row['phoneNumber'] ?? '') ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn-action bg-danger text-white btn-delete" data-id="<?= $row['doctor_ID'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="addForm" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-success"><i class="bi bi-person-plus-fill me-2"></i>เพิ่มสัตวแพทย์ใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">ชื่อ-นามสกุล (รวมคำนำหน้า)</label>
                    <input type="text" name="name" class="form-control rounded-3" placeholder="เช่น สพ.ญ. สมหญิง ใจดี" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                    <input type="text" name="phonenumber" class="form-control rounded-3" maxlength="10">
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editForm" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-warning"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลสัตวแพทย์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="doctor_ID" id="edit_id">
                <div class="mb-3">
                    <label class="form-label fw-bold small">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                    <input type="text" name="phonenumber" id="edit_phone" class="form-control rounded-3" maxlength="10">
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // 1. เพิ่มคุณหมอใหม่
    $('#addForm').submit(function(e) {
        e.preventDefault();
        $.post('../api/add_doctor.php', $(this).serialize(), function(res) {
            if(res.status === 'success') { location.reload(); } 
            else { alert('Error: ' + res.message); }
        }, 'json');
    });

    // 2. ดึงข้อมูลใส่ Modal แก้ไข
    $('.btn-edit').click(function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_phone').val($(this).data('phone'));
        $('#editModal').modal('show');
    });

    // 3. บันทึกการแก้ไข
    $('#editForm').submit(function(e) {
        e.preventDefault();
        $.post('../api/update_doctor.php', $(this).serialize(), function(res) {
            if(res.status === 'success') { location.reload(); } 
            else { alert('Error: ' + res.message); }
        }, 'json');
    });

    // 4. ระบบลบ
    $('.btn-delete').click(function() {
        let id = $(this).data('id');
        if(confirm('🚨 คำเตือน: คุณต้องการลบรายชื่อคุณหมอท่านนี้ใช่หรือไม่?\n(หากหมอท่านนี้มีประวัติการรักษาค้างอยู่ จะไม่สามารถลบได้)')) {
            $.post('../api/delete_doctor.php', { id: id }, function(res) {
                if(res.status === 'success') {
                    $('#row-' + id).fadeOut(function() { $(this).remove(); });
                } else {
                    alert('Error: ไม่สามารถลบได้ เนื่องจากหมอท่านนี้มีคิวรักษาหรือประวัติผูกอยู่ในระบบ');
                }
            }, 'json');
        }
    });
});
</script>
</body>
</html>