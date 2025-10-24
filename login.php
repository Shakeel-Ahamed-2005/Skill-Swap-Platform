<?php
include 'includes/header.php';
require 'includes/config.php';

$err = '';
$success = '';

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

<!-- ==============================
     LOGIN HERO
=============================== -->
<section class="login-hero">
  <div class="login-hero-content">
    <h1>Welcome Back</h1>
    <p>Log in to access your SkillSwap account and connect with learners & experts.</p>
  </div>
</section>

<!-- ==============================
     LOGIN FORM
=============================== -->
<div class="login-container">
    <?php if ($success): ?>
      <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="errors"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="post" class="login-form" autocomplete="off">
    <label>
        Email
        <input type="email" name="email" required autocomplete="off" placeholder="Enter your email">
    </label>

    <label>
        Password
        <input type="password" name="password" required autocomplete="new-password" placeholder="Enter your password">
    </label>

    <button type="submit" class="btn">Login</button>
</form>


    <p class="register-link">Don't have an account? <a href="/skillswap/register.php">Register</a></p>
</div>

<?php include 'includes/footer.php'; ?>
<link rel="stylesheet" href="/skillswap/assets/css/login.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
