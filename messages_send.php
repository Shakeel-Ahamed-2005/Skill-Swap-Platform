<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) exit("Unauthorized");

$sender_id = $_SESSION['user_id'];
$receiver_id = (int)$_POST['receiver_id'];
$message = trim($_POST['message']);
$file_path = null;

// Handle file upload if provided
if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'wav', 'doc', 'docx'];
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    if (in_array($file_ext, $allowed_exts)) {
        $filename = uniqid('msg_', true) . '.' . $file_ext;
        $destination = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            $file_path = 'uploads/' . $filename;
        }
    }
}

// Don’t insert empty messages
if ($message === '' && !$file_path) {
    header("Location: messages.php?user=" . $receiver_id);
    exit;
}

// Insert message
$stmt = $mysqli->prepare("INSERT INTO messages (sender_id, receiver_id, message, file_path) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $file_path);
$stmt->execute();
$stmt->close();

header("Location: messages.php?user=" . $receiver_id);
exit;
?>
