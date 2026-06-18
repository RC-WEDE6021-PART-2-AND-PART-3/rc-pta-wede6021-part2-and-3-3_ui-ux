<?php
// ============================================================
//  COSUP V2 — admin/dashboard.php
//  Admin Dashboard
//  Community Oriented Substance Use Programme
//  The 5 Watermelons | XISD5319 | IIE Rosebank College
// ============================================================

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Only admin/superadmin allowed
if (empty($_SESSION['cosup_user_id']) ||
    !in_array($_SESSION['cosup_role'] ?? '', ['admin','superadmin'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$adminName = $_SESSION['cosup_first_name'] ?? 'Admin';
$role      = $_SESSION['cosup_role'] ?? 'admin';

// Fetch all stats for admin overview
$conn = getDBConnection();

$totalUsers    = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'user'")->fetch_assoc()['c'];
$totalSites    = $conn->query("SELECT COUNT(*) as c FROM sites WHERE is_active = 1")->fetch_assoc()['c'];
$totalReferrals= $conn->query("SELECT COUNT(*) as c FROM referrals")->fetch_assoc()['c'];
$pendingRef    = $conn->query("SELECT COUNT(*) as c FROM referrals WHERE status = 'pending'")->fetch_assoc()['c'];
$totalNews     = $conn->query("SELECT COUNT(*) as c FROM news_items WHERE is_published = 1")->fetch_assoc()['c'];
$totalHopeline = $conn->query("SELECT COUNT(*) as c FROM hopeline_logs")->fetch_assoc()['c'];

// Recent referrals
$recentReferrals = $conn->query(
    "SELECT * FROM vw_pending_referrals LIMIT 8"
)->fetch_all(MYSQLI_ASSOC);

// Recent users
$recentUsers = $conn->query(
    "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

// Recent audit logs
$auditLogs = $conn->query(
    "SELECT a.*, u.first_name, u.last_name
     FROM audit_logs a
     LEFT JOIN users u ON a.admin_user_id = u.user_id
     ORDER BY a.performed_at DESC
     LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!-- ===== ADMIN HERO ===== -->
<section class="admin-hero">
  <div class="container">
    <div class="admin-hero-inner">
      <div class="admin-hero-text" data-aos="fade-up">
        <span class="section-tag">Admin Portal</span>
        <h1 class="page-hero-title">
          Welcome, <?= htmlspecialchars($adminName) ?>
        </h1>
        <p style="color:#a0c8b0;font-size:15px;">
          COSUP Administration Dashboard ·
          <span class="role-badge role-<?= $role ?>">
            <?= ucfirst($role) ?>
          </span>
        </p>
      </div>
      <div class="admin-hero-actions">
        <a href="<?= BASE_URL ?>/admin/manage-sites.php"
           class="btn-outline"
           style="color:white;border-color:rgba(255,255,255,0.3);">
          Manage Sites
        </a>
        <a href="<?= BASE_URL ?>/admin/manage-news.php"
           class="btn-primary">
          + Publish News
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ===== STATS OVERVIEW ===== -->
<section style="background:var(--stats-bg);padding:40px 0;">
  <div class="container">
    <div class="admin-stats-grid">
      <div class="admin-stat-card" data-aos="fade-up" data-delay="0">
        <div class="admin-stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="admin-stat-num"><?= $totalUsers ?></div>
        <div class="admin-stat-lbl">Registered Users</div>
      </div>
      <div class="admin-stat-card" data-aos="fade-up" data-delay="100">
        <div class="admin-stat-icon"><i class="fa-solid fa-location-dot"></i></div>
        <div class="admin-stat-num"><?= $totalSites ?></div>
        <div class="admin-stat-lbl">Active Sites</div>
      </div>
      <div class="admin-stat-card" data-aos="fade-up" data-delay="200">
        <div class="admin-stat-icon"><i class="fa-regular fa-clipboard"></i></div>
        <div class="admin-stat-num"><?= $totalReferrals ?></div>
        <div class="admin-stat-lbl">Total Referrals</div>
      </div>
      <div class="admin-stat-card pending" data-aos="fade-up" data-delay="300">
        <div class="admin-stat-icon"><i class="fa-regular fa-hourglass-half"></i></div>
        <div class="admin-stat-num"><?= $pendingRef ?></div>
        <div class="admin-stat-lbl">Pending Referrals</div>
      </div>
      <div class="admin-stat-card" data-aos="fade-up" data-delay="400">
        <div class="admin-stat-icon"><i class="fa-regular fa-newspaper"></i></div>
        <div class="admin-stat-num"><?= $totalNews ?></div>
        <div class="admin-stat-lbl">Published News</div>
      </div>
      <div class="admin-stat-card" data-aos="fade-up" data-delay="500">
        <div class="admin-stat-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
        <div class="admin-stat-num"><?= $totalHopeline ?></div>
        <div class="admin-stat-lbl">Hopeline Clicks</div>
      </div>
    </div>
  </div>
</section>

<!-- ===== ADMIN MAIN GRID ===== -->
<section class="section" style="background:var(--bg);padding-top:48px;">
  <div class="container">
    <div class="admin-main-grid">

      <!-- Recent Referrals -->
      <div class="dashboard-card admin-card-wide" data-aos="fade-up">
        <div style="display:flex;justify-content:space-between;
                    align-items:center;margin-bottom:20px;">
          <h3 class="dashboard-card-title" style="margin:0;">
            Recent Referrals
          </h3>
          <a href="<?= BASE_URL ?>/admin/view-referrals.php"
             class="contact-link">
            View All →
          </a>
        </div>

        <?php if (empty($recentReferrals)): ?>
        <div class="empty-state">
          <p>No referrals yet.</p>
        </div>
        <?php else: ?>
        <div class="referrals-table-wrap">
          <table class="referrals-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Area</th>
                <th>Service</th>
                <th>Source</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentReferrals as $ref): ?>
              <tr>
                <td><strong><?= htmlspecialchars($ref['full_name']) ?></strong></td>
                <td><?= htmlspecialchars($ref['contact_number'] ?? '—') ?></td>
                <td><?= htmlspecialchars($ref['preferred_area'] ?? '—') ?></td>
                <td><?= htmlspecialchars($ref['service_needed'] ?? '—') ?></td>
                <td>
                  <span class="status-badge"
                        style="background:rgba(255,255,255,0.05);
                               color:var(--muted);">
                    <?= htmlspecialchars($ref['source'] ?? 'website') ?>
                  </span>
                </td>
                <td><?= date('d M Y', strtotime($ref['submitted_at'])) ?></td>
                <td>
                  <span class="status-badge status-<?= $ref['status'] ?>">
                    <?= ucfirst($ref['status']) ?>
                  </span>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>/admin/view-referrals.php?id=<?= $ref['referral_id'] ?>"
                     class="contact-link" style="font-size:12px;">
                    View
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Quick Admin Actions -->
      <div class="dashboard-card" data-aos="fade-up" data-delay="100">
        <h3 class="dashboard-card-title">Quick Actions</h3>
        <div class="quick-actions">
          <a href="<?= BASE_URL ?>/admin/manage-sites.php"
             class="quick-action-btn">
            <span><i class="fa-solid fa-location-dot"></i></span>
            <div>
              <strong>Manage Sites</strong>
              <small>Update clinic hours & info</small>
            </div>
          </a>
          <a href="<?= BASE_URL ?>/admin/manage-news.php"
             class="quick-action-btn">
            <span><i class="fa-regular fa-newspaper"></i></span>
            <div>
              <strong>Publish News</strong>
              <small>Add announcements</small>
            </div>
          </a>
          <a href="<?= BASE_URL ?>/admin/view-referrals.php"
             class="quick-action-btn">
            <span><i class="fa-regular fa-clipboard"></i></span>
            <div>
              <strong>View Referrals</strong>
              <small><?= $pendingRef ?> pending</small>
            </div>
          </a>
          <a href="<?= BASE_URL ?>/admin/reports.php"
             class="quick-action-btn">
            <span><i class="fa-solid fa-chart-bar"></i></span>
            <div>
              <strong>Reports</strong>
              <small>Generate & export data</small>
            </div>
          </a>
        </div>
      </div>

      <!-- Recent Users -->
      <div class="dashboard-card" data-aos="fade-up" data-delay="200">
        <h3 class="dashboard-card-title">Recent Registrations</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <?php foreach ($recentUsers as $u): ?>
          <div style="display:flex;align-items:center;
                      gap:12px;padding:10px 0;
                      border-bottom:1px solid var(--border2);">
            <div class="dashboard-avatar"
                 style="width:36px;height:36px;font-size:14px;flex-shrink:0;">
              <?= strtoupper(substr($u['first_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:14px;font-weight:600;
                          color:var(--text);
                          white-space:nowrap;overflow:hidden;
                          text-overflow:ellipsis;">
                <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
              </div>
              <div style="font-size:11px;color:var(--muted);">
                <?= htmlspecialchars($u['email']) ?>
              </div>
            </div>
            <span class="role-badge role-<?= $u['role'] ?>">
              <?= ucfirst($u['role']) ?>
            </span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Audit Log -->
      <div class="dashboard-card admin-card-wide"
           data-aos="fade-up" data-delay="300">
        <h3 class="dashboard-card-title">Recent Admin Activity</h3>
        <?php if (empty($auditLogs)): ?>
        <div class="empty-state"><p>No activity logged yet.</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:0;">
          <?php foreach ($auditLogs as $log): ?>
          <div style="display:flex;align-items:flex-start;
                      gap:16px;padding:14px 0;
                      border-bottom:1px solid var(--border2);">
            <div style="width:8px;height:8px;border-radius:50%;
                        background:var(--green);flex-shrink:0;
                        margin-top:6px;"></div>
            <div style="flex:1;">
              <div style="font-size:14px;color:var(--text);
                          font-weight:500;">
                <?= htmlspecialchars($log['action']) ?>
              </div>
              <div style="font-size:11px;color:var(--muted);
                          margin-top:3px;">
                By <?= htmlspecialchars(
                    ($log['first_name'] ?? '') . ' ' .
                    ($log['last_name']  ?? '')
                ) ?> ·
                <?= date('d M Y H:i', strtotime($log['performed_at'])) ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>