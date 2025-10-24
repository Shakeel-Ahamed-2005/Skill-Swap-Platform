<?php
include 'includes/header.php';
?>

<!-- ==============================
     CONTACT HERO SECTION
=============================== -->
<section class="contact-hero">
  <div class="contact-hero-content">
    <h1>Get in <span>Touch</span></h1>
    <p>
      We’d love to hear from you! Whether you have questions, feedback, or collaboration ideas -
      let’s start the conversation.
    </p>
  </div>
</section>

<!-- ==============================
     CONTACT SECTION
=============================== -->
<div class="contact-container">

  <!-- Contact Info Cards -->
  <div class="contact-info">
    <div class="info-card">
      <i class="fas fa-envelope"></i>
      <h3>Email Us</h3>
      <p>ahamedskl2005@gmail.com</p>
    </div>

    <div class="info-card">
      <i class="fas fa-map-marker-alt"></i>
      <h3>Location</h3>
      <p>Kandy</p>
      <p>Sri Lanka</p>
    </div>

    <div class="info-card">
      <i class="fas fa-clock"></i>
      <p>Open 24/7</p>
      <p>We are always available online!</p>
    </div>
  </div>

  <!-- Contact Form -->
  <div class="contact-form">
    <h2>Send a Message</h2>
    <form action="#" method="POST" onsubmit="return validateForm(event)">
      <div class="form-group">
        <input type="text" name="name" id="name" required>
        <label for="name">Your Name</label>
      </div>

      <div class="form-group">
        <input type="email" name="email" id="email" required>
        <label for="email">Your Email</label>
      </div>

      <div class="form-group">
        <textarea name="message" id="message" rows="5" required></textarea>
        <label for="message">Your Message</label>
      </div>

      <button type="submit" class="btn-submit">Send Message</button>
    </form>
  </div>

</div>

<!-- ==============================
     MAP SECTION
=============================== -->
<div class="map-section">
  <iframe
    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.123456789012!2d80.63122331575924!3d7.290573194587648!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae36c5a1b2a1b2b%3A0x123456789abcdef0!2sKandy%2C%20Central%20Province%2C%20Sri%20Lanka!5e0!3m2!1sen!2sus!4v1698523456789"
    width="100%"
    height="400"
    style="border:0;"
    allowfullscreen=""
    loading="lazy">
  </iframe>
</div>

<?php
include 'includes/footer.php';
?>

<link rel="stylesheet" href="/skillswap/assets/css/contact.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>

<script>
  function validateForm(e) {
    e.preventDefault();
    alert("Thank you for contacting SkillSwap! We’ll get back to you soon.");
    e.target.reset();
    return false;
  }
</script>