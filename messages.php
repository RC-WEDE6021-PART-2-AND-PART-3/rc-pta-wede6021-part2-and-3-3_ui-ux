<?php
/**
 * ============================================================
 * PASTIMES — Online Second-Hand Clothing Store
 * File: messages.php
 * Description: Messaging system for buyer-seller communication
 * ============================================================
 */

session_start();
require_once 'includes/DBConn.php';

// Redirect if not logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['userID'];
$error = '';
$success = '';
$conversations = [];
$activeConversation = null;
$messages = [];

// Handle new message from product page
$toUserID = isset($_GET['to']) ? intval($_GET['to']) : 0;
$aboutItem = isset($_GET['item']) ? intval($_GET['item']) : 0;

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $receiverID = intval($_POST['receiverID']);
    $clothingID = !empty($_POST['clothingID']) ? intval($_POST['clothingID']) : null;
    $messageText = trim($_POST['messageText']);
    
    if (empty($messageText)) {
        $error = 'Please enter a message.';
    } elseif (strlen($messageText) < 5) {
        $error = 'Message must be at least 5 characters.';
    } else {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("INSERT INTO tblMessage (senderID, receiverID, clothingID, messageText, isRead, isBroadcast) VALUES (?, ?, ?, ?, 0, 0)");
            $clothingIDStr = $clothingID ? strval($clothingID) : null;
            $stmt->bind_param('ssss', $userID, $receiverID, $clothingIDStr, $messageText);
            $stmt->execute();
            $success = 'Message sent!';
            $conn->close();
            
            // Redirect to avoid form resubmission
            header("Location: messages.php?conversation=$receiverID");
            exit();
        } catch (Exception $e) {
            $error = 'Could not send message.';
        }
    }
}

