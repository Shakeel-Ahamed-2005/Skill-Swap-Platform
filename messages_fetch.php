<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if(!isset($_SESSION['user_id']) || !isset($_GET['receiver_id'])){
  echo json_encode([]);
  exit;
}

$user_id = $_SESSION['user_id'];
$receiver_id = (int)$_GET['receiver_id'];

// Mark received messages as read
$mysqli->query("UPDATE messages SET is_read=1 WHERE sender_id=$receiver_id AND receiver_id=$user_id AND is_read=0");

$stmt = $mysqli->prepare("
  SELECT * FROM messages
  WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
  ORDER BY created_at ASC
");
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while($row = $result->fetch_assoc()){
  $messages[] = $row;
}
echo json_encode($messages);
?>
