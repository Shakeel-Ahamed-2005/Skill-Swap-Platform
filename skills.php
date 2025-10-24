<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

include 'includes/sidebar.php';

// Fetch all skills with user info
$query = "
    SELECT skills.*, users.name AS user_name, users.profile_pic, users.bio, users.email
    FROM skills 
    JOIN users ON skills.user_id = users.id
    ORDER BY skills.created_at DESC
";
$result = $mysqli->query($query);
?>

<main class="browse-main">
  <div class="browse-header">
    <h2>Browse Skills</h2>
    <div class="search-bar">
      <input type="text" id="searchSkill" placeholder="Search skills, categories...">
      <button><i class="fas fa-search"></i></button>
    </div>
  </div>

  <div class="skills-grid">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="skill-card">
          <div class="skill-info">
            <div class="skill-user">
              <img src="<?php echo htmlspecialchars($row['profile_pic'] ?: '/skillswap/assets/images/default-avatar.png'); ?>" 
                   alt="Profile" class="user-avatar-small">
              <div>
                <h4><?php echo htmlspecialchars($row['user_name']); ?></h4>
                <p class="bio"><?php echo htmlspecialchars($row['bio'] ?: 'No bio available'); ?></p>
              </div>
            </div>

            <h3><?php echo htmlspecialchars($row['skill_name']); ?></h3>
            <p class="desc"><?php echo htmlspecialchars($row['skill_description']); ?></p>

            <div class="skill-meta">
              <span class="category"><?php echo htmlspecialchars($row['skill_category']); ?></span>
              <span class="skill-type <?php echo $row['skill_type'] === 'offer' ? 'offer' : 'learn'; ?>">
                <?php echo ucfirst($row['skill_type'] === 'offer' ? 'Offering' : 'Learning'); ?>
              </span>
            </div>
          </div>

          <button class="btn-view" 
                  onclick='openModal(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
            View Details
          </button>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No skills found.</p>
    <?php endif; ?>
  </div>
</main>

<!-- MODAL -->
<div id="skillModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <div id="modalBody"></div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Styles -->
<link rel="stylesheet" href="/skillswap/assets/css/sidebar.css">
<link rel="stylesheet" href="/skillswap/assets/css/skills.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">

<script src="https://kit.fontawesome.com/a2e0e9e6d2.js" crossorigin="anonymous"></script>

<script>
function openModal(skill) {
  const modalBody = document.getElementById("modalBody");
  modalBody.innerHTML = `
    <div class="modal-user">
      <img src="${skill.profile_pic || '/skillswap/assets/images/default-avatar.png'}" alt="Profile">
      <h3>${skill.user_name}</h3>
      <p>${skill.bio || "No bio available."}</p>
      <hr>
      <p><strong>Skill:</strong> ${skill.skill_name}</p>
      <p><strong>Category:</strong> ${skill.skill_category}</p>
      <p><strong>Type:</strong> ${skill.skill_type === 'offer' ? 'Offering' : 'Learning'}</p>
      <p><strong>Description:</strong> ${skill.skill_description}</p>
      <p><strong>Email:</strong> <a href="mailto:${skill.email}">${skill.email}</a></p>
      <div class="contact-options">
        <a href="mailto:${skill.email}" class="contact-btn"><i class="fas fa-envelope"></i> Email</a>
        <button class="swap-btn" onclick="sendSwapRequest(${skill.id})"><i class="fas fa-exchange-alt"></i> Send Swap Request</button>
      </div>
    </div>
  `;
  document.getElementById("skillModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("skillModal").style.display = "none";
}

function sendSwapRequest(skillId) {
  fetch('/skillswap/send_swap_request.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'skill_id=' + skillId
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message);
  })
  .catch(err => {
    console.error(err);
    alert('Error sending swap request');
  });
}


window.onclick = function(event) {
  const modal = document.getElementById("skillModal");
  if (event.target === modal) closeModal();
};
</script>
<script>
const searchInput = document.getElementById("searchSkill");
const skillCards = document.querySelectorAll(".skill-card");

searchInput.addEventListener("input", function() {
  const query = this.value.toLowerCase().trim();

  skillCards.forEach(card => {
    const skillName = card.querySelector("h3").textContent.toLowerCase();
    const skillCategory = card.querySelector(".category").textContent.toLowerCase();
    const userName = card.querySelector(".skill-user h4").textContent.toLowerCase();

    if (skillName.includes(query) || skillCategory.includes(query) || userName.includes(query)) {
      card.style.display = "flex"; // show card
    } else {
      card.style.display = "none"; // hide card
    }
  });
});
</script>
