<?php
// ============================================================
//  COSUP V2 — admin/view-referrals.php
//  Admin — View & Manage Referrals
//  The 5 Watermelons | XISD5319 | IIE Rosebank College
// ============================================================

$page_title = 'Manage Referrals';
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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['referral_id'], $_POST['new_status'])) {
    $refId     = intval($_POST['referral_id']);
    $newStatus = $conn->real_escape_string($_POST['new_status']);
    $conn->query(
        "UPDATE referrals SET status = '$newStatus',
         updated_at = NOW()
         WHERE referral_id = $refId"
    );
    // Log to audit
    $conn->query(
        "INSERT INTO audit_logs
            (admin_user_id, action, target_table, target_id)
         VALUES
            ($adminId,
             'Updated referral #$refId status to $newStatus',
             'referrals', $refId)"
    );
    $success = "Referral #$refId updated to: " . ucfirst($newStatus);
}

// Filter
$filterStatus = $conn->real_escape_string($_GET['status'] ?? '');
$whereClause  = $filterStatus
    ? "WHERE r.status = '$filterStatus'"
    : '';

$allReferrals = $conn->query(
    "SELECT r.*, u.email as registered_email
     FROM referrals r
     LEFT JOIN users u ON r.user_id = u.user_id
     $whereClause
     ORDER BY r.submitted_at DESC"
)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="page-hero-text">
      <span class="section-tag">Admin · Referrals</span>
      <h1 class="page-hero-title">Manage Referrals</h1>
      <p class="page-hero-desc">
        View, filter and update all self-referral submissions.
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

    <!-- Filter Bar -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px;">
      <?php
      $statuses = ['' => 'All', 'pending' => 'Pending',
                   'contacted' => 'Contacted',
                   'completed' => 'Completed',
                   'cancelled' => 'Cancelled'];
      foreach ($statuses as $val => $label):
        $active = ($filterStatus === $val)
                  ? 'background:var(--green);color:white;border-color:var(--green);'
                  : '';
      ?>
      <a href="?status=<?= $val ?>"
         style="padding:8px 20px;border-radius:50px;
                border:1px solid var(--border);
                font-size:13px;font-weight:600;
                color:var(--text);<?= $active ?>
                transition:all 0.2s;">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="dashboard-card">
      <?php if (empty($allReferrals)): ?>
      <div class="empty-state">
        <div style="font-size:40px;margin-bottom:12px;"><i class="fa-regular fa-clipboard"></i></div>
        <p>No referrals found.</p>
      </div>
      <?php else: ?>
      <div class="referrals-table-wrap">
        <table class="referrals-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Contact</th>
              <th>Area</th>
              <th>Service</th>
              <th>Source</th>
              <th>Date</th>
              <th>Status</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allReferrals as $ref): ?>
            <tr>
              <td style="color:var(--muted);font-size:12px;">
                #<?= $ref['referral_id'] ?>
              </td>
              <td><strong><?= htmlspecialchars($ref['full_name']) ?></strong></td>
              <td>
                <?php if ($ref['contact_number']): ?>
                <a href="tel:<?= preg_replace('/\s+/','',$ref['contact_number']) ?>"
                   style="color:var(--green);font-weight:600;">
                  <?= htmlspecialchars($ref['contact_number']) ?>
                </a>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><?= htmlspecialchars($ref['preferred_area'] ?? '—') ?></td>
              <td style="font-size:12px;">
                <?= htmlspecialchars($ref['service_needed'] ?? '—') ?>
              </td>
              <td>
                <span class="status-badge"
                      style="background:rgba(255,255,255,0.05);
                             color:var(--muted);">
                  <?= htmlspecialchars($ref['source']) ?>
                </span>
              </td>
              <td style="font-size:12px;white-space:nowrap;">
                <?= date('d M Y', strtotime($ref['submitted_at'])) ?>
              </td>
              <td>
                <span class="status-badge status-<?= $ref['status'] ?>">
                  <?= ucfirst($ref['status']) ?>
                </span>
              </td>
              <td>
                <form method="POST" style="display:flex;gap:6px;">
                  <input type="hidden" name="referral_id"
                         value="<?= $ref['referral_id'] ?>"/>
                  <select name="new_status"
                          style="padding:4px 8px;border-radius:6px;
                                 border:1px solid var(--border);
                                 background:var(--bg);
                                 color:var(--text);font-size:12px;">
                    <option value="pending"
                      <?= $ref['status']==='pending'?'selected':'' ?>>
                      Pending
                    </option>
                    <option value="contacted"
                      <?= $ref['status']==='contacted'?'selected':'' ?>>
                      Contacted
                    </option>
                    <option value="completed"
                      <?= $ref['status']==='completed'?'selected':'' ?>>
                      Completed
                    </option>
                    <option value="cancelled"
                      <?= $ref['status']==='cancelled'?'selected':'' ?>>
                      Cancelled
                    </option>
                  </select>
                  <button type="submit"
                          style="padding:4px 12px;border-radius:6px;
                                 border:none;background:var(--green);
                                 color:white;font-size:12px;
                                 font-weight:600;cursor:pointer;">
                    Save
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <div style="margin-top:20px;">
      <a href="<?= BASE_URL ?>/admin/dashboard.php"
         class="contact-link">← Back to Dashboard</a>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>