<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name']);

// Fetch current user's skills
$stmt = $mysqli->prepare("SELECT skills_offered, skills_wanted FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($skills_offered, $skills_wanted);
$stmt->fetch();
$stmt->close();

$offered_skills = $skills_offered ? array_map('trim', explode(',', $skills_offered)) : [];
$wanted_skills = $skills_wanted ? array_map('trim', explode(',', $skills_wanted)) : [];

$matches = [];
if (!empty($wanted_skills) && !empty($offered_skills)) {
    $stmt = $mysqli->prepare("
        SELECT id, name, bio, profile_pic, skills_offered, skills_wanted, email
        FROM users
        WHERE id != ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($user = $result->fetch_assoc()) {
        $user_offered = $user['skills_offered'] ? array_map('trim', explode(',', $user['skills_offered'])) : [];
        $user_wanted  = $user['skills_wanted'] ? array_map('trim', explode(',', $user['skills_wanted'])) : [];

        if (count(array_intersect($wanted_skills, $user_offered)) > 0 &&
            count(array_intersect($offered_skills, $user_wanted)) > 0) {
            $matches[] = $user;
        }
    }
    $stmt->close();
}
?>

<main class="best-matches-main">
    <h2>Best Matches for You</h2>

    <?php if (empty($matches)): ?>
        <p class="no-matches">No matching users found at this time. Check back later!</p>
    <?php else: ?>
        <div class="matches-container">
            <?php foreach ($matches as $user): ?>
                <div class="match-card">
                    <img src="<?php echo htmlspecialchars($user['profile_pic'] ?: '/skillswap/assets/images/user.PNG'); ?>" 
                         alt="<?php echo htmlspecialchars($user['name']); ?>" class="match-avatar">
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="match-bio"><?php echo htmlspecialchars($user['bio']); ?></p>
                    <p class="match-skills">
                        <strong>Offers:</strong> <?php echo htmlspecialchars($user['skills_offered']); ?><br>
                        <strong>Wants:</strong> <?php echo htmlspecialchars($user['skills_wanted']); ?>
                    </p>
                    <button class="viewProfileBtn"
                        data-name="<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>"
                        data-bio="<?php echo htmlspecialchars($user['bio'], ENT_QUOTES); ?>"
                        data-offered="<?php echo htmlspecialchars($user['skills_offered'], ENT_QUOTES); ?>"
                        data-wanted="<?php echo htmlspecialchars($user['skills_wanted'], ENT_QUOTES); ?>"
                        data-pic="<?php echo htmlspecialchars($user['profile_pic'], ENT_QUOTES); ?>"
                        data-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>"
                        data-user-id="<?php echo $user['id']; ?>">
                        View Profile
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Profile Modal -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-body">
            <img id="modalProfilePic" src="" alt="User Avatar" class="modal-avatar">
            <h2 id="modalName"></h2>
            <p><strong>Bio:</strong> <span id="modalBio"></span></p>
            <p><strong>Skills Offered:</strong> <span id="modalOffered"></span></p>
            <p><strong>Skills Wanted:</strong> <span id="modalWanted"></span></p>
            <!-- Contact buttons dynamically inserted here -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="/skillswap/assets/css/sidebar.css">
<link rel="stylesheet" href="/skillswap/assets/css/best_matches.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('profileModal');
    const closeBtn = modal.querySelector('.close');

    document.querySelectorAll('.viewProfileBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Fill modal content
            document.getElementById('modalName').textContent = btn.dataset.name;
            document.getElementById('modalBio').textContent = btn.dataset.bio || 'N/A';
            document.getElementById('modalOffered').textContent = btn.dataset.offered || 'N/A';
            document.getElementById('modalWanted').textContent = btn.dataset.wanted || 'N/A';
            document.getElementById('modalProfilePic').src = btn.dataset.pic || '/skillswap/assets/images/user.PNG';

            // Remove previous contact buttons
            const existing = modal.querySelector('.contact-options');
            if(existing) existing.remove();

            // Add contact buttons
            const contactHTML = `
                <div class="contact-options">
                    <a href="mailto:${btn.dataset.email}" class="contact-btn">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                    <button class="swap-btn" onclick="sendSwapRequest(${btn.dataset.userId})">
                        <i class="fas fa-exchange-alt"></i> Send Swap Request
                    </button>
                </div>
            `;
            modal.querySelector('.modal-body').insertAdjacentHTML('beforeend', contactHTML);

            modal.style.display = 'flex';
        });
    });

    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', e => { if(e.target === modal) modal.style.display = 'none'; });
});

function sendSwapRequest(userId) {
    fetch('/skillswap/send_swap_request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'user_id=' + userId + '&from_best_matches=1' // optional flag
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || 'Swap request sent!');
    })
    .catch(err => {
        console.error(err);
        alert('Error sending swap request.');
    });
}

</script>
