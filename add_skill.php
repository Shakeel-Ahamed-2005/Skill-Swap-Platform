<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /skillswap/login.php");
    exit;
}

include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Categories
$categories = ['Technology','Art','Language','Music','Cooking','Other'];

/* -----------------------------
   HANDLE FORM SUBMISSION (ADD / EDIT)
----------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skill_id = (int)($_POST['skill_id'] ?? 0);
    $skill_name = trim($_POST['skill_name'] ?? '');
    $skill_category = trim($_POST['skill_category'] ?? '');
    $skill_type = $_POST['skill_type'] ?? '';
    $skill_description = trim($_POST['skill_description'] ?? '');

    // Validation
    if (!$skill_name) $errors[] = "Skill name is required.";
    if (!$skill_category) $errors[] = "Please choose a category.";
    if (!in_array($skill_type,['offer','want'])) $errors[] = "Please specify skill type.";
    if (!$skill_description) $errors[] = "Please provide a description.";

    if (!$errors) {
        if ($skill_id > 0) {
            // Update existing skill
            $stmt = $mysqli->prepare("UPDATE skills SET skill_name=?, skill_category=?, skill_type=?, skill_description=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssssii", $skill_name, $skill_category, $skill_type, $skill_description, $skill_id, $user_id);
            if ($stmt->execute()) $success = "Skill updated successfully!";
            else $errors[] = "Database error: " . $mysqli->error;
            $stmt->close();
        } else {
            // Add new skill
            $stmt = $mysqli->prepare("INSERT INTO skills (user_id, skill_name, skill_category, skill_type, skill_description, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issss", $user_id, $skill_name, $skill_category, $skill_type, $skill_description);
            if ($stmt->execute()) $success = "Skill added successfully!";
            else $errors[] = "Database error: " . $mysqli->error;
            $stmt->close();
        }
        // Clear POST for form reset
        $_POST = [];
    }
}

/* -----------------------------
   HANDLE DELETE
----------------------------- */
if (isset($_GET['delete'])) {
    $skill_id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM skills WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $skill_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: add_skill.php");
    exit;
}

/* -----------------------------
   FETCH USER SKILLS
----------------------------- */
$skills = [];
$stmt = $mysqli->prepare("SELECT id, skill_name, skill_category, skill_type, skill_description, created_at FROM skills WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $skills[] = $row;
$stmt->close();
?>

<main class="add-skill-main">
    <h2>Add / Edit Skill</h2>

    <?php if (!empty($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="errors"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php endif; ?>

    <form method="post" class="add-skill-form" id="addSkillForm">
        <input type="hidden" name="skill_id" id="skill_id" value="">

        <label>
            Skill Name
            <input type="text" name="skill_name" id="skill_name" placeholder="e.g., Graphic Design" value="">
        </label>

        <label>
            Category
            <select name="skill_category" id="skill_category">
                <option value="">Select category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="skill-type">
            <label><input type="radio" name="skill_type" value="offer" id="type_offer"> I Offer This Skill</label>
            <label><input type="radio" name="skill_type" value="want" id="type_want"> I Want to Learn This Skill</label>
        </div>

        <label>
            Description
            <textarea name="skill_description" id="skill_description" rows="4"></textarea>
        </label>

        <button type="submit" class="btn" id="submitBtn">Add Skill</button>
        <button type="button" class="btn cancel-btn" onclick="resetForm()">Cancel</button>
    </form>

    <h2>My Skills</h2>
    <?php if (count($skills) === 0): ?>
        <p>You haven't added any skills yet.</p>
    <?php else: ?>
        <table class="skills-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($skills as $skill): ?>
                    <tr data-id="<?php echo $skill['id']; ?>">
                        <td class="skill-name"><?php echo htmlspecialchars($skill['skill_name']); ?></td>
                        <td class="skill-category"><?php echo htmlspecialchars($skill['skill_category']); ?></td>
                        <td>
  <span class="skill-type <?php echo $skill['skill_type']; ?>">
    <?php echo htmlspecialchars($skill['skill_type']); ?>
  </span>
</td>

                        <td class="skill-description"><?php echo htmlspecialchars($skill['skill_description']); ?></td>
                        <td>
                            <button class="btn edit-btn" onclick="openEditForm(<?php echo $skill['id']; ?>)">Edit</button>
                            <a href="add_skill.php?delete=<?php echo $skill['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

<link rel="stylesheet" href="assets/css/sidebar.css">
<link rel="stylesheet" href="assets/css/add_skill.css">
<link rel="stylesheet" href="assets/css/footer.css">

<script>
function openEditForm(skillId){
    const row = document.querySelector(`tr[data-id='${skillId}']`);
    document.getElementById('skill_id').value = skillId;
    document.getElementById('skill_name').value = row.querySelector('.skill-name').textContent;
    document.getElementById('skill_category').value = row.querySelector('.skill-category').textContent;
    const type = row.querySelector('.skill-type').textContent;
    document.getElementById('type_offer').checked = (type==='offer');
    document.getElementById('type_want').checked = (type==='want');
    document.getElementById('skill_description').value = row.querySelector('.skill-description').textContent;
    document.getElementById('submitBtn').textContent = 'Update Skill';

    // Smooth scroll to the form
    document.getElementById('addSkillForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}


function resetForm(){
    document.getElementById('skill_id').value = '';
    document.getElementById('addSkillForm').reset();
    document.getElementById('submitBtn').textContent = 'Add Skill';
}
</script>
