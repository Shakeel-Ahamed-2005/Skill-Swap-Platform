<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$sender_id = $_SESSION['user_id'];

if (isset($_POST['skill_id'])) {
    // Skill-based request (from Browse Skills)
    $skill_id = (int)$_POST['skill_id'];

    $query = "SELECT user_id FROM skills WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $skill_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Skill not found']);
        exit;
    }

    $skill = $result->fetch_assoc();
    $receiver_id = $skill['user_id'];
} elseif (isset($_POST['user_id'])) {
    $receiver_id = (int)$_POST['user_id'];

    // Pick first offered skill of the receiver (the one the sender wants)
    $stmt = $mysqli->prepare("SELECT id FROM skills WHERE user_id = ? AND skill_type='offer' ORDER BY created_at LIMIT 1");
    $stmt->bind_param("i", $receiver_id); // <--- Use receiver_id
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $skill_id = $row['id'] ?? 0;

    if ($skill_id === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Receiver has no offered skills']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No skill or user specified']);
    exit;
}

// Insert into swap_requests
$insert = "INSERT INTO swap_requests (sender_id, receiver_id, skill_id, status, created_at) VALUES (?, ?, ?, 'pending', NOW())";
$stmt = $mysqli->prepare($insert);
$stmt->bind_param("iii", $sender_id, $receiver_id, $skill_id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Swap request sent']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send request']);
}
