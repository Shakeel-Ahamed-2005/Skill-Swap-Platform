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

// Fetch user's profile picture
$stmt = $mysqli->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();

if (!empty($profile_pic)) {
    $profile_pic = str_replace('\\', '/', $profile_pic);
    $base_path = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    if (strpos($profile_pic, $base_path) === 0) {
        $profile_pic = str_replace($base_path, '', $profile_pic);
    }
} else {
    $profile_pic = "/skillswap/assets/images/user.PNG";
}

/* -----------------------------
   FETCH DASHBOARD STATS
----------------------------- */

// Total skills
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM skills WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($skill_count);
$stmt->fetch();
$stmt->close();

// Offered and wanted counts
$stmt = $mysqli->prepare("SELECT 
    SUM(skill_type = 'offer') AS offers, 
    SUM(skill_type = 'want') AS wants 
    FROM skills WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($offer_count, $want_count);
$stmt->fetch();
$stmt->close();

// Total users
$result = $mysqli->query("SELECT COUNT(*) AS total FROM users");
$total_users = ($row = $result->fetch_assoc()) ? $row['total'] : 0;

// Skill data for charts
$skill_data = [];
$stmt = $mysqli->prepare("
    SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) AS total 
    FROM skills 
    WHERE user_id = ? 
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $skill_data[] = $row;
$stmt->close();

/* -----------------------------
   Notifications
----------------------------- */

// Unread messages
$unread_msgs = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($unread_msgs);
$stmt->fetch();
$stmt->close();

// Pending swap requests for receiver
$pending_swaps = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM swap_requests WHERE receiver_id=? AND status='pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($pending_swaps);
$stmt->fetch();
$stmt->close();

// Pending updates for sender (accepted/rejected swap requests)
$pending_updates = 0;
$stmt = $mysqli->prepare("
    SELECT COUNT(*) 
    FROM swap_requests 
    WHERE sender_id=? AND sender_notified=0 AND status IN ('accept','reject')
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($pending_updates);
$stmt->fetch();
$stmt->close();

// Total notifications
$total_notifications = $unread_msgs + $pending_swaps + $pending_updates;
?>

<main class="dashboard-main">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h2>Welcome, <?php echo $user_name; ?> 👋</h2>
        <div class="dashboard-actions">
            <button class="notification-btn" onclick="window.location.href='/skillswap/swap_requests.php'">
                <i class="fas fa-bell"></i>
                <?php if ($total_notifications > 0): ?>
                    <span class="badge"><?php echo $total_notifications; ?></span>
                <?php endif; ?>
            </button>
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="User Avatar" class="user-avatar">
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fas fa-user"></i></div>
            <div>
                <h3><?php echo $skill_count; ?></h3>
                <p>Skills Added</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fas fa-handshake"></i></div>
            <div>
                <h3><?php echo $offer_count; ?></h3>
                <p>Skills Offered</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-yellow"><i class="fas fa-lightbulb"></i></div>
            <div>
                <h3><?php echo $want_count; ?></h3>
                <p>Skills Wanted</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-purple"><i class="fas fa-users"></i></div>
            <div>
                <h3><?php echo $total_users; ?></h3>
                <p>Total Members</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="dashboard-charts">
        <div class="chart-card">
            <h3>Skills Added Over Time</h3>
            <canvas id="skillsChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Skill Distribution</h3>
            <canvas id="swapChart"></canvas>
        </div>
    </div>

    <!-- Swap Updates Card -->
    <section class="dashboard-swap <?php echo ($pending_updates > 0) ? 'has-updates' : ''; ?>">
        <div class="card-icon"><i class="fas fa-bell"></i></div>
        <h3>Swap Updates</h3>
        <p>You have <?php echo $pending_updates; ?> recent responses to your swap requests.</p>
        <a href="/skillswap/swap_requests.php" class="btn">View Updates</a>
    </section>

    <!-- Dashboard Cards -->
    <div class="dashboard-container">
        <section class="dashboard-card">
            <div class="card-icon"><i class="fas fa-user"></i></div>
            <h3>Your Profile</h3>
            <p>Manage your account, update your profile, and view your skills.</p>
            <a href="/skillswap/profile.php" class="btn">Go to Profile</a>
        </section>

        <section class="dashboard-card">
            <div class="card-icon"><i class="fas fa-bullseye"></i></div>
            <h3>Find Skills</h3>
            <p>Browse available skills and connect with other learners or experts.</p>
            <a href="/skillswap/skills.php" class="btn">Explore Skills</a>
        </section>

        <section class="dashboard-card">
            <div class="card-icon"><i class="fas fa-comments"></i></div>
            <h3>Messages</h3>
            <p>Stay connected with your learning partners and keep track of conversations.</p>
            <a href="/skillswap/messages.php" class="btn">Go to Messages</a>
        </section>

        <section class="dashboard-card">
            <div class="card-icon"><i class="fas fa-cog"></i></div>
            <h3>Settings</h3>
            <p>Update your preferences, password, and notification settings.</p>
            <a href="/skillswap/settings.php" class="btn">Account Settings</a>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<!-- Styles -->
<link rel="stylesheet" href="/skillswap/assets/css/sidebar.css">
<link rel="stylesheet" href="/skillswap/assets/css/dashboard.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const skillsData = <?php echo json_encode($skill_data); ?>;
    const months = skillsData.map(item => item.month);
    const totals = skillsData.map(item => item.total);

    new Chart(document.getElementById('skillsChart'), {
        type: 'line',
        data: {
            labels: months.length ? months : ['No Data'],
            datasets: [{
                label: 'Skills Added',
                data: totals.length ? totals : [0],
                borderColor: '#086375',
                backgroundColor: 'rgba(8,99,117,0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('swapChart'), {
        type: 'doughnut',
        data: {
            labels: ['Offer', 'Want'],
            datasets: [{
                data: [<?php echo $offer_count; ?>, <?php echo $want_count; ?>],
                backgroundColor: ['#16a34a', '#facc15']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const avatar = document.querySelector(".user-avatar");
        avatar.onerror = () => avatar.src = "/skillswap/assets/images/default-avatar.png";
    });
</script>