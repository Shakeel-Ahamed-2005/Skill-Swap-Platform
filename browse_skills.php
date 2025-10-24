<?php
include 'includes/header.php';
include 'includes/config.php'; // Use your existing config.php

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Prepare SQL with filters
$sql = "SELECT s.*, u.name, u.profile_pic 
        FROM skills s 
        JOIN users u ON s.user_id = u.id
        WHERE 1=1";

if ($search) {
    $sql .= " AND s.skill_name LIKE '%" . $mysqli->real_escape_string($search) . "%'";
}

if ($category && $category != 'all') {
    $sql .= " AND s.skill_category = '" . $mysqli->real_escape_string($category) . "'";
}

$sql .= " ORDER BY s.created_at DESC";
$result = $mysqli->query($sql);

// Fetch categories dynamically
$cat_res = $mysqli->query("SELECT DISTINCT skill_category FROM skills");
$categories = [];
if ($cat_res && $cat_res->num_rows > 0) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row['skill_category'];
    }
}
?>

<!-- ==============================
     HERO SECTION
=============================== -->
<section class="browse-hero">
    <div class="browse-hero-content">
        <h1>Explore <span>Skills</span></h1>
        <p>Discover what our members are offering and looking to learn. Connect and grow together!</p>
    </div>
</section>

<!-- ==============================
     FILTER & SEARCH
=============================== -->
<div class="filter-bar">
    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Search skills..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="all">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat; ?>" <?php if ($category == $cat) echo 'selected'; ?>><?php echo $cat; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Search</button>
    </form>
</div>

<!-- ==============================
     SKILLS GRID
=============================== -->
<div class="skills-container">
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="skills-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="skill-card">
    <div class="skill-user">
        <img src="<?php echo $row['profile_pic'] ? $row['profile_pic'] : '/skillswap/assets/images/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
        <h4><?php echo htmlspecialchars($row['name']); ?></h4>
    </div>
    <div class="skill-details">
        <h3><?php echo htmlspecialchars($row['skill_name']); ?></h3>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($row['skill_category']); ?></p>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($row['skill_type']); ?></p>
        <p><?php echo htmlspecialchars($row['skill_description']); ?></p>

        <!-- View Details Button -->
        <button class="btn-view" 
            data-skill-id="<?php echo $row['id']; ?>"
            data-user-id="<?php echo $row['user_id']; ?>"
            data-skill="<?php echo htmlspecialchars($row['skill_name']); ?>"
            data-category="<?php echo htmlspecialchars($row['skill_category']); ?>"
            data-type="<?php echo htmlspecialchars($row['skill_type']); ?>"
            data-description="<?php echo htmlspecialchars($row['skill_description']); ?>"
            data-pic="<?php echo $row['profile_pic']; ?>">
            View Details
        </button>
    </div>
</div>

<!-- Skill Details Modal -->
<!-- Skill Details Modal -->
<div id="skillModal" class="modal">
    <div class="modal-content card-modal">
        <span class="close">&times;</span>
        <div class="modal-body">
            <div class="modal-user">
                <img id="modalProfilePic" src="" alt="User Avatar" class="modal-avatar">
                <h3 id="modalUserName"></h3>
                <hr>
            </div>
            <div class="modal-skill-info">
    <h2 id="modalSkillName"></h2>
    <p><strong>Category:</strong> <span id="modalCategory"></span></p>
    <p><strong>Type:</strong> <span id="modalType"></span></p>
    <p><strong>Description:</strong></p>
    <p id="modalDescription"></p>

    <div class="modal-actions">
        <button id="btnSendRequest" class="modal-btn">Send Request</button>
        <button id="btnCloseModal" class="modal-btn secondary">Close</button>
    </div>
</div>

        </div>
    </div>
</div>


            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="no-skills">No skills found matching your search or category.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('skillModal');
    const closeBtn = modal.querySelector('.close');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnSendRequest = document.getElementById('btnSendRequest');

    // Show modal
    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('modalProfilePic').src = btn.dataset.pic || '/skillswap/assets/images/default-avatar.png';
            document.getElementById('modalUserName').textContent = btn.dataset.name;
            document.getElementById('modalSkillName').textContent = btn.dataset.skill;
            document.getElementById('modalCategory').textContent = btn.dataset.category;
            document.getElementById('modalType').textContent = btn.dataset.type;
            document.getElementById('modalDescription').textContent = btn.dataset.description;

            modal.style.display = 'flex';

            // Store data for sending request
            btnSendRequest.dataset.skillId = btn.dataset.skillId;
            btnSendRequest.dataset.userId = btn.dataset.userId;
        });
    });

    // Close modal
    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    btnCloseModal.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', e => { if(e.target === modal) modal.style.display = 'none'; });

    // Send request button
    btnSendRequest.addEventListener('click', () => {
        const skillId = btnSendRequest.dataset.skillId;
        const userId = btnSendRequest.dataset.userId;

        // Example: check if user is logged in
        <?php if (!isset($_SESSION['user_id'])): ?>
            alert('Please login to send a swap request.');
            modal.style.display = 'none';
        <?php else: ?>
            fetch('/skillswap/send_swap_request.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `skill_id=${skillId}&user_id=${userId}`
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                modal.style.display = 'none';
            })
            .catch(err => console.error(err));
        <?php endif; ?>
    });
});

</script>

<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
<link rel="stylesheet" href="/skillswap/assets/css/browse_skills.css">