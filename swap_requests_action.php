<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['request_id'], $_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$request_id = (int)$_POST['request_id'];
$action = $_POST['action'];

if (!in_array($action, ['accept', 'reject'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}

$status = $action === 'accept' ? 'accepted' : 'rejected';

// Update the swap_requests table
$stmt = $mysqli->prepare("UPDATE swap_requests SET status=?, chat_enabled=? WHERE id=?");
$chat_enabled = ($status === 'accepted') ? 1 : 0;
$stmt->bind_param("sii", $status, $chat_enabled, $request_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Request updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update request']);
}

$stmt->close();
?>
