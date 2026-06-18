<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: /Pastimes/browse.php'); exit; }

$conn = getConnection();

// Get product
$stmt = $conn->prepare(
    "SELECT c.*, u.fullName AS sellerName, u.userID AS sellerID, u.email AS sellerEmail
     FROM tblClothing c
     JOIN tblUser u ON c.sellerID = u.userID
     WHERE c.clothingID = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) { header('Location: /Pastimes/browse.php'); exit; }

// Related items
$related = [];
$rs = $conn->prepare(
    "SELECT clothingID, brand, description, price, imagePath, clothingCondition
     FROM tblClothing
     WHERE category = ? AND clothingID != ? AND status = 'available'
     LIMIT 4"
);
$rs->bind_param('si', $item['category'], $id);
$rs->execute();
$rr = $rs->get_result();
while ($r = $rr->fetch_assoc()) $related[] = $r;

// Handle add to cart
$cartMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['userID'])) {
        header('Location: /Pastimes/login.php'); exit;
    }
    if ($_SESSION['role'] !== 'buyer') {
        $cartMsg = 'error:Only buyers can add items to cart.';
    } else {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $alreadyIn = false;
        foreach ($_SESSION['cart'] as $ci) {
            if ($ci['clothingID'] == $id) { $alreadyIn = true; break; }
        }
        if ($alreadyIn) {
            $cartMsg = 'error:This item is already in your cart.';
        } else {
            $_SESSION['cart'][] = [
                'clothingID'  => $item['clothingID'],
                'brand'       => $item['brand'],
                'description' => $item['description'],
                'price'       => $item['price'],
                'imagePath'   => $item['imagePath'],
            ];
            $cartMsg = 'success:Item added to cart!';
        }
    }
}

// Handle wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_wishlist'])) {
    if (!isset($_SESSION['userID'])) {
        header('Location: /Pastimes/login.php'); exit;
    }
    $uid = $_SESSION['userID'];
    $ws = $conn->prepare("INSERT IGNORE INTO tblWishlist (userID, clothingID) VALUES (?,?)");
    $ws->bind_param('ii', $uid, $id);
    $ws->execute();
    $cartMsg = 'success:Added to wishlist!';
}

$conn->close();
include 'includes/header.php';

$msgType = $msgText = '';
if ($cartMsg) {
    [$msgType, $msgText] = explode(':', $cartMsg, 2);
}
?>

<!-- Lightbox -->
<div id="lightbox" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,.92); align-items:center; justify-content:center;">
    <button onclick="closeLightbox()" style="position:absolute; top:20px; right:30px; background:none; border:none; color:var(--gold); font-size:2rem; cursor:pointer;">&times;</button>
    <img id="lightboxImg" src="" alt="" style="max-width:90vw; max-height:90vh; border-radius:8px; border:2px solid var(--gold);">
</div>

