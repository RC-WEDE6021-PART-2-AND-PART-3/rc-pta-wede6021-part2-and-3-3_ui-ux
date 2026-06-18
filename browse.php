<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/DBConn.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$conn = getConnection();

$category  = $_GET['category']  ?? '';
$brand     = $_GET['brand']     ?? '';
$size      = $_GET['size']      ?? '';
$condition = $_GET['condition'] ?? '';
$minPrice  = $_GET['minPrice']  ?? '';
$maxPrice  = $_GET['maxPrice']  ?? '';
$search    = $_GET['search']    ?? '';

$where = ["c.status = 'available'"];
$params = []; $types = '';

if ($category)  { $where[] = "c.category = ?";         $params[] = $category;  $types .= 's'; }
if ($brand)     { $where[] = "c.brand LIKE ?";          $params[] = "%$brand%"; $types .= 's'; }
if ($size)      { $where[] = "c.size = ?";              $params[] = $size;      $types .= 's'; }
if ($condition) { $where[] = "c.clothingCondition = ?"; $params[] = $condition; $types .= 's'; }
if ($minPrice)  { $where[] = "c.price >= ?";            $params[] = $minPrice;  $types .= 'd'; }
if ($maxPrice)  { $where[] = "c.price <= ?";            $params[] = $maxPrice;  $types .= 'd'; }
if ($search)    { $where[] = "(c.brand LIKE ? OR c.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }

$sql = "SELECT c.*, u.fullName AS sellerName FROM tblClothing c
        JOIN tblUser u ON c.sellerID = u.userID
        WHERE " . implode(' AND ', $where) . " ORDER BY c.dateAdded DESC";

$items = [];
if ($types) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
while ($r = $result->fetch_assoc()) $items[] = $r;
$conn->close();
include 'includes/header.php';
?>

<!-- Lightbox -->
<div id="lightbox" onclick="closeLightbox()" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.93);align-items:center;justify-content:center;flex-direction:column;gap:1rem;">
    <button onclick="closeLightbox()" style="position:absolute;top:20px;right:30px;background:none;border:none;color:var(--gold);font-size:2.5rem;cursor:pointer;z-index:10000;">&times;</button>
    <img id="lightboxImg" src="" alt="" style="max-width:88vw;max-height:82vh;border-radius:12px;border:2px solid var(--gold);box-shadow:0 0 60px rgba(201,168,76,.3);">
    <p id="lightboxCaption" style="color:var(--gold);font-family:var(--font-display);font-size:1.1rem;letter-spacing:.08em;"></p>
</div>

<div class="page-header">
    <h1>Browse <span style="color:var(--gold)">Collection</span></h1>
    <p>Discover premium second-hand fashion from verified sellers</p>
</div>

