<?php
require 'includes/config.php';

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

include 'includes/sidebar.php';
?>

<main class="dashboard-main">
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h2>

  <div class="dashboard-container">
    <section class="dashboard-card">
      <h3>Your Profile</h3>
      <p>Manage your account settings, update your profile, and view your skills.</p>
      <a href="/skillswap/profile.php" class="btn">Go to Profile</a>
    </section>

    <section class="dashboard-card">
      <h3>Find Skills</h3>
      <p>Browse available skills and connect with other learners or experts.</p>
      <a href="/skillswap/skills.php" class="btn">Explore Skills</a>
    </section>

    <section class="dashboard-card">
      <h3>Messages</h3>
      <p>Stay connected with your learning partners.</p>
      <a href="/skillswap/messages.php" class="btn">Go to Messages</a>
    </section>

    <section class="dashboard-card">
      <h3>Settings</h3>
      <p>Update your preferences, password, and notifications.</p>
      <a href="/skillswap/settings.php" class="btn">Account Settings</a>
    </section>
  </div>
</main>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="/skillswap/assets/css/sidebar.css">
<link rel="stylesheet" href="/skillswap/assets/css/dashboard.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
