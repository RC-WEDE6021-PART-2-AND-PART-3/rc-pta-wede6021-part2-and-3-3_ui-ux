<?php
// ============================================================
//  COSUP V2 — admin/manage-news.php
//  Admin — Publish & Manage News Items
//  The 5 Watermelons | XISD5319 | IIE Rosebank College
// ============================================================

$page_title = 'Manage News';
require_once __DIR__ . '/../includes/header.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['cosup_user_id']) ||
    !in_array($_SESSION['cosup_role'] ?? '', ['admin','superadmin'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$conn    = getDBConnection();
$adminId = $_SESSION['cosup_user_id'];
$success = '';
$error   = '';

// Handle publish new article
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) && $_POST['action'] === 'publish') {
    $title  = $conn->real_escape_string(trim($_POST['title']  ?? ''));
    $body   = $conn->real_escape_string(trim($_POST['body']   ?? ''));
    $author = $conn->real_escape_string(trim($_POST['author'] ?? 'COSUP Communications'));

    if (empty($title) || empty($body)) {
        $error = 'Title and body are required.';
    } else {
        $conn->query(
            "INSERT INTO news_items
                (title, body, author, is_published, published_at)
             VALUES
                ('$title', '$body', '$author', 1, NOW())"
        );
        $newsId = $conn->insert_id;
        $conn->query(
            "INSERT INTO audit_logs
                (admin_user_id, action, target_table, target_id)
             VALUES
                ($adminId,
                 'Published news item: $title',
                 'news_items', $newsId)"
        );
        $success = 'News item published successfully!';
    }
}

// Handle unpublish/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $newsId    = intval($_POST['news_id']);
    $newStatus = intval($_POST['new_status']);
    $conn->query(
        "UPDATE news_items SET is_published = $newStatus
         WHERE news_id = $newsId"
    );
    $success = 'News item updated.';
}

// Fetch all news
$allNews = $conn->query(
    "SELECT * FROM news_items ORDER BY created_at DESC"
)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="page-hero-text">
      <span class="section-tag">Admin · News</span>
      <h1 class="page-hero-title">Manage News</h1>
      <p class="page-hero-desc">
        Publish and manage COSUP news and announcements.
      </p>
    </div>
  </div>
</section>

<section class="section" style="background:var(--bg);padding-top:48px;">
  <div class="container">

    <?php if ($success): ?>
    <div class="auth-message success" style="margin-bottom:24px;">
      <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="auth-message error" style="margin-bottom:24px;">
      <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;
                gap:24px;align-items:start;">

      <!-- Publish Form -->
      <div class="dashboard-card">
        <h3 class="dashboard-card-title">Publish New Article</h3>
        <form method="POST">
          <input type="hidden" name="action" value="publish"/>
          <div class="form-group">
            <label>Title *</label>
            <div class="input-wrap">
              <span class="input-icon"><i class="fa-regular fa-newspaper"></i></span>
              <input type="text" name="title"
                     placeholder="Article title" required/>
            </div>
          </div>
          <div class="form-group">
            <label>Author</label>
            <div class="input-wrap">
              <span class="input-icon"><i class="fa-solid fa-pen-nib"></i></span>
              <input type="text" name="author"
                     value="COSUP Communications"/>
            </div>
          </div>
          <div class="form-group">
            <label>Body *</label>
            <textarea name="body" rows="8"
                      placeholder="Write your article here..."
                      required
                      style="width:100%;padding:12px 16px;
                             border:1px solid var(--border);
                             border-radius:10px;
                             background:var(--bg);
                             color:var(--text);
                             font-family:inherit;
                             font-size:14px;outline:none;
                             resize:vertical;transition:all 0.2s;"
                      onfocus="this.style.borderColor='var(--green-light)'"
                      onblur="this.style.borderColor='var(--border)'">
            </textarea>
          </div>
          <button type="submit" class="btn-form-submit">
            Publish Article
          </button>
        </form>
      </div>

      <!-- All Articles -->
      <div class="dashboard-card">
        <h3 class="dashboard-card-title">
          All Articles (<?= count($allNews) ?>)
        </h3>
        <div style="display:flex;flex-direction:column;gap:12px;
                    max-height:600px;overflow-y:auto;">
          <?php foreach ($allNews as $item): ?>
          <div style="padding:14px;border-radius:10px;
                      border:1px solid var(--border);
                      background:var(--bg2);">
            <div style="display:flex;justify-content:space-between;
                        align-items:flex-start;gap:10px;
                        margin-bottom:8px;">
              <h4 style="font-size:14px;font-weight:700;
                          color:var(--text);line-height:1.3;">
                <?= htmlspecialchars($item['title']) ?>
              </h4>
              <span class="status-badge <?= $item['is_published']
                ? 'status-completed' : 'status-cancelled' ?>">
                <?= $item['is_published'] ? 'Live' : 'Draft' ?>
              </span>
            </div>
            <p style="font-size:12px;color:var(--muted);
                      margin-bottom:10px;">
              By <?= htmlspecialchars($item['author']) ?> ·
              <?= date('d M Y', strtotime($item['created_at'])) ?>
            </p>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="action" value="toggle"/>
              <input type="hidden" name="news_id"
                     value="<?= $item['news_id'] ?>"/>
              <input type="hidden" name="new_status"
                     value="<?= $item['is_published'] ? 0 : 1 ?>"/>
              <button type="submit"
                      style="font-size:11px;font-weight:700;
                             color:<?= $item['is_published']
                               ? 'var(--red)' : 'var(--green)' ?>;
                             background:none;border:none;
                             cursor:pointer;">
                <?= $item['is_published'] ? 'Unpublish' : 'Publish' ?>
              </button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <div style="margin-top:24px;">
      <a href="<?= BASE_URL ?>/admin/dashboard.php"
         class="contact-link">← Back to Dashboard</a>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>