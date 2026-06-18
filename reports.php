<?php
// ============================================================
//  COSUP V2 — admin/reports.php
//  Admin — System Reports
//  The 5 Watermelons | XISD5319 | IIE Rosebank College
// ============================================================

$page_title = 'Reports';
require_once __DIR__ . '/../includes/header.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['cosup_user_id']) ||
    !in_array($_SESSION['cosup_role'] ?? '', ['admin','superadmin'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$conn = getDBConnection();

// Report data
$referralsByStatus = $conn->query(
    "SELECT status, COUNT(*) as total
     FROM referrals GROUP BY status"
)->fetch_all(MYSQLI_ASSOC);

$referralsBySource = $conn->query(
    "SELECT source, COUNT(*) as total
     FROM referrals GROUP BY source"
)->fetch_all(MYSQLI_ASSOC);

$hopelineByChannel = $conn->query(
    "SELECT channel_type, platform, COUNT(*) as total
     FROM hopeline_logs
     GROUP BY channel_type, platform"
)->fetch_all(MYSQLI_ASSOC);

$usersByLang = $conn->query(
    "SELECT lang_pref, COUNT(*) as total
     FROM users GROUP BY lang_pref
     ORDER BY total DESC"
)->fetch_all(MYSQLI_ASSOC);

$sitesByService = $conn->query(
    "SELECT sv.service_name,
            COUNT(ss.site_id) as site_count
     FROM services sv
     LEFT JOIN site_services ss ON sv.service_id = ss.service_id
     GROUP BY sv.service_id
     ORDER BY site_count DESC"
)->fetch_all(MYSQLI_ASSOC);

$conn->close();

$langs = [
    'en'=>'English','zu'=>'IsiZulu','ts'=>'Xitsonga',
    've'=>'TshiVenda','tn'=>'SeTswana/Sepedi',
    'af'=>'Afrikaans','xh'=>'Xhosa'
];
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="page-hero-text">
      <span class="section-tag">Admin · Reports</span>
      <h1 class="page-hero-title">System Reports</h1>
      <p class="page-hero-desc">
        Live data overview across all COSUP digital platform activity.
      </p>
    </div>
  </div>
</section>

<section class="section" style="background:var(--bg);padding-top:48px;">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;
                gap:24px;">

      <!-- Referrals by Status -->
      <div class="dashboard-card">
        <h3 class="dashboard-card-title">Referrals by Status</h3>
        <?php foreach ($referralsByStatus as $row): ?>
        <div style="display:flex;justify-content:space-between;
                    align-items:center;padding:10px 0;
                    border-bottom:1px solid var(--border2);">
          <span class="status-badge status-<?= $row['status'] ?>">
            <?= ucfirst($row['status']) ?>
          </span>
          <strong style="font-size:18px;color:var(--text);
                          font-family:'Playfair Display',serif;">
            <?= $row['total'] ?>
          </strong>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Referrals by Source -->
      <div class="dashboard-card">
        <h3 class="dashboard-card-title">Referrals by Source</h3>
        <?php foreach ($referralsBySource as $row): ?>
        <div style="display:flex;justify-content:space-between;
                    align-items:center;padding:10px 0;
                    border-bottom:1px solid var(--border2);">
          <span style="font-size:14px;font-weight:600;
                       color:var(--text);text-transform:capitalize;">
            <?= htmlspecialchars($row['source']) ?>
          </span>
          <strong style="font-size:18px;color:var(--green);
                          font-family:'Playfair Display',serif;">
            <?= $row['total'] ?>
          </strong>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Hopeline Usage -->
      <div class="dashboard-card">
        <h3 class="dashboard-card-title">Hopeline Usage</h3>
        <?php foreach ($hopelineByChannel as $row): ?>
        <div style="display:flex;justify-content:space-between;
                    align-items:center;padding:10px 0;
                    border-bottom:1px solid var(--border2);">
          <span style="font-size:14px;color:var(--text);">
            <?= ucfirst($row['channel_type']) ?> ·
            <?= ucfirst($row['platform']) ?>
          </span>
          <strong style="font-size:18px;color:var(--green);
                          font-family:'Playfair Display',serif;">
            <?= $row['total'] ?>
          </strong>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Users by Language -->
      <div class="dashboard-card">
        <h3 class="dashboard-card-title">Users by Language</h3>
        <?php foreach ($usersByLang as $row): ?>
        <div style="display:flex;justify-content:space-between;
                    align-items:center;padding:10px 0;
                    border-bottom:1px solid var(--border2);">
          <span style="font-size:14px;color:var(--text);">
            <?= $langs[$row['lang_pref']] ?? $row['lang_pref'] ?>
          </span>
          <strong style="font-size:18px;color:var(--green);
                          font-family:'Playfair Display',serif;">
            <?= $row['total'] ?>
          </strong>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Services by Site Coverage -->
      <div class="dashboard-card" style="grid-column:1/-1;">
        <h3 class="dashboard-card-title">Services — Site Coverage</h3>
        <div class="referrals-table-wrap">
          <table class="referrals-table">
            <thead>
              <tr>
                <th>Service</th>
                <th>Sites Offering This Service</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($sitesByService as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['service_name']) ?></td>
                <td>
                  <strong style="color:var(--green);font-size:16px;">
                    <?= $row['site_count'] ?>
                  </strong> sites
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
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