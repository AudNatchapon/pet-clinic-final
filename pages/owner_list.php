<?php 
require_once '../config/db.php';

// รับค่าการค้นหา
$search = $_GET['search'] ?? '';

// ดึงข้อมูลเจ้าของ
$sql = "SELECT * FROM owner";
$params = []; // สร้าง Array เก็บตัวแปร

if (!empty($search)) {
    // ใช้ ? แทนตัวแปร 3 จุด (ชื่อ, เบอร์โทร, อีเมล)
    $sql .= " WHERE name LIKE ? OR phonenumber LIKE ? OR email LIKE ?";
    $safe_search = "%$search%";
    // โยนค่าเข้าไป 3 ตัวให้ครบตามจำนวน ?
    $params = [$safe_search, $safe_search, $safe_search];
}
$sql .= " ORDER BY owner_ID DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params); // ให้ PDO จัดการแมปค่าให้เอง
$owners = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการข้อมูลเจ้าของ | PetCare V2</title>
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
        .btn { border-radius: var(--button-radius) !important; }
        .text-primary { color: var(--color-primary) !important; }
        .form-control { border-radius: var(--radius-md) !important; border-color: var(--input-border) !important; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-people-fill text-primary me-2"></i>จัดการข้อมูลเจ้าของ</h2>
            <p class="text-muted">ค้นหา แก้ไข และลบข้อมูลเจ้าของสัตว์เลี้ยง</p>
        </div>
        <a href="../index.php" class="btn btn-outline-secondary rounded-pill px-4">กลับหน้าหลัก</a>
    </div>

    <div class="card list-card p-4 mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control rounded-pill px-4" placeholder="ค้นหาชื่อ, เบอร์โทรศัพท์, หรืออีเมล..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">ค้นหา</button>
            </div>
        </form>
    </div>

    <div class="card list-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>อีเมล</th>
                        <th>ที่อยู่</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($owners)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูล</td></tr>
                    <?php else: ?>
                        <?php foreach ($owners as $row): ?>
                        <tr id="row-<?= $row['owner_ID'] ?>">
                            <td class="text-muted">#<?= $row['owner_ID'] ?></td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['phonenumber']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><div class="text-truncate" style="max-width: 250px;"><?= htmlspecialchars($row['address']) ?></div></td>
                            <td class="text-center">
                                <button class="btn-action bg-warning text-dark me-1 btn-edit" 
                                        data-id="<?= $row['owner_ID'] ?>"
                                        data-name="<?= htmlspecialchars($row['name']) ?>"
                                        data-phone="<?= htmlspecialchars($row['phonenumber']) ?>"
                                        data-email="<?= htmlspecialchars($row['email']) ?>"
                                        data-address="<?= htmlspecialchars($row['address']) ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn-action bg-danger text-white btn-delete" data-id="<?= $row['owner_ID'] ?>">
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

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editForm" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">แก้ไขข้อมูลเจ้าของ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="owner_ID" id="edit_id">
                <div class="mb-3">
                    <label class="form-label fw-bold small">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                        <input type="text" name="phonenumber" id="edit_phone" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">อีเมล</label>
                        <input type="email" name="email" id="edit_email" class="form-control rounded-3">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">ที่อยู่</label>
                    <textarea name="address" id="edit_address" class="form-control rounded-3" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // 1. นำข้อมูลใส่ Modal เมื่อกดปุ่มแก้ไข
    $('.btn-edit').click(function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_phone').val($(this).data('phone'));
        $('#edit_email').val($(this).data('email'));
        $('#edit_address').val($(this).data('address'));
        $('#editModal').modal('show');
    });

    // 2. บันทึกการแก้ไข (AJAX)
    $('#editForm').submit(function(e) {
        e.preventDefault();
        $.post('../api/update_owner.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                location.reload(); // รีเฟรชหน้าจอเมื่อสำเร็จ
            } else {
                alert('Error: ' + res.message);
            }
        }, 'json');
    });

    // 3. ระบบลบข้อมูล
    $('.btn-delete').click(function() {
        let id = $(this).data('id');
        if(confirm('🚨 คำเตือน: คุณต้องการลบข้อมูลเจ้าของคนนี้ใช่หรือไม่? \n(หากลบ สัตว์เลี้ยงที่เกี่ยวข้องอาจถูกลบไปด้วย)')) {
            $.post('../api/delete_owner.php', { id: id }, function(res) {
                if(res.status === 'success') {
                    $('#row-' + id).fadeOut(function() { $(this).remove(); });
                } else {
                    alert('Error: ไม่สามารถลบได้ (อาจมีข้อมูลสัตว์เลี้ยงที่ผูกอยู่)');
                }
            }, 'json');
        }
    });
});
</script>
</body>
</html> 