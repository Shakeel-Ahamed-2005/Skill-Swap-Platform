<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Not logged in']);
    exit;
}

if(!isset($_POST['request_id'], $_POST['action'])){
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

$request_id = (int)$_POST['request_id'];
$action = $_POST['action'] === 'accept' ? 'accepted' : ($_POST['action'] === 'reject' ? 'rejected' : null);

if(!$action){
    echo json_encode(['status'=>'error','message'=>'Invalid action']);
    exit;
}

// Update the swap_requests table
$stmt = $mysqli->prepare("UPDATE swap_requests SET status=? WHERE id=?");
$stmt->bind_param("si", $action, $request_id);

if($stmt->execute()){
    echo json_encode(['status'=>'success','message'=>'Status updated']);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to update status']);
}