<div class="container section">

    <form method="GET" action="browse.php" style="margin-bottom:var(--space-xl);">
        <div style="display:flex;gap:var(--space-sm);">
            <input type="text" name="search" class="form-control" placeholder="Search by brand or description..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <?php if ($search||$category||$brand||$size||$condition): ?>
            <a href="browse.php" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="browse-layout">

        <aside class="filter-panel">
            <div class="filter-header">
                <h3><i class="fas fa-filter"></i> Filters</h3>
                <a href="browse.php" class="filter-clear">Clear all</a>
            </div>
            <form method="GET" action="browse.php" id="filterForm">
                <?php if ($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                <div class="filter-section">
                    <div class="filter-label">Category</div>
                    <?php foreach (['Men','Women','Footwear','Accessories'] as $cat): ?>
                    <label class="radio-option">
                        <input type="radio" name="category" value="<?php echo $cat; ?>" <?php echo $category===$cat?'checked':''; ?> onchange="this.form.submit()">
                        <?php echo $cat; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="filter-section">
                    <div class="filter-label">Condition</div>
                    <?php foreach (['Like New','Excellent','Good','Fair'] as $cond): ?>
                    <label class="radio-option">
                        <input type="radio" name="condition" value="<?php echo $cond; ?>" <?php echo $condition===$cond?'checked':''; ?> onchange="this.form.submit()">
                        <?php echo $cond; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="filter-section">
                    <div class="filter-label">Size</div>
                    <input type="hidden" name="size" id="sizeInput" value="<?php echo htmlspecialchars($size); ?>">
                    <div class="size-options">
                        <?php foreach (['XS','S','M','L','XL','XXL','38','39','40','41','42','43','44','One Size'] as $s): ?>
                        <button type="button" class="size-btn <?php echo $size===$s?'active':''; ?>" data-size="<?php echo $s; ?>"><?php echo $s; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm btn-full" style="margin-top:.5rem;">Apply Size</button>
                </div>
                <div class="filter-section">
                    <div class="filter-label">Price Range (R)</div>
                    <div style="display:flex;gap:.5rem;align-items:center;">
                        <input type="number" name="minPrice" class="form-control" placeholder="Min" value="<?php echo htmlspecialchars($minPrice); ?>" style="width:80px;">
                        <span style="color:var(--text-muted);">-</span>
                        <input type="number" name="maxPrice" class="form-control" placeholder="Max" value="<?php echo htmlspecialchars($maxPrice); ?>" style="width:80px;">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm btn-full" style="margin-top:.5rem;">Apply Price</button>
                </div>
            </form>
        </aside>

        <div>
            <div class="products-header">
                <div class="products-count">
                    Showing <span><?php echo count($items); ?></span> item<?php echo count($items)!==1?'s':''; ?>
                    <?php if ($category): ?> in <strong style="color:var(--gold);"><?php echo htmlspecialchars($category); ?></strong><?php endif; ?>
                </div>
            </div>

            <?php if ($items): ?>
            <div class="products-grid">
                <?php foreach ($items as $item): ?>
                <div class="card product-card">
                    <!-- Image -->
                    <a href="product.php?id=<?php echo $item['clothingID']; ?>" class="card-img-placeholder card-position-relative" style="display:block;">
                        <img
                            src="/Pastimes/<?php echo htmlspecialchars($item['imagePath']); ?>"
                            alt="<?php echo htmlspecialchars($item['brand']); ?>"
                            onerror="this.src='/Pastimes/images/placeholder.png'"
                            loading="lazy"
                            class="product-thumb"
                            id="img-<?php echo $item['clothingID']; ?>"
                        >
                        <span class="card-badge"><?php echo htmlspecialchars($item['clothingCondition']); ?></span>
                        <span class="card-badge-category"><?php echo htmlspecialchars($item['category']); ?></span>
                    </a>

                    <!-- Body -->
                    <div class="card-body">
                        <div class="card-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                        <div class="card-title"><?php echo htmlspecialchars(mb_strimwidth($item['description'],0,55,'…')); ?></div>
                        <div class="card-meta">Size: <?php echo htmlspecialchars($item['size']); ?> &bull; <?php echo htmlspecialchars($item['sellerName']); ?></div>

                        <!-- Price row with eye toggle -->
                        <div class="card-price-row">
                            <div class="card-price">R <?php echo number_format($item['price'],2); ?></div>
                            <button
                                class="eye-toggle"
                                id="eye-<?php echo $item['clothingID']; ?>"
                                onclick="toggleZoom(<?php echo $item['clothingID']; ?>, '/Pastimes/<?php echo htmlspecialchars($item['imagePath']); ?>', '<?php echo htmlspecialchars(addslashes($item['brand'])); ?>')"
                                title="Preview image"
                                aria-label="Toggle image zoom"
                            >
                                <i class="fas fa-eye" style="color:var(--gold);" id="eye-icon-<?php echo $item['clothingID']; ?>"></i>
                            </button>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div style="font-size:3rem;">🔍</div>
                <h2>No items found</h2>
                <p>Try adjusting your filters or search term.</p>
                <a href="browse.php" class="btn btn-primary">Clear Filters</a>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
var activeEye = null;

function toggleZoom(id, src, caption) {
    var eyeBtn  = document.getElementById('eye-' + id);
    var eyeIcon = document.getElementById('eye-icon-' + id);
    var lb      = document.getElementById('lightbox');

    // If same eye clicked again — close
    if (activeEye === id) {
        closeLightbox();
        return;
    }

    // Reset previous eye if any
    if (activeEye !== null) {
        var prevIcon = document.getElementById('eye-icon-' + activeEye);
        var prevBtn  = document.getElementById('eye-' + activeEye);
        if (prevIcon) { prevIcon.className = 'fas fa-eye'; prevIcon.style.color = 'var(--gold)'; }
        if (prevBtn)  prevBtn.classList.remove('eye-on');
    }

    // Open lightbox
    activeEye = id;
    document.getElementById('lightboxImg').src     = src;
    document.getElementById('lightboxCaption').textContent = caption;
    lb.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Switch to eye-slash (on state)
    eyeIcon.className = 'fas fa-eye-slash'; eyeIcon.style.color = 'var(--navy)';
    eyeBtn.classList.add('eye-on');
}

function closeLightbox() {
    var lb = document.getElementById('lightbox');
    lb.style.display = 'none';
    document.body.style.overflow = '';

    if (activeEye !== null) {
        var prevIcon = document.getElementById('eye-icon-' + activeEye);
        var prevBtn  = document.getElementById('eye-' + activeEye);
        if (prevIcon) { prevIcon.className = 'fas fa-eye'; prevIcon.style.color = 'var(--gold)'; }
        if (prevBtn)  prevBtn.classList.remove('eye-on');
    }
    activeEye = null;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});
</script>

<?php include 'includes/footer.php'; ?>