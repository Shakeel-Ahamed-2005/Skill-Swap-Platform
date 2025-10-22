<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

$user_name = htmlspecialchars($_SESSION['user_name']);
?>

<!-- Load Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<aside class="sidebar">
  <div class="sidebar-header">
  <div class="logo">
            <a href="/skillswap/index.php">
                <img src="/skillswap/assets/images/logo.png" alt="Skill Swap Logo">
            </a>
        </div>
    <p><i class="fas fa-user-circle"></i> <?php echo $user_name; ?></p>
  </div>

  <nav class="sidebar-nav">
    <a href="/skillswap/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
      <i class="fas fa-home"></i> Dashboard
    </a>

    <a href="/skillswap/profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
      <i class="fas fa-user"></i> Profile
    </a>

    <a href="/skillswap/add_skill.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add-skill.php' ? 'active' : ''; ?>">
      <i class="fas fa-plus-circle"></i> Add Skill
    </a>

    <a href="/skillswap/skills.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'skills.php' ? 'active' : ''; ?>">
      <i class="fas fa-bullseye"></i> Browse Skills
    </a>

    <a href="/skillswap/swap-requests.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'swap-requests.php' ? 'active' : ''; ?>">
      <i class="fas fa-exchange-alt"></i> Swap Requests
    </a>

    <a href="/skillswap/best-matches.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'best-matches.php' ? 'active' : ''; ?>">
      <i class="fas fa-star"></i> Best Matches
    </a>

    <a href="/skillswap/messages.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
      <i class="fas fa-comments"></i> Messages
    </a>

    <a href="/skillswap/logout.php" class="logout-link">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </nav>
</aside>
