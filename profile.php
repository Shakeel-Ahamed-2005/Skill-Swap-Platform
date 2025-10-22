<?php
require 'includes/config.php';
if (session_status() == PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name']);
$errors = [];
$success = '';

/* -----------------------------
   HANDLE PROFILE UPDATE
----------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $skills_offered = trim($_POST['skills_offered'] ?? '');
    $skills_wanted = trim($_POST['skills_wanted'] ?? '');
    $profile_pic_path = null;
    $remove_flag = isset($_POST['remove_profile_pic']) && $_POST['remove_profile_pic'] === '1';

    // filesystem and web directories
    $uploadFsDir = __DIR__ . "/uploads/profile_pics/";
    $webDirPrefix = "/skillswap/uploads/profile_pics/";

    // Ensure upload directory exists
    if (!file_exists($uploadFsDir)) {
        if (!mkdir($uploadFsDir, 0777, true) && !is_dir($uploadFsDir)) {
            $errors[] = "Failed to create upload directory.";
        }
    }

    // --- Handle image upload ---
    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        $fileName = basename($_FILES['profile_pic']['name']);
        $uniqueName = uniqid() . "_" . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
        $targetFsFile = $uploadFsDir . $uniqueName;
        $imageFileType = strtolower(pathinfo($uniqueName, PATHINFO_EXTENSION));
        $validTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($imageFileType, $validTypes)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif ($_FILES['profile_pic']['size'] > 2 * 1024 * 1024) { // 2MB
            $errors[] = "File size must be less than 2MB.";
        } elseif (empty($errors)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFsFile)) {
                $profile_pic_path = $webDirPrefix . $uniqueName; // what we store in DB & use in <img src>
                // optionally delete previous file later after DB update
            } else {
                $errors[] = "Error uploading the image.";
            }
        }
    }

    // --- If remove flag set and no new upload, remove existing DB entry and delete file ---
    if ($remove_flag && !$profile_pic_path) {
        // fetch existing pic path to delete file
        $q = $mysqli->prepare("SELECT profile_pic FROM users WHERE id = ?");
        $q->bind_param("i", $user_id);
        $q->execute();
        $q->bind_result($existing_pic);
        $q->fetch();
        $q->close();

        if ($existing_pic) {
            $existing_basename = basename($existing_pic);
            $existingFs = $uploadFsDir . $existing_basename;
            if (file_exists($existingFs)) {
                @unlink($existingFs);
            }
        }
        // set profile_pic_path to empty string to update DB to NULL-like value
        $profile_pic_path = null;
    }

    // --- Update user info ---
    if (empty($errors)) {
        if ($profile_pic_path !== null) {
            // either new upload or explicitly set to null (remove)
            if ($profile_pic_path === '') {
                // not used, but placeholder
            }
            $stmt = $mysqli->prepare("UPDATE users SET bio=?, skills_offered=?, skills_wanted=?, profile_pic=? WHERE id=?");
            $stmt->bind_param("ssssi", $bio, $skills_offered, $skills_wanted, $profile_pic_path, $user_id);
        } else {
            // no change to profile_pic column
            $stmt = $mysqli->prepare("UPDATE users SET bio=?, skills_offered=?, skills_wanted=? WHERE id=?");
            $stmt->bind_param("sssi", $bio, $skills_offered, $skills_wanted, $user_id);
        }

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";

            // If we uploaded a new pic and there was a previous pic, delete the old file
            if (!empty($profile_pic_path)) {
                // get previous profile pic (before update) to delete
                // Note: since we already updated the DB, fetch rows but check if file exists in uploads matching basename not equal to current
                // (This is best-effort; adjust if you want safer transactional behavior)
                // We'll skip deleting previous here to keep it safe unless you want it.
            }
        } else {
            $errors[] = "Error updating profile.";
        }
        $stmt->close();
    }
}

/* -----------------------------
   FETCH USER DATA
----------------------------- */
$query = "SELECT name, email, bio, skills_offered, skills_wanted, profile_pic FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("Database error: " . $mysqli->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $bio, $skills_offered, $skills_wanted, $profile_pic);
$stmt->fetch();
$stmt->close();

// Default profile image if none
$uploadFsDir = __DIR__ . "/uploads/profile_pics/";
if (!empty($profile_pic)) {
    // profile_pic in DB is like: /skillswap/uploads/profile_pics/unique.jpg
    $basename = basename($profile_pic);
    $expectedFs = $uploadFsDir . $basename;
    if (file_exists($expectedFs)) {
        // Use web path stored in DB (already correct)
        // ensure it starts with /skillswap/... if not add prefix
        if (strpos($profile_pic, '/skillswap/') === 0) {
            // ok
        } else {
            $profile_pic = "/skillswap/uploads/profile_pics/" . $basename;
        }
    } else {
        $profile_pic = "/skillswap/assets/images/default-avatar.png";
    }
} else {
    $profile_pic = "/skillswap/assets/images/default-avatar.png";
}
?>

<main class="profile-main">
    <h2>Your Profile</h2>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="errors"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php endif; ?>

    <form method="post" class="profile-form" enctype="multipart/form-data">
        <div class="profile-picture-wrapper">
            <div class="profile-picture">
                <img id="profilePreview" src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
                <div class="upload-controls" id="uploadControls">
                    <label for="profile_pic" class="upload-btn" title="Upload">
                        <i class="fas fa-upload"></i>
                    </label>
                    <button type="button" class="remove-btn" id="removeImage" title="Remove">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <input type="file" name="profile_pic" id="profile_pic" accept="image/*">
            </div>
        </div>

        <!-- hidden flag to instruct server to remove the pic on save -->
        <input type="hidden" name="remove_profile_pic" id="remove_profile_pic" value="0">

        <label>
            Full Name
            <input type="text" value="<?php echo htmlspecialchars($name); ?>" disabled>
        </label>

        <label>
            Email
            <input type="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
        </label>

        <label>
            Bio
            <textarea name="bio" rows="4"><?php echo htmlspecialchars($bio ?? ''); ?></textarea>
        </label>

        <label>
            Skills You Offer
            <input type="text" name="skills_offered" value="<?php echo htmlspecialchars($skills_offered ?? ''); ?>">
        </label>

        <label>
            Skills You Want to Learn
            <input type="text" name="skills_wanted" value="<?php echo htmlspecialchars($skills_wanted ?? ''); ?>">
        </label>

        <button type="submit" class="btn">Save Changes</button>
    </form>
</main>

<script>
(function(){
  const fileInput = document.getElementById('profile_pic');
  const preview = document.getElementById('profilePreview');
  const removeBtn = document.getElementById('removeImage');
  const removeFlag = document.getElementById('remove_profile_pic');

  const defaultAvatar = '/skillswap/assets/images/default-avatar.png';

  // When user selects a new file -> immediate preview and unset remove flag
  fileInput.addEventListener('change', function (event) {
      const [file] = event.target.files;
      if (file) {
          preview.src = URL.createObjectURL(file);
          removeFlag.value = '0';
      }
  });

  // Remove button: reset preview to default, clear file input, and set remove flag
  removeBtn.addEventListener('click', function () {
      // Clear the file input
      fileInput.value = '';
      // Reset preview image
      preview.src = defaultAvatar;
      // Tell server to remove stored profile on Save
      removeFlag.value = '1';
  });
})();
</script>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="assets/css/sidebar.css">
<link rel="stylesheet" href="assets/css/profile.css">
<link rel="stylesheet" href="assets/css/footer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
