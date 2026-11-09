<?php
// ฟังก์ชันสำหรับดึงรหัสถัดไป (Auto Increment Next ID)
function getNextID($pdo, $table, $column) {
    $sql = "SELECT MAX($column) AS max_id FROM $table";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $next_id = $row['max_id'] ? $row['max_id'] + 1 : 1;
    return $next_id;
}
?>