<div class="container section">

    <?php if ($msgText): ?>
    <div class="alert alert-<?php echo $msgType; ?> mb-lg">
        <i class="fas fa-<?php echo $msgType==='success'?'check-circle':'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($msgText); ?>
    </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:var(--space-xl);">
        <a href="/Pastimes/index.php" style="color:var(--text-muted);">Home</a>
        <span style="margin:0 .5rem;">›</span>
        <a href="/Pastimes/browse.php" style="color:var(--text-muted);">Browse</a>
        <span style="margin:0 .5rem;">›</span>
        <a href="/Pastimes/browse.php?category=<?php echo urlencode($item['category']); ?>" style="color:var(--text-muted);"><?php echo htmlspecialchars($item['category']); ?></a>
        <span style="margin:0 .5rem;">›</span>
        <span style="color:var(--gold);"><?php echo htmlspecialchars($item['brand']); ?></span>
    </div>

    <div class="grid-2" style="gap:var(--space-2xl); align-items:start;">

        <!-- Image -->
        <div>
            <div class="product-image-box" style="position:relative; cursor:zoom-in;" onclick="openLightbox('/Pastimes/<?php echo htmlspecialchars($item['imagePath']); ?>')">
                <img
                    src="/Pastimes/<?php echo htmlspecialchars($item['imagePath']); ?>"
                    alt="<?php echo htmlspecialchars($item['brand']); ?>"
                    onerror="this.src='/Pastimes/images/placeholder.png'"
                    style="width:100%; height:100%; object-fit:cover;"
                >
                <!-- Zoom overlay -->
                <div style="position:absolute; inset:0; background:rgba(0,0,0,0); display:flex; align-items:center; justify-content:center; transition:background .2s;"
                     onmouseover="this.style.background='rgba(0,0,0,0.35)'; this.querySelector('span').style.opacity='1';"
                     onmouseout="this.style.background='rgba(0,0,0,0)'; this.querySelector('span').style.opacity='0';">
                    <span style="opacity:0; transition:opacity .2s; font-size:2.5rem; color:white;">
                        <i class="fas fa-search-plus"></i>
                    </span>
                </div>
                <!-- Condition badge -->
                <div style="position:absolute; top:12px; left:12px;">
                    <span class="badge badge-gold"><?php echo htmlspecialchars($item['clothingCondition']); ?></span>
                </div>
            </div>
            <p style="font-size:0.75rem; color:var(--text-muted); text-align:center; margin-top:.5rem;">
                <i class="fas fa-search-plus"></i> Click image to zoom
            </p>
        </div>

        <!-- Details -->
        <div>
            <div style="margin-bottom:var(--space-sm);">
                <span class="badge badge-gold"><?php echo htmlspecialchars($item['category']); ?></span>
                <span class="badge badge-muted" style="margin-left:.5rem;"><?php echo htmlspecialchars($item['size']); ?></span>
            </div>

            <h1 style="font-family:var(--font-display); font-size:1.8rem; color:var(--gold); margin-bottom:var(--space-xs);">
                <?php echo htmlspecialchars($item['brand']); ?>
            </h1>

            <p style="color:var(--text-secondary); font-size:1rem; line-height:1.7; margin-bottom:var(--space-lg);">
                <?php echo htmlspecialchars($item['description']); ?>
            </p>

            <div style="font-family:var(--font-display); font-size:2.2rem; color:var(--gold); font-weight:700; margin-bottom:var(--space-lg);">
                R <?php echo number_format($item['price'], 2); ?>
            </div>

            <!-- Details grid -->
            <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-md); padding:var(--space-lg); margin-bottom:var(--space-xl);">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md);">
                    <div>
                        <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px;">Brand</div>
                        <div style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($item['brand']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px;">Category</div>
                        <div style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($item['category']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px;">Size</div>
                        <div style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($item['size']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px;">Condition</div>
                        <div style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($item['clothingCondition']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px;">Seller</div>
                        <div style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($item['sellerName']); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px;">Status</div>
                        <span class="badge badge-<?php echo $item['status']==='available'?'success':'muted'; ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <?php if ($item['status'] === 'available'): ?>
            <div style="display:flex; gap:var(--space-md); flex-wrap:wrap; margin-bottom:var(--space-md);">

                <?php if (isset($_SESSION['userID']) && $_SESSION['role'] === 'buyer'): ?>
                <form method="POST" style="flex:1;">
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-full btn-lg">
                        <i class="fas fa-shopping-bag"></i> Add to Cart
                    </button>
                </form>
                <form method="POST">
                    <button type="submit" name="add_wishlist" class="btn btn-outline btn-lg">
                        <i class="fas fa-heart"></i>
                    </button>
                </form>

                <?php elseif (!isset($_SESSION['userID'])): ?>
                <a href="/Pastimes/login.php" class="btn btn-primary btn-full btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Login to Buy
                </a>

                <?php else: ?>
                <div class="alert alert-info" style="width:100%;">
                    <i class="fas fa-info-circle"></i>
                    Only buyers can purchase items.
                    <?php if ($_SESSION['role'] === 'seller'): ?>
                    You are logged in as a seller.
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>

            <!-- Message Seller -->
            <?php if (isset($_SESSION['userID']) && $_SESSION['userID'] !== $item['sellerID']): ?>
            <a href="/Pastimes/messages.php?to=<?php echo $item['sellerID']; ?>&item=<?php echo $item['clothingID']; ?>" class="btn btn-ghost btn-full">
                <i class="fas fa-comment"></i> Message Seller
            </a>
            <?php endif; ?>

            <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> This item is no longer available.
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Related Items -->
    <?php if ($related): ?>
    <div style="margin-top:var(--space-3xl);">
        <h2 style="font-family:var(--font-display); font-size:1.5rem; color:var(--text-primary); margin-bottom:var(--space-xl);">
            More in <span style="color:var(--gold);"><?php echo htmlspecialchars($item['category']); ?></span>
        </h2>
        <div class="products-grid">
            <?php foreach ($related as $r): ?>
            <a href="product.php?id=<?php echo $r['clothingID']; ?>" class="card">
                <div class="card-img-placeholder card-position-relative" style="position:relative;">
                    <img
                        src="/Pastimes/<?php echo htmlspecialchars($r['imagePath']); ?>"
                        alt="<?php echo htmlspecialchars($r['brand']); ?>"
                        onerror="this.src='/Pastimes/images/placeholder.png'"
                        loading="lazy"
                    >
                    <span class="card-badge"><?php echo htmlspecialchars($r['clothingCondition']); ?></span>
                </div>
                <div class="card-body">
                    <div class="card-brand"><?php echo htmlspecialchars($r['brand']); ?></div>
                    <div class="card-title"><?php echo htmlspecialchars(mb_strimwidth($r['description'],0,50,'…')); ?></div>
                    <div class="card-price">R <?php echo number_format($r['price'],2); ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Lightbox Script -->
<script>
function openLightbox(src) {
    var lb = document.getElementById('lightbox');
    var img = document.getElementById('lightboxImg');
    img.src = src;
    lb.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
}
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});
</script>

<?php include 'includes/footer.php'; ?>