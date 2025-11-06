<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];

// Fetch swap requests where user is the sender
$query = "
    SELECT sr.id, sr.status, sr.created_at,
           s.skill_name, s.skill_type, s.skill_category,
           u.name AS receiver_name, u.email AS receiver_email
    FROM swap_requests sr
    JOIN skills s ON sr.skill_id = s.id
    JOIN users u ON sr.receiver_id = u.id
    WHERE sr.sender_id = ?
    ORDER BY sr.created_at DESC
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<main class="dashboard-main">
  <div class="dashboard-header">
    <h2>My Sent Requests</h2>

    <!-- Category Filter Buttons -->
    <div class="filter-buttons">
      <button class="filter-btn active" data-filter="all">All</button>
      <button class="filter-btn" data-filter="pending">Pending</button>
      <button class="filter-btn" data-filter="accepted">Accepted</button>
      <button class="filter-btn" data-filter="rejected">Rejected</button>
    </div>
  </div>

  <div class="dashboard-container" id="sentRequestsContainer">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="dashboard-card" data-status="<?php echo strtolower($row['status']); ?>">
          <div class="card-icon"><i class="fas fa-paper-plane"></i></div>
          <h3><?php echo htmlspecialchars($row['skill_name']); ?></h3>
          <p><strong>Category:</strong> <?php echo htmlspecialchars($row['skill_category']); ?></p>
          <p><strong>Type:</strong> <?php echo ucfirst($row['skill_type'] === 'offer' ? 'Offering' : 'Learning'); ?></p>
          <p><strong>To:</strong> <?php echo htmlspecialchars($row['receiver_name']); ?>
            (<a href="mailto:<?php echo htmlspecialchars($row['receiver_email']); ?>"><?php echo htmlspecialchars($row['receiver_email']); ?></a>)
          </p>
          <p class="status">
            <strong>Status:</strong> 
            <?php echo ucfirst($row['status'] ?? 'Pending'); ?>
          </p>
          <p><small>Requested on: <?php echo date("d M Y, H:i", strtotime($row['created_at'])); ?></small></p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>You haven’t sent any swap requests yet.</p>
    <?php endif; ?>
  </div>
</main>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="/skillswap/assets/css/sidebar.css">
<link rel="stylesheet" href="/skillswap/assets/css/swap_requests.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
<script src="https://kit.fontawesome.com/a2e0e9e6d2.js" crossorigin="anonymous"></script>

<script>
// ---------- FILTER LOGIC ----------
const filterButtons = document.querySelectorAll('.filter-btn');
const cards = document.querySelectorAll('.dashboard-card');

filterButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    // Remove active class from all buttons
    filterButtons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const filter = btn.dataset.filter;

    cards.forEach(card => {
      const status = card.dataset.status;
      if (filter === 'all' || status === filter) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  });
});
</script>

<style>
/* --- Filter Buttons --- */
.filter-buttons {
  margin-top: 10px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 8px 16px;
  background: #eee;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-btn:hover {
  background: #ddd;
}

.filter-btn.active {
  background: #086375;
  color: #fff;
  box-shadow: 0 0 5px rgba(0,0,0,0.15);
}
</style>