// Fetch conversations
try {
    $conn = getConnection();
    
    // Get unique conversations (users you've messaged with)
    $sql = "SELECT DISTINCT 
                CASE WHEN m.senderID = ? THEN m.receiverID ELSE m.senderID END as otherUserID,
                u.username, u.fullName,
                (SELECT messageText FROM tblMessage WHERE 
                    (senderID = ? AND receiverID = u.userID) OR 
                    (senderID = u.userID AND receiverID = ?)
                ORDER BY sentAt DESC LIMIT 1) as lastMessage,
                (SELECT sentAt FROM tblMessage WHERE 
                    (senderID = ? AND receiverID = u.userID) OR 
                    (senderID = u.userID AND receiverID = ?)
                ORDER BY sentAt DESC LIMIT 1) as lastMessageTime
            FROM tblMessage m
            JOIN tblUser u ON (CASE WHEN m.senderID = ? THEN m.receiverID ELSE m.senderID END) = u.userID
            WHERE m.senderID = ? OR m.receiverID = ?
            ORDER BY lastMessageTime DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssss', $userID, $userID, $userID, $userID, $userID, $userID, $userID, $userID);
    $stmt->execute();
    $conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Also get broadcast messages
    $broadcastStmt = $conn->prepare("SELECT m.*, u.username, u.fullName 
                                     FROM tblMessage m 
                                     JOIN tblUser u ON m.senderID = u.userID 
                                     WHERE m.isBroadcast = 1 
                                     ORDER BY m.sentAt DESC LIMIT 5");
    $broadcastStmt->execute();
    $broadcasts = $broadcastStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // If viewing a specific conversation
    $conversationWith = isset($_GET['conversation']) ? intval($_GET['conversation']) : ($toUserID ? $toUserID : 0);
    
    if ($conversationWith > 0) {
        // Get other user info
        $stmt = $conn->prepare("SELECT userID, username, fullName FROM tblUser WHERE userID = ?");
        $stmt->bind_param('s', $conversationWith);
        $stmt->execute();
        $activeConversation = $stmt->get_result()->fetch_assoc();
        
        if ($activeConversation) {
            // Get messages
            $stmt = $conn->prepare("SELECT m.*, 
                                    CASE WHEN m.senderID = ? THEN 'outgoing' ELSE 'incoming' END as direction,
                                    c.brand, c.description as itemDescription
                                    FROM tblMessage m
                                    LEFT JOIN tblClothing c ON m.clothingID = c.clothingID
                                    WHERE (m.senderID = ? AND m.receiverID = ?) 
                                       OR (m.senderID = ? AND m.receiverID = ?)
                                    ORDER BY m.sentAt ASC");
            $stmt->bind_param('sssss', $userID, $userID, $conversationWith, $conversationWith, $userID);
            $stmt->execute();
            $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Mark messages as read
            $stmt = $conn->prepare("UPDATE tblMessage SET isRead = 1 WHERE senderID = ? AND receiverID = ? AND isRead = 0");
            $stmt->bind_param('ss', $conversationWith, $userID);
            $stmt->execute();
        }
    }
    
    // If starting a new conversation from product page, get item info
    $itemInfo = null;
    if ($aboutItem > 0) {
        $stmt = $conn->prepare("SELECT brand, description FROM tblClothing WHERE clothingID = ?");
        $stmt->bind_param('s', $aboutItem);
        $stmt->execute();
        $itemInfo = $stmt->get_result()->fetch_assoc();
    }
    
} catch (Exception $e) {
    $error = 'Could not load messages.';
}
?>
<?php include 'includes/header.php'; ?>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Messages</h1>
            <p>Communicate with buyers and sellers</p>
        </div>

        <!-- Messages Section -->
        <section class="section">
            <div class="container">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="inbox-layout">
                    <!-- Sidebar - Conversations List -->
                    <div class="inbox-sidebar">
                        <div class="inbox-search">
                            <input type="text" placeholder="Search messages..." id="searchMessages">
                        </div>
                        
                        <?php if (!empty($conversations)): ?>
                            <?php foreach ($conversations as $conv): ?>
                                <a href="?conversation=<?php echo $conv['otherUserID']; ?>" 
                                   class="inbox-thread <?php echo $conversationWith == $conv['otherUserID'] ? 'active' : ''; ?>">
                                    <div class="inbox-thread-avatar">
                                        <?php echo strtoupper(substr($conv['username'], 0, 1)); ?>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="inbox-thread-name"><?php echo htmlspecialchars($conv['username']); ?></div>
                                        <div class="inbox-thread-preview"><?php echo htmlspecialchars(substr($conv['lastMessage'], 0, 30)); ?>...</div>
                                    </div>
                                    <div class="inbox-thread-time">
                                        <?php echo date('M j', strtotime($conv['lastMessageTime'])); ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding: var(--space-xl);">
                                <p class="text-muted">No messages yet</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Chat Area -->
                    <div class="inbox-chat">
                        <?php if ($activeConversation): ?>
                            <div class="inbox-chat-header">
                                <div class="flex gap-md" style="align-items: center;">
                                    <div class="inbox-thread-avatar">
                                        <?php echo strtoupper(substr($activeConversation['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($activeConversation['username']); ?></strong>
                                        <?php if ($itemInfo): ?>
                                            <br><small class="text-muted">Re: <?php echo htmlspecialchars($itemInfo['brand']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="inbox-messages">
                                <?php if (!empty($messages)): ?>
                                    <?php foreach ($messages as $msg): ?>
                                        <div class="msg-bubble <?php echo $msg['direction']; ?>">
                                            <?php if ($msg['brand']): ?>
                                                <small class="text-gold" style="display: block; margin-bottom: 4px;">
                                                    Re: <?php echo htmlspecialchars($msg['brand']); ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php echo nl2br(htmlspecialchars($msg['messageText'])); ?>
                                            <div class="msg-time"><?php echo date('M j, g:i A', strtotime($msg['sentAt'])); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted" style="padding: var(--space-xl);">
                                        <p>Start the conversation!</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form method="POST" class="inbox-compose">
                                <input type="hidden" name="action" value="send_message">
                                <input type="hidden" name="receiverID" value="<?php echo $activeConversation['userID']; ?>">
                                <?php if ($aboutItem): ?>
                                    <input type="hidden" name="clothingID" value="<?php echo $aboutItem; ?>">
                                <?php endif; ?>
                                <input type="text" name="messageText" placeholder="Type your message..." required minlength="5" maxlength="2000">
                                <button type="submit" class="inbox-send-btn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="flex-center" style="height: 100%; flex-direction: column; padding: var(--space-2xl);">
                                <div style="font-size: 4rem; color: var(--text-faint); margin-bottom: var(--space-lg);">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3 style="color: var(--text-secondary); margin-bottom: var(--space-sm);">Select a conversation</h3>
                                <p class="text-muted">Choose a conversation from the left to start messaging</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Broadcast Messages -->
                <?php if (!empty($broadcasts)): ?>
                    <div class="settings-section mt-xl">
                        <h2>Platform Announcements</h2>
                        <p>Important updates from Pastimes</p>
                        
                        <?php foreach ($broadcasts as $broadcast): ?>
                            <div class="alert alert-info" style="margin-bottom: var(--space-md);">
                                <div class="flex-between">
                                    <strong><?php echo htmlspecialchars($broadcast['messageText']); ?></strong>
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($broadcast['sentAt'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

<?php 
if (isset($conn)) $conn->close();
include 'includes/footer.php'; 
?>