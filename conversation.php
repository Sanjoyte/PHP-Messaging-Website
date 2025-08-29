<?php
session_start();
require 'connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$my_id = $_SESSION['user_id'];

// Get the conversation partner's ID
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die("Invalid user.");
}
$other_id = intval($_GET['user_id']);

// Get other user's name
$stmt = $connection->prepare("SELECT name FROM user WHERE user_id = ?");
$stmt->bind_param("i", $other_id);
$stmt->execute();
$stmt->bind_result($other_name);
if (!$stmt->fetch()) {
    die("User not found.");
}
$stmt->close();

// ---------------------
// Mark unread messages as seen
// ---------------------
$update = $connection->prepare("
    UPDATE messages
    SET seen = 1
    WHERE sender_id = ? AND receiver_id = ? AND seen = 0
");
$update->bind_param("ii", $other_id, $my_id);
$update->execute();
$update->close();

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

function decryptMessage($encrypted) {
    $key = hash('sha256', SECRET_KEY);
    $iv = substr(hash('sha256', SECRET_IV), 0, 16);
    return openssl_decrypt($encrypted, CIPHER_METHOD, $key, 0, $iv);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text'])) {
    $message_text = trim($_POST['message_text']);
    $message_text = encryptMessage($message_text);
    if ($message_text !== "") {
        $stmt = $connection->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $my_id, $other_id, $message_text);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: conversation.php?user_id=" . $other_id);
    exit();
}

// ---------------------
// Get conversation messages
// ---------------------
$sql = "
SELECT 
    m.message_id,
    m.sender_id,
    m.receiver_id,
    u1.name AS sender_name,
    m.message_text,
    m.sent_at,
    m.seen
FROM messages m
JOIN user u1 ON u1.user_id = m.sender_id
WHERE (m.sender_id = ? AND m.receiver_id = ?)
   OR (m.sender_id = ? AND m.receiver_id = ?)
ORDER BY m.sent_at ASC
";

$stmt = $connection->prepare($sql);
$stmt->bind_param("iiii", $my_id, $other_id, $other_id, $my_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Conversation with <?php echo htmlspecialchars($other_name); ?></title>
</head>
<body>
    <h2>Conversation with <?php echo htmlspecialchars($other_name); ?></h2>

    <!-- Messages -->
    <div>
        <?php if (empty($messages)): ?>
            <p>No messages yet.</p>
        <?php else: ?>
            <?php foreach ($messages as $m): ?>
                <p>
                    <strong><?php echo htmlspecialchars($m['sender_name']); ?>:</strong>
                    <?php echo htmlspecialchars(decryptMessage($m['message_text'])); ?>
                    <br>
                    <small><?php echo $m['sent_at']; ?></small>
                </p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>


    <hr>

    <!-- Send New Message -->
    <form method="POST" action="">
        <textarea name="message_text" placeholder="Type your message..." required></textarea>
        <br><br>
        <button type="submit">Send</button>
    </form>

    <br>
    <a href="messages.php">Back to Messages</a>
</body>
</html>
