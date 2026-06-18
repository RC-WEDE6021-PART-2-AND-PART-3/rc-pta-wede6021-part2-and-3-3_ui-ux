<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['userID'])) {
    header('Location: /Pastimes/login.php'); exit;
}

$userID   = $_SESSION['userID'];
$fullName = $_SESSION['fullName'];
$conn     = getConnection();

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiverID = intval($_POST['receiverID'] ?? 0);
    $msgText    = trim($_POST['messageText'] ?? '');
    $clothingID = intval($_POST['clothingID'] ?? 0) ?: null;

    if ($msgText && $receiverID) {
        $stmt = $conn->prepare("INSERT INTO tblMessage (senderID,receiverID,clothingID,messageText) VALUES (?,?,?,?)");
        $stmt->bind_param('iiis', $userID, $receiverID, $clothingID, $msgText);
        $stmt->execute();
    }
    header("Location: /Pastimes/messages.php?to=$receiverID"); exit;
}

// Active conversation partner
$activeTo  = intval($_GET['to'] ?? 0);
$clothingID = intval($_GET['item'] ?? 0);

// Mark messages as read
if ($activeTo) {
    $conn->query("UPDATE tblMessage SET isRead=1 WHERE senderID=$activeTo AND receiverID=$userID");
}

// Get all conversation partners
$convSQL = "SELECT DISTINCT
                CASE WHEN senderID = $userID THEN receiverID ELSE senderID END AS partnerID
            FROM tblMessage
            WHERE (senderID = $userID OR receiverID = $userID)
              AND isBroadcast = 0
              AND receiverID IS NOT NULL";
$convResult = $conn->query($convSQL);
$partners   = [];
while ($r = $convResult->fetch_assoc()) {
    $pid  = $r['partnerID'];
    $user = $conn->query("SELECT userID, fullName, role FROM tblUser WHERE userID=$pid")->fetch_assoc();
    if ($user) {
        $unread = $conn->query("SELECT COUNT(*) AS c FROM tblMessage WHERE senderID=$pid AND receiverID=$userID AND isRead=0")->fetch_assoc()['c'];
        $lastMsg = $conn->query("SELECT messageText FROM tblMessage WHERE ((senderID=$userID AND receiverID=$pid) OR (senderID=$pid AND receiverID=$userID)) AND isBroadcast=0 ORDER BY sentAt DESC LIMIT 1")->fetch_assoc();
        $user['unread']  = $unread;
        $user['preview'] = $lastMsg ? mb_strimwidth($lastMsg['messageText'],0,40,'…') : '';
        $partners[] = $user;
    }
}

// If ?to= param, add that user if not already in list
if ($activeTo && !in_array($activeTo, array_column($partners,'userID'))) {
    $user = $conn->query("SELECT userID, fullName, role FROM tblUser WHERE userID=$activeTo")->fetch_assoc();
    if ($user) { $user['unread'] = 0; $user['preview'] = ''; $partners[] = $user; }
}

