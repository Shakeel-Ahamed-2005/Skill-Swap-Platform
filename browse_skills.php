<?php
include 'includes/header.php';
?>

<!-- Browse Skills Section -->
<section class="browse-skills" id="browse-skills">
  <div class="container">
    <h1>Browse Skills</h1>
    <p>Discover a variety of skills shared by our community. Learn, teach, and grow together!</p>

    <!-- Search Bar -->
    <div class="search-bar">
      <input type="text" placeholder="Search for a skill (e.g., Cooking, Coding, Photography)">
      <button class="btn">Search</button>
    </div>

    <!-- Skill Categories -->
    <div class="skills-grid">
      <div class="skill-card">
        <div class="icon">💻</div>
        <h3>Web Development</h3>
        <p>Learn HTML, CSS, JavaScript, and more.</p>
        <a href="#" class="btn small">View Details</a>
      </div>

      <div class="skill-card">
        <div class="icon">🎨</div>
        <h3>Graphic Design</h3>
        <p>Master Photoshop, Illustrator, and creativity tools.</p>
        <a href="#" class="btn small">View Details</a>
      </div>

      <div class="skill-card">
        <div class="icon">🎸</div>
        <h3>Music & Instruments</h3>
        <p>From guitar to piano — share and learn melodies.</p>
        <a href="#" class="btn small">View Details</a>
      </div>

      <div class="skill-card">
        <div class="icon">🗣️</div>
        <h3>Language Exchange</h3>
        <p>Improve your English, Spanish, Japanese, and more.</p>
        <a href="#" class="btn small">View Details</a>
      </div>

      <div class="skill-card">
        <div class="icon">📸</div>
        <h3>Photography</h3>
        <p>Learn to capture stunning moments with your camera.</p>
        <a href="#" class="btn small">View Details</a>
      </div>

      <div class="skill-card">
        <div class="icon">🧘</div>
        <h3>Fitness & Wellness</h3>
        <p>Yoga, meditation, and healthy lifestyle practices.</p>
        <a href="#" class="btn small">View Details</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- CSS and JS -->
<link rel="stylesheet" href="/skillswap/assets/css/browse_skills.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
<script src="/skillswap/assets/js/index.js"></script>
