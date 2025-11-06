<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /skillswap/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
include 'includes/sidebar.php';

// Fetch chat partners (users with accepted swaps)
$query = "
    SELECT DISTINCT 
        IF(sr.sender_id = ?, sr.receiver_id, sr.sender_id) AS chat_user_id,
        u.name, u.last_active
    FROM swap_requests sr
    JOIN users u 
      ON u.id = IF(sr.sender_id = ?, sr.receiver_id, sr.sender_id)
    WHERE (sr.sender_id = ? OR sr.receiver_id = ?)
      AND sr.status = 'accepted'
      AND sr.chat_enabled = 1
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$chat_users = $stmt->get_result();

$selected_user_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$receiver = null;
$is_online = false;

// If a user is selected, fetch their info
if ($selected_user_id) {
    $receiver = $mysqli->query("SELECT name, last_active FROM users WHERE id=$selected_user_id")->fetch_assoc();
    if ($receiver) {
        $is_online = (strtotime($receiver['last_active']) > time() - 120); // active within 2 mins
    }
}
?>

<main class="messages-main">
  <div class="messages-wrapper">
    <!-- CHAT SIDEBAR -->
    <div class="chat-sidebar">
      <div class="message-header">
        <h3>Chats</h3>
      </div>
      <ul class="chat-list">
        <?php if ($chat_users->num_rows > 0): ?>
          <?php while ($chat = $chat_users->fetch_assoc()): ?>
            <li class="<?php echo $selected_user_id == $chat['chat_user_id'] ? 'active' : ''; ?>">
              <a href="?user=<?php echo $chat['chat_user_id']; ?>">
                <div class="chat-avatar"><i class="fas fa-user-circle"></i></div>
                <div class="chat-info">
                  <strong><?php echo htmlspecialchars($chat['name']); ?></strong>
                </div>
              </a>
            </li>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="no-chat">No active chats yet.</p>
        <?php endif; ?>
      </ul>
    </div>

    <!-- MAIN CHAT WINDOW -->
    <div class="chat-window">
      <?php if ($selected_user_id && $receiver): ?>
        <?php
          $msg_query = "
            SELECT * FROM messages 
            WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
            ORDER BY created_at ASC
          ";
          $stmt = $mysqli->prepare($msg_query);
          $stmt->bind_param("iiii", $user_id, $selected_user_id, $selected_user_id, $user_id);
          $stmt->execute();
          $messages = $stmt->get_result();
        ?>

        <div class="chat-header">
          <div class="chat-user-info">
            <i class="fas fa-user-circle"></i>
            <div>
              <h4>
                <?php echo htmlspecialchars($receiver['name']); ?>
                <span class="status-dot <?php echo ($is_online ? 'online' : 'offline'); ?>"></span>
              </h4>
              <p id="typingStatus"></p>
            </div>
          </div>
        </div>

        <div class="chat-box" id="chat-box">
          <?php while ($msg = $messages->fetch_assoc()): ?>
            <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
              <div class="bubble">
                <?php 
                if (!empty($msg['message'])) echo nl2br(htmlspecialchars($msg['message']));
                if (!empty($msg['file_path'])) {
                    $ext = strtolower(pathinfo($msg['file_path'], PATHINFO_EXTENSION));
                    echo "<div class='chat-file'>";
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo "<img src='" . htmlspecialchars($msg['file_path']) . "' class='chat-image'>";
                    } elseif (in_array($ext, ['mp3', 'wav'])) {
                        echo "<audio controls><source src='" . htmlspecialchars($msg['file_path']) . "' type='audio/$ext'></audio>";
                    } else {
                        echo "<a href='" . htmlspecialchars($msg['file_path']) . "' target='_blank'><i class='fas fa-file-alt'></i> Download File</a>";
                    }
                    echo "</div>";
                }
                ?>
              </div>

              <small class="timestamp">
                <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                <?php if ($msg['sender_id'] == $user_id): ?>
                  <i class="fas fa-check-double read-status <?php echo $msg['is_read'] ? 'read' : ''; ?>"></i>
                  <input type="checkbox" class="msg-select" value="<?php echo $msg['id']; ?>">
                <?php endif; ?>
              </small>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- ✅ Delete bar -->
        <div class="delete-bar" id="deleteBar" style="display:none;">
          <form method="POST" action="messages_delete.php" id="deleteForm">
            <input type="hidden" name="message_ids" id="messageIds">
            <button type="submit" class="delete-btn"><i class="fas fa-trash"></i> Delete Selected</button>
          </form>
        </div>

        <form method="POST" class="chat-form" action="messages_send.php" enctype="multipart/form-data">
  <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id; ?>">

  <div class="preview-area" id="previewArea"></div>

  <textarea id="message" name="message" placeholder="Type a message..."></textarea>

  <!-- 📎 File upload before send -->
  <label for="file" class="file-upload" title="Attach file">
    <i class="fas fa-paperclip"></i>
  </label>
  <input type="file" id="file" name="file" accept="image/*,audio/*,.pdf,.doc,.docx" style="display:none;">

  <button type="submit" title="Send"><i class="fas fa-paper-plane"></i></button>
