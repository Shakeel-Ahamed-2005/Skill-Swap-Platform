<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$skill_id = intval($_GET['id'] ?? 0);

$stmt = $mysqli->prepare("DELETE FROM skills WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $skill_id, $user_id);
$stmt->execute();
$stmt->close();

header("Location: my_skills.php");
exit;
