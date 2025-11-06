<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['message_ids'])) {
    $ids = explode(',', $_POST['message_ids']);
    $ids = array_map('intval', $ids);
    $id_list = implode(',', $ids);

    // Delete only messages sent by this user (safety)
    $query = "DELETE FROM messages WHERE id IN ($id_list) AND sender_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
