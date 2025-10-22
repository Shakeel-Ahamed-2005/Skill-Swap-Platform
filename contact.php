<?php
include 'includes/header.php';
require 'includes/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = "Please fill in all fields.";
    } else {
        // Option 1: Save message to DB (uncomment if you have a `contact_messages` table)
        /*
        $stmt = $mysqli->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sss', $name, $email, $message);
            if ($stmt->execute()) {
                $success = "Thank you for contacting us! We'll get back to you soon.";
            } else {
                $error = "Unable to send your message. Please try again.";
            }
            $stmt->close();
        }
        */

        // Option 2: Just display success (without DB)
        $success = "Thank you, $name! Your message has been received.";
    }
}
?>

<h2>Contact Us</h2>

<?php if ($success): ?>
  <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="errors"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" class="form contact-form" autocomplete="off">
  <label>
    Your Name
    <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
  </label>

  <label>
    Your Email
    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
  </label>

  <label>
    Message
    <textarea name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
  </label>

  <button type="submit" class="btn">Send Message</button>
</form>

<section class="contact-info">
  <h3>Get in Touch</h3>
  <p>We’d love to hear from you! Whether you have questions, feedback, or partnership ideas — feel free to reach out.</p>
  <ul>
    <li><strong>Email:</strong> support@skillswap.com</li>
    <li><strong>Phone:</strong> +1 (555) 123-4567</li>
    <li><strong>Address:</strong> 123 Learning Lane, Innovation City</li>
  </ul>
</section>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="/skillswap/assets/css/index.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
<link rel="stylesheet" href="/skillswap/assets/css/contact.css">
