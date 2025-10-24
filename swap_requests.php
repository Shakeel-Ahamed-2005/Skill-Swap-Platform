<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];

// Fetch swap requests where user is receiver
$query = "
    SELECT sr.id, sr.status, sr.created_at,
           s.skill_name, s.skill_type, s.skill_category,
           u.name AS sender_name, u.email AS sender_email
    FROM swap_requests sr
    JOIN skills s ON sr.skill_id = s.id
    JOIN users u ON sr.sender_id = u.id
    WHERE sr.receiver_id = ?
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
    <h2>Swap Requests</h2>
  </div>

  <div class="dashboard-container" id="swapRequestsContainer">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="dashboard-card" data-request-id="<?php echo $row['id']; ?>">
          <div class="card-icon"><i class="fas fa-exchange-alt"></i></div>
          <h3><?php echo htmlspecialchars($row['skill_name']); ?></h3>
          <p><strong>Category:</strong> <?php echo htmlspecialchars($row['skill_category']); ?></p>
          <p><strong>Type:</strong> <?php echo ucfirst($row['skill_type'] === 'offer' ? 'Offering' : 'Learning'); ?></p>
          <p><strong>From:</strong> <?php echo htmlspecialchars($row['sender_name']); ?> 
            (<a href="mailto:<?php echo htmlspecialchars($row['sender_email']); ?>"><?php echo htmlspecialchars($row['sender_email']); ?></a>)
          </p>
          <p class="status"><strong>Status:</strong> <?php echo ucfirst($row['status']); ?></p>
          <p><small>Requested on: <?php echo date("d M Y, H:i", strtotime($row['created_at'])); ?></small></p>
          <?php if ($row['status'] === 'pending'): ?>
          <div class="card-actions">
            <button class="btn-accept" data-action="accept">Accept</button>
            <button class="btn-reject" data-action="reject">Reject</button>
          </div>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No swap requests found.</p>
    <?php endif; ?>
  </div>
</main>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="/skillswap/assets/css/sidebar.css">
<link rel="stylesheet" href="/skillswap/assets/css/swap_requests.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
<script src="https://kit.fontawesome.com/a2e0e9e6d2.js" crossorigin="anonymous"></script>

<script>
// Function to handle Accept/Reject via AJAX
function handleSwapAction(button) {
  const card = button.closest('.dashboard-card');
  const requestId = card.getAttribute('data-request-id');
  const action = button.getAttribute('data-action');

  fetch('swap_requests_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `request_id=${requestId}&action=${action}`
  })
  .then(res => res.json())
  .then(data => {
    if(data.status === 'success'){
      card.querySelector('.status').innerHTML = `<strong>Status:</strong> ${action.charAt(0).toUpperCase() + action.slice(1)}`;
      const actionsDiv = card.querySelector('.card-actions');
      if(actionsDiv) actionsDiv.remove();
    } else {
      alert(data.message);
    }
  })
  .catch(err => {
    console.error(err);
    alert('Something went wrong.');
  });
}

// Attach event listeners
document.querySelectorAll('.card-actions button').forEach(btn => {
  btn.addEventListener('click', () => handleSwapAction(btn));
});

// -----------------------------
// Real-time sender notifications (polling)
// -----------------------------
function fetchSenderUpdates() {
  fetch('swap_sender_updates.php') // new endpoint
    .then(res => res.json())
    .then(data => {
      if(data.success && data.updates.length > 0){
        alert('Your swap request was ' + data.updates.map(u => u.status).join(', '));
        // Optionally update dashboard badge dynamically
        const badge = document.querySelector('.notification-btn .badge');
        if(badge) badge.textContent = parseInt(badge.textContent || '0') + data.updates.length;
      }
    })
    .catch(err => console.error(err));
}

// Poll every 10 seconds
setInterval(fetchSenderUpdates, 10000);
</script>
