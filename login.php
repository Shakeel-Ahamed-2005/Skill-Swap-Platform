<?php
include 'includes/header.php';
require 'includes/config.php';

$err = '';
$success = '';

// Show success if redirected after registration
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success = "Registration successful! You can now log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $mysqli->prepare("SELECT id, password, name FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hash, $name);
        $stmt->fetch();
        if (password_verify($password, $hash)) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            header('Location: /skillswap/dashboard.php');
            exit;
        }
    }
    $err = "Invalid email or password.";
    $stmt->close();
}
?>

<h2>Login</h2>

<?php if ($success): ?>
  <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($err): ?>
  <div class="errors"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<form method="post" class="form" autocomplete="off">
  <label>
    Email
    <input type="email" name="email" 
           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
           required autocomplete="off">
  </label>

  <label>
    Password
    <input type="password" name="password" required autocomplete="new-password">
  </label>

  <button type="submit" class="btn">Login</button>
</form>

<p>Don't have an account? <a href="/skillswap/register.php">Register</a></p>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="/skillswap/assets/css/index.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css"> 
<link rel="stylesheet" href="/skillswap/assets/css/login.css">
