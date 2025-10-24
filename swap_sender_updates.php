<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all swap requests where sender has not been notified and status is accept/reject
$stmt = $mysqli->prepare("
    SELECT sr.id, sr.status, s.skill_name, u.name AS receiver_name
    FROM swap_requests sr
    JOIN skills s ON sr.skill_id = s.id
    JOIN users u ON sr.receiver_id = u.id
    WHERE sr.sender_id = ? AND sr.sender_notified = 0 AND sr.status IN ('accept','reject')
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$updates = [];
while($row = $result->fetch_assoc()){
    $updates[] = [
        'id' => $row['id'],
        'status' => $row['status'],
        'skill_name' => $row['skill_name'],
        'receiver_name' => $row['receiver_name']
    ];
}

// Mark these notifications as sent
if(!empty($updates)){
    $ids = array_column($updates, 'id');
    $in  = implode(',', array_map('intval', $ids));
    $mysqli->query("UPDATE swap_requests SET sender_notified=1 WHERE id IN ($in)");
}

echo json_encode(['success'=>true, 'updates'=>$updates]);
?>
