<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pet_id = $_POST['pet_ID'];
        $name = $_POST['name'];
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];

        // คำนวณอายุใหม่จาก dob ที่รับมา
        $calculated_age = '0 เดือน';
        if (!empty($dob)) {
            $birthDate = new DateTime($dob);
            $today = new DateTime('today');
            $diff = $birthDate->diff($today);
            
            if ($diff->y > 0) {
                $calculated_age = $diff->y . " ปี " . $diff->m . " เดือน"; 
            } else {
                $calculated_age = $diff->m . " เดือน";
            }
        }

        // อัปเดตข้อมูลลง Database
        $sql = "UPDATE pet SET name = ?, dob = ?, age = ?, gender = ? WHERE pet_ID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $dob, $calculated_age, $gender, $pet_id]);
        
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}