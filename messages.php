<?php
session_start();
require 'connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$my_id = $_SESSION['user_id'];

// ---------------------
// Handle new message
// ---------------------
// define a secret key (keep this safe, ideally outside repo/env var)
define("SECRET_KEY", "your-32-char-secret-key-here"); 
define("SECRET_IV", "your-16-char-iv-here"); 
define("CIPHER_METHOD", "AES-256-CBC");

function encryptMessage($message) {
    $key = hash('sha256', SECRET_KEY);
    $iv = substr(hash('sha256', SECRET_IV), 0, 16);
    return openssl_encrypt($message, CIPHER_METHOD, $key, 0, $iv);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['message_text'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $message_text = trim($_POST['message_text']);
    $message_text = encryptMessage($message_text);
    if ($receiver_id > 0 && $message_text !== "") {
        $stmt = $connection->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $my_id, $receiver_id, $message_text);
        $stmt->execute();
        $stmt->close();
    }
    // Refresh to avoid resubmission
    header("Location: messages.php");
    exit();
}

// ---------------------
// Get conversation list
// ---------------------
$sql = "
SELECT 
    u.user_id,
    u.name,
    MAX(m.sent_at) AS last_message_time,
    SUM(CASE WHEN m.receiver_id = ? AND m.seen = 0 THEN 1 ELSE 0 END) AS unread_count
FROM messages m
JOIN user u 
    ON (u.user_id = m.sender_id AND m.receiver_id = ?)
    OR (u.user_id = m.receiver_id AND m.sender_id = ?)
WHERE ? IN (m.sender_id, m.receiver_id)
GROUP BY u.user_id, u.name
ORDER BY last_message_time DESC
";

$stmt = $connection->prepare($sql);
$stmt->bind_param("iiii", $my_id, $my_id, $my_id, $my_id);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
</head>
<body>
    <h2>Messages</h2>

    <!-- Create New Message -->
    <h3>Create Message</h3>
    <form method="POST" action="messages.php">
        <label for="receiver_id">Receiver User ID:</label>
        <input type="number" name="receiver_id" id="receiver_id" required>
        <br><br>
        <textarea name="message_text" placeholder="Type your message..." required></textarea>
        <br><br>
        <button type="submit">Send</button>
    </form>

    <hr>

    <!-- Conversation List -->
    <h3>Your Conversations</h3>
    <?php if (empty($conversations)): ?>
        <p>No conversations yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($conversations as $c): ?>
                <li>
                    <a href="conversation.php?user_id=<?php echo $c['user_id']; ?>"
                       <?php if ($c['unread_count'] > 0) echo 'style="font-weight:bold;"'; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <br>
    <a href="user.php">Back to Dashboard</a>
</body>
</html>
