<?php
// ============================================================
//  COSUP V2 — admin/manage-sites.php
//  Admin — Manage Clinic Sites
//  The 5 Watermelons | XISD5319 | IIE Rosebank College
// ============================================================

$page_title = 'Manage Sites';
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

// Handle site update
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['site_id'])) {
    $siteId  = intval($_POST['site_id']);
    $name    = $conn->real_escape_string(trim($_POST['site_name']       ?? ''));
    $addr    = $conn->real_escape_string(trim($_POST['address']         ?? ''));
    $hours   = $conn->real_escape_string(trim($_POST['operating_hours'] ?? ''));
    $phone   = $conn->real_escape_string(trim($_POST['phone']           ?? ''));

    if (empty($name) || empty($addr)) {
        $error = 'Site name and address are required.';
    } else {
        $conn->query(
            "UPDATE sites SET
                site_name       = '$name',
                address         = '$addr',
                operating_hours = '$hours',
                phone           = '$phone',
                updated_at      = NOW()
             WHERE site_id = $siteId"
        );
        $conn->query(
            "INSERT INTO audit_logs
                (admin_user_id, action, target_table, target_id)
             VALUES
                ($adminId,
                 'Updated site: $name',
                 'sites', $siteId)"
        );
        $success = "Site '$name' updated successfully!";
    }
}

// Fetch all sites
$allSites = $conn->query(
    "SELECT * FROM sites ORDER BY is_main_centre DESC, site_name ASC"
)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="page-hero-text">
      <span class="section-tag">Admin · Sites</span>
      <h1 class="page-hero-title">Manage Sites</h1>
      <p class="page-hero-desc">
        Update clinic information, hours and contact details.
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

    <div style="display:flex;flex-direction:column;gap:16px;">
      <?php foreach ($allSites as $site): ?>
      <div class="dashboard-card">
        <details>
          <summary style="cursor:pointer;display:flex;
                          align-items:center;gap:12px;
                          list-style:none;">
            <span class="site-num <?= $site['is_main_centre'] ? 'main' : '' ?>"
                  style="flex-shrink:0;">
              <?= $site['site_id'] ?>
            </span>
            <strong style="font-size:15px;color:var(--text);">
              <?= htmlspecialchars($site['site_name']) ?>
              <?php if ($site['is_main_centre']): ?>
              <span class="main-badge">Main Centre</span>
              <?php endif; ?>
            </strong>
            <span style="margin-left:auto;font-size:12px;
                         color:var(--muted);">
              Click to edit ▼
            </span>
          </summary>

          <form method="POST"
                style="margin-top:20px;display:grid;
                       grid-template-columns:1fr 1fr;gap:16px;">
            <input type="hidden" name="site_id"
                   value="<?= $site['site_id'] ?>"/>

            <div class="form-group" style="margin:0;">
              <label>Site Name</label>
              <div class="input-wrap">
                <span class="input-icon"><i class="fa-solid fa-hospital"></i></span>
                <input type="text" name="site_name"
                       value="<?= htmlspecialchars($site['site_name']) ?>"
                       required/>
              </div>
            </div>

            <div class="form-group" style="margin:0;">
              <label>Phone</label>
              <div class="input-wrap">
                <span class="input-icon"><i class="fa-solid fa-phone"></i></span>
                <input type="text" name="phone"
                       value="<?= htmlspecialchars($site['phone'] ?? '') ?>"/>
              </div>
            </div>

            <div class="form-group" style="margin:0;grid-column:1/-1;">
              <label>Address</label>
              <div class="input-wrap">
                <span class="input-icon"><i class="fa-solid fa-location-dot"></i></span>
                <input type="text" name="address"
                       value="<?= htmlspecialchars($site['address']) ?>"
                       required/>
              </div>
            </div>

            <div class="form-group" style="margin:0;grid-column:1/-1;">
              <label>Operating Hours</label>
              <div class="input-wrap">
                <span class="input-icon"><i class="fa-regular fa-clock"></i></span>
                <input type="text" name="operating_hours"
                       value="<?= htmlspecialchars($site['operating_hours'] ?? '') ?>"
                       placeholder="e.g. Mon–Fri 08:00–16:00"/>
              </div>
            </div>

            <div style="grid-column:1/-1;">
              <button type="submit" class="btn-form-submit"
                      style="width:auto;padding:12px 32px;">
                Save Changes
              </button>
            </div>
          </form>
        </details>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:24px;">
      <a href="<?= BASE_URL ?>/admin/dashboard.php"
         class="contact-link">← Back to Dashboard</a>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>