<?php
include 'includes/header.php';
require 'includes/config.php';
global $mysqli;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = "Please fill all required fields.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors[] = "Email already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $mysqli->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
                if ($ins) {
                    $ins->bind_param('sss', $name, $email, $hash);
                    if ($ins->execute()) {
                        header('Location: /skillswap/login.php?registered=1');
                        exit;
                    } else {
                        $errors[] = "Registration failed. Try again.";
                    }
                    $ins->close();
                } else {
                    $errors[] = "Database error: unable to prepare insert.";
                }
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: unable to prepare select.";
        }
    }
}
?>

<h2>Create an Account</h2>

<?php if(!empty($errors)): ?>
  <div class="errors"><?php echo implode('<br>', array_map('htmlspecialchars',$errors)); ?></div>
<?php endif; ?>

<form method="post" class="form" autocomplete="off">
  <label>
    Full Name
    <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
  </label>

  <label>
    Email
    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
  </label>

  <label>
    Password
    <input type="password" name="password" required autocomplete="new-password">
  </label>

  <label>
    Confirm Password
    <input type="password" name="confirm" required autocomplete="new-password">
  </label>

  <button type="submit" class="btn">Register</button>
</form>

<p>Already have an account? <a href="/skillswap/login.php">Login here</a></p>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="/skillswap/assets/css/index.css">
<link rel="stylesheet" href="/skillswap/assets/css/footer.css">
<link rel="stylesheet" href="/skillswap/assets/css/register.css">