</form>


      <?php else: ?>
        <div class="no-chat-selected"><p>Select a user to start chatting.</p></div>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- JS: Typing Indicator -->
<script>
const messageInput = document.getElementById('message');
const typingStatus = document.getElementById('typingStatus');
let typingTimeout;
if (messageInput) {
  messageInput.addEventListener('input', () => {
    clearTimeout(typingTimeout);
    typingStatus.textContent = 'Typing...';
    typingTimeout = setTimeout(() => typingStatus.textContent = '', 2000);
  });
}
</script>

<!-- JS: File Preview -->
<script>
const fileInput = document.getElementById('file');
const previewArea = document.getElementById('previewArea');
fileInput.addEventListener('change', function() {
  previewArea.innerHTML = '';
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  if (file.type.startsWith('image/')) {
    reader.onload = e => {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'preview-image';
      const closeBtn = document.createElement('span');
      closeBtn.textContent = '×';
      closeBtn.className = 'preview-remove';
      closeBtn.onclick = () => { previewArea.innerHTML = ''; fileInput.value = ''; };
      previewArea.appendChild(img); previewArea.appendChild(closeBtn);
    };
    reader.readAsDataURL(file);
  } else {
    const fileInfo = document.createElement('div');
    fileInfo.className = 'preview-file';
    fileInfo.innerHTML = `<i class="fas fa-file-alt"></i> ${file.name}
      <span class="preview-remove" title="Remove">×</span>`;
    fileInfo.querySelector('.preview-remove').onclick = () => {
      previewArea.innerHTML = ''; fileInput.value = '';
    };
    previewArea.appendChild(fileInfo);
  }
});
</script>

<!-- JS: Delete Message Selection -->
<script>
const checkboxes = document.querySelectorAll('.msg-select');
const deleteBar = document.getElementById('deleteBar');
const messageIdsInput = document.getElementById('messageIds');

checkboxes.forEach(cb => {
  cb.addEventListener('change', () => {
    const selected = Array.from(checkboxes).filter(c => c.checked).map(c => c.value);
    if (selected.length > 0) {
      deleteBar.style.display = 'flex';
      messageIdsInput.value = selected.join(',');
    } else {
      deleteBar.style.display = 'none';
      messageIdsInput.value = '';
    }
  });
});
</script>

<link rel="stylesheet" href="assets/css/sidebar.css">
<link rel="stylesheet" href="assets/css/footer.css">
<link rel="stylesheet" href="/skillswap/assets/css/messages.css">
<script src="https://kit.fontawesome.com/a2e0e9e6d2.js" crossorigin="anonymous"></script>
<?php include 'includes/footer.php'; ?>