// Get conversation messages
$messages = [];
if ($activeTo) {
    $ms = $conn->prepare("SELECT m.*, u.fullName AS senderName
                          FROM tblMessage m
                          JOIN tblUser u ON m.senderID = u.userID
                          WHERE ((m.senderID=? AND m.receiverID=?) OR (m.senderID=? AND m.receiverID=?))
                            AND m.isBroadcast=0
                          ORDER BY m.sentAt ASC");
    $ms->bind_param('iiii', $userID, $activeTo, $activeTo, $userID);
    $ms->execute();
    $mr = $ms->get_result();
    while ($r = $mr->fetch_assoc()) $messages[] = $r;
}

// Get active partner info
$activePartner = null;
if ($activeTo) {
    $activePartner = $conn->query("SELECT userID, fullName, role FROM tblUser WHERE userID=$activeTo")->fetch_assoc();
}

// Broadcasts
$broadcasts = [];
$br = $conn->query("SELECT m.*, u.fullName AS senderName FROM tblMessage m JOIN tblUser u ON m.senderID=u.userID WHERE m.isBroadcast=1 ORDER BY m.sentAt DESC LIMIT 5");
while ($r = $br->fetch_assoc()) $broadcasts[] = $r;

$conn->close();
include 'includes/header.php';
?>

<div class="page-header">
    <h1><span style="color:var(--gold);">Messages</span></h1>
    <p>Chat with buyers and sellers</p>
</div>

<!-- Broadcasts -->
<?php if ($broadcasts): ?>
<div class="container" style="margin-top:var(--space-lg);">
    <?php foreach ($broadcasts as $b): ?>
    <div class="alert alert-info" style="margin-bottom:.5rem;">
        <i class="fas fa-bullhorn"></i>
        <strong><?php echo htmlspecialchars($b['senderName']); ?>:</strong>
        <?php echo htmlspecialchars($b['messageText']); ?>
        <span style="font-size:0.75rem; color:var(--text-muted); margin-left:auto;"><?php echo date('d M Y', strtotime($b['sentAt'])); ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="container" style="margin-top:var(--space-lg);">
    <div class="messages-layout" style="border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; min-height:500px;">

        <!-- Conversations List -->
        <div class="conversations-list">
            <div style="padding:var(--space-md) var(--space-lg); border-bottom:1px solid var(--border); font-size:0.85rem; font-weight:700; color:var(--gold);">
                Conversations
            </div>

            <?php if (empty($partners)): ?>
            <div style="padding:var(--space-lg); color:var(--text-muted); font-size:0.85rem; text-align:center;">
                No conversations yet.<br>
                <a href="/Pastimes/browse.php" style="color:var(--gold);">Browse items</a> and message a seller.
            </div>
            <?php endif; ?>

            <?php foreach ($partners as $p): ?>
            <a href="/Pastimes/messages.php?to=<?php echo $p['userID']; ?>"
               class="conversation-item <?php echo $activeTo==$p['userID']?'active':''; ?>"
               style="display:block; text-decoration:none;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div class="conversation-name"><?php echo htmlspecialchars($p['fullName']); ?></div>
                    <?php if ($p['unread'] > 0): ?>
                    <span class="badge badge-danger" style="font-size:0.65rem;"><?php echo $p['unread']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="conversation-preview"><?php echo htmlspecialchars($p['preview']); ?></div>
                <div style="font-size:0.7rem; color:var(--text-muted); margin-top:2px;">
                    <span class="badge badge-<?php echo $p['role']; ?>" style="font-size:0.6rem;"><?php echo ucfirst($p['role']); ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Messages Panel -->
        <div class="messages-panel">
            <?php if ($activeTo && $activePartner): ?>

            <!-- Header -->
            <div style="padding:var(--space-md) var(--space-lg); border-bottom:1px solid var(--border); display:flex; align-items:center; gap:var(--space-md); background:var(--bg-deep);">
                <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--navy-light),var(--gold-dark));border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);color:var(--gold-light);font-size:1rem;">
                    <?php echo strtoupper(substr($activePartner['fullName'],0,1)); ?>
                </div>
                <div>
                    <div style="font-weight:700; color:var(--text-primary); font-size:0.95rem;"><?php echo htmlspecialchars($activePartner['fullName']); ?></div>
                    <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo ucfirst($activePartner['role']); ?></div>
                </div>
            </div>

            <!-- Messages -->
            <div class="messages-container" id="messagesContainer">
                <?php if (empty($messages)): ?>
                <div style="text-align:center; color:var(--text-muted); font-size:0.85rem; margin:auto;">
                    Start the conversation!
                    <?php if ($clothingID): ?>
                    <br>Asking about item #<?php echo $clothingID; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php foreach ($messages as $msg): ?>
                <div style="display:flex; flex-direction:column; align-items:<?php echo $msg['senderID']==$userID?'flex-end':'flex-start'; ?>;">
                    <div class="message-bubble <?php echo $msg['senderID']==$userID?'outgoing':'incoming'; ?>">
                        <?php echo htmlspecialchars($msg['messageText']); ?>
                    </div>
                    <div class="message-time"><?php echo date('d M, H:i', strtotime($msg['sentAt'])); ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Send Form -->
            <div class="message-form-area">
                <form method="POST" style="display:flex; gap:var(--space-sm); width:100%;">
                    <input type="hidden" name="receiverID" value="<?php echo $activeTo; ?>">
                    <input type="hidden" name="clothingID" value="<?php echo $clothingID; ?>">
                    <input type="text" name="messageText" class="form-control" placeholder="Type a message..." required autocomplete="off">
                    <button type="submit" name="send_message" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>

            <?php else: ?>
            <div style="display:flex; align-items:center; justify-content:center; height:100%; color:var(--text-muted); font-size:0.9rem;">
                <div style="text-align:center;">
                    <div style="font-size:3rem; margin-bottom:1rem;"><i class="fas fa-comment" style="color:var(--gold);font-size:3rem;"></i></div>
                    <p>Select a conversation or</p>
                    <a href="/Pastimes/browse.php" class="btn btn-outline btn-sm" style="margin-top:.5rem;">Browse items to message sellers</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
// Auto scroll to bottom of messages
var mc = document.getElementById('messagesContainer');
if (mc) mc.scrollTop = mc.scrollHeight;
</script>

<?php include 'includes/footer.php'; ?>