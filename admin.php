<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user_id'];

$q = mysqli_query($conn, "SELECT * FROM users WHERE id='$user'");
$adminData = mysqli_fetch_assoc($q);

if (!$adminData || $adminData['role'] != "admin") {
    die("Access Denied");
}

$posts      = mysqli_query($conn, "SELECT * FROM products ORDER BY Id DESC");
$total_posts = mysqli_num_rows($posts);

$users_q    = mysqli_query($conn, "SELECT * FROM users WHERE id != '$user' ORDER BY id DESC");
$total_users = mysqli_num_rows($users_q);

$lost_q     = mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE type='Lost'");
$lost_count  = mysqli_fetch_assoc($lost_q)['c'];

$found_q    = mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE type='Found'");
$found_count = mysqli_fetch_assoc($found_q)['c'];

$banned_q   = mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE status='Banned' AND id != '$user'");
$banned_count = mysqli_fetch_assoc($banned_q)['c'];

$posts2     = mysqli_query($conn, "SELECT * FROM products ORDER BY Id DESC");
$users_q2   = mysqli_query($conn, "SELECT * FROM users WHERE id != '$user' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel — Lost & Found</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  html { scroll-behavior: smooth; }

  body {
    background: #f0f4ff;
    font-family: 'Segoe UI', sans-serif;
    font-size: 14px;
  }

  /* ── NAVBAR (same as index.php) ── */
  .navbar {
    backdrop-filter: blur(10px);
    background: rgba(18, 18, 30, 0.97) !important;
  }

  /* ── HERO BANNER ── */
  .admin-hero {
    background: linear-gradient(135deg, #0d6efd, #0944CE);
    padding: 32px 0 28px;
    position: relative;
    overflow: hidden;
  }
  .admin-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.07) 0%, transparent 60%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%);
  }
  .admin-hero .container { position: relative; z-index: 2; }
  .admin-hero h2 { font-weight: 700; font-size: 1.6rem; margin-bottom: 4px; }
  .admin-hero p  { opacity: .85; font-size: .9rem; margin: 0; }

  /* ── STAT CARDS ── */
  .stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px 18px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s;
    border-top: 4px solid var(--accent, #0d6efd);
  }
  .stat-card:hover { transform: translateY(-4px); }
  .stat-card .number { font-size: 2rem; font-weight: 700; }
  .stat-card .label  { font-size: .8rem; color: #6c757d; font-weight: 500; margin-bottom: 4px; }
  .stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
  }

  /* ── SECTION HEADING (same as index.php) ── */
  .section-heading {
    font-weight: 700;
    font-size: 1.2rem;
    position: relative;
    padding-bottom: 8px;
    margin-bottom: 0;
  }
  .section-heading::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0;
    width: 40px; height: 3px;
    border-radius: 10px;
    background: currentColor;
    opacity: 0.45;
  }

  /* ── FILTER TABS (same as index.php) ── */
  .filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
  .f-btn {
    border-radius: 50px;
    padding: 6px 18px;
    font-size: .82rem;
    font-weight: 500;
    border: 2px solid #dee2e6;
    background: #fff;
    color: #555;
    cursor: pointer;
    transition: .2s;
  }
  .f-btn.active, .f-btn:hover { background: #0d6efd; color: #fff; border-color: #0d6efd; }
  .f-btn.lost.active, .f-btn.lost:hover { background: #dc3545; border-color: #dc3545; }
  .f-btn.found.active, .f-btn.found:hover { background: #198754; border-color: #198754; }
  .f-btn.banned.active, .f-btn.banned:hover { background: #dc3545; border-color: #dc3545; }

  /* ── CARD PANEL ── */
  .panel-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    overflow: hidden;
  }
  .panel-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
  }

  /* ── TABLE ── */
  table { width: 100%; border-collapse: collapse; }
  th {
    background: linear-gradient(135deg, #0d6efd, #0944CE);
    color: #fff;
    padding: 11px 16px;
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    text-align: left;
  }
  td { padding: 13px 16px; border-bottom: 1px solid #f1f3f9; vertical-align: middle; }
  tr:last-child td { border-bottom: none; }
  tbody tr:hover { background: #f8f9ff; }

  /* ── BADGES ── */
  .badge-lost    { background: #fde8ea; color: #dc3545; }
  .badge-found   { background: #e6f4ed; color: #198754; }
  .badge-active  { background: #e6f4ed; color: #198754; }
  .badge-banned  { background: #fde8ea; color: #dc3545; }
  .badge-admin   { background: #e8edff; color: #0d6efd; }
  .badge-user    { background: #f1f3f9; color: #6c757d; }
  .tag {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 50px;
    font-size: .75rem; font-weight: 600;
  }

  /* ── ACTION BUTTONS ── */
  .btn-act {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: .75rem; font-weight: 600;
    border: 1.5px solid transparent;
    cursor: pointer; background: transparent;
    transition: .15s; text-decoration: none;
    font-family: 'Segoe UI', sans-serif;
  }
  .btn-act-danger  { color: #dc3545; border-color: #f5c2c7; }
  .btn-act-danger:hover  { background: #fde8ea; color: #dc3545; }
  .btn-act-warn    { color: #f59e0b; border-color: #fde68a; }
  .btn-act-warn:hover    { background: #fffbeb; color: #f59e0b; }
  .btn-act-success { color: #198754; border-color: #b7dfca; }
  .btn-act-success:hover { background: #e6f4ed; color: #198754; }
  .btn-act-primary { color: #0d6efd; border-color: #bfcffe; }
  .btn-act-primary:hover { background: #e8edff; color: #0d6efd; }

  /* ── USER AVATAR ── */
  .u-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd, #0944CE);
    color: #fff; font-size: 12px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }

  /* ── SEARCH INPUT ── */
  .search-box {
    position: relative;
  }
  .search-box i {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: #adb5bd; font-size: 14px;
  }
  .search-box input {
    border: 1.5px solid #dee2e6;
    border-radius: 50px;
    padding: 7px 14px 7px 34px;
    font-size: 13px;
    outline: none;
    width: 210px;
    transition: border .2s;
  }
  .search-box input:focus { border-color: #0d6efd; }

  /* ── WARN MODAL ── */
  .modal-backdrop-custom {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 1050;
    align-items: center;
    justify-content: center;
  }
  .modal-backdrop-custom.show { display: flex; }
  .warn-modal {
    background: #fff;
    border-radius: 16px;
    padding: 28px;
    width: 460px;
    max-width: 95vw;
    box-shadow: 0 16px 48px rgba(0,0,0,0.18);
    animation: popIn .2s ease;
  }
  @keyframes popIn {
    from { opacity:0; transform: translateY(16px); }
    to   { opacity:1; transform: translateY(0); }
  }
  .warn-modal textarea {
    width: 100%;
    border: 1.5px solid #dee2e6;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    resize: vertical;
    outline: none;
    font-family: 'Segoe UI', sans-serif;
    line-height: 1.6;
    transition: border .2s;
  }
  .warn-modal textarea:focus { border-color: #0d6efd; }
  .warn-type-btn {
    border: 1.5px solid #dee2e6;
    border-radius: 50px;
    padding: 4px 14px;
    font-size: .78rem;
    font-weight: 500;
    background: #fff;
    color: #555;
    cursor: pointer;
    transition: .15s;
  }
  .warn-type-btn.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }

  /* ── EMPTY STATE ── */
  .empty-state { text-align: center; padding: 48px 0; color: #adb5bd; }
  .empty-state i { font-size: 2.2rem; display: block; margin-bottom: 10px; }

  /* ── FOOTER (same as index.php) ── */
  footer { background: #12121e; color: #cdd2dc; }
  footer a { color: #cdd2dc; text-decoration: none; transition: .2s; }
  footer a:hover { color: #0d6efd; padding-left: 5px; }

  /* BACK TO TOP */
  #backTop {
    position: fixed; bottom: 28px; right: 28px;
    background: #0d6efd; color: #fff;
    border: none; border-radius: 50%;
    width: 44px; height: 44px; font-size: 1.2rem;
    display: none; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.2);
    cursor: pointer; z-index: 999; transition: background .2s;
  }
  #backTop:hover { background: #0944CE; }
  #backTop.show  { display: flex; }

  .row-hidden { display: none; }
</style>
</head>
<body>

<!-- ════ NAVBAR (same style as index.php) ════ -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4" href="index.php">Lost&amp;Found</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="admin.php">Admin Panel</a></li>
        <li class="nav-item dropdown ms-2">
          <a class="nav-link dropdown-toggle text-white px-3 py-2 rounded-pill"
             href="#" role="button" data-bs-toggle="dropdown"
             style="background:linear-gradient(135deg,#0d6efd,#0944ce);">
            👤 <?php echo htmlspecialchars($adminData['name'] ?? 'Admin'); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:12px;border:none;">
            <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ════ HERO BANNER ════ -->
<div class="admin-hero text-white">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <h2><i class="bi bi-shield-check me-2"></i>Admin Dashboard</h2>
        <p>Manage posts, users and platform activity — <?php echo date('l, d M Y'); ?></p>
      </div>
      <a href="index.php" class="btn btn-light rounded-pill px-4 fw-semibold" style="color:#0d6efd;">
        <i class="bi bi-house me-1"></i> Back to Site
      </a>
    </div>
  </div>
</div>

<!-- ════ MAIN CONTENT ════ -->
<div class="container my-4">

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card" style="--accent:#0d6efd;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="label">Total Posts</div>
          <div class="stat-icon" style="background:#e8edff;color:#0d6efd;"><i class="bi bi-file-text"></i></div>
        </div>
        <div class="number text-primary"><?php echo $total_posts; ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card" style="--accent:#dc3545;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="label">Lost Items</div>
          <div class="stat-icon" style="background:#fde8ea;color:#dc3545;"><i class="bi bi-exclamation-triangle"></i></div>
        </div>
        <div class="number text-danger"><?php echo $lost_count; ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card" style="--accent:#198754;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="label">Found Items</div>
          <div class="stat-icon" style="background:#e6f4ed;color:#198754;"><i class="bi bi-check-circle"></i></div>
        </div>
        <div class="number text-success"><?php echo $found_count; ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card" style="--accent:#6c757d;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="label">Members</div>
          <div class="stat-icon" style="background:#f1f3f9;color:#6c757d;"><i class="bi bi-people"></i></div>
        </div>
        <div class="number text-secondary"><?php echo $total_users; ?></div>
        <div style="font-size:.75rem;color:#adb5bd;"><?php echo $banned_count; ?> banned</div>
      </div>
    </div>
  </div>

  <!-- POSTS PANEL -->
  <div class="panel-card mb-4">
    <div class="panel-card-header">
      <div class="d-flex align-items-center gap-3">
        <h5 class="section-heading text-primary mb-0">Manage Posts</h5>
        <span class="tag badge-user"><?php echo $total_posts; ?> total</span>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Filter tabs -->
        <div class="filter-tabs">
          <button class="f-btn active" onclick="filterPosts('All',this)">All</button>
          <button class="f-btn lost"   onclick="filterPosts('Lost',this)">Lost</button>
          <button class="f-btn found"  onclick="filterPosts('Found',this)">Found</button>
        </div>
        <!-- Search -->
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input type="text" id="postSearch" placeholder="Search posts..." oninput="searchTable('postSearch','postsTable','post-name')">
        </div>
      </div>
    </div>

    <?php if($total_posts > 0): ?>
    <div class="table-responsive">
      <table id="postsTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Type</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($posts2)): ?>
          <tr data-type="<?php echo $row['type']; ?>">
            <td style="color:#adb5bd;font-size:12px;">#<?php echo $row['Id']; ?></td>
            <td class="fw-semibold post-name"><?php echo htmlspecialchars($row['name']); ?></td>
            <td>
              <?php if($row['type'] == 'Lost'): ?>
                <span class="tag badge-lost"><i class="bi bi-exclamation-triangle"></i> Lost</span>
              <?php else: ?>
                <span class="tag badge-found"><i class="bi bi-check-circle"></i> Found</span>
              <?php endif; ?>
            </td>
            <td style="color:#6c757d;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              <?php echo htmlspecialchars(substr($row['description'], 0, 65)); ?>...
            </td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <a href="cardpage.php?id=<?php echo $row['Id']; ?>" class="btn-act btn-act-primary">
                  <i class="bi bi-eye"></i> View
                </a>
                <a href="delete_post.php?id=<?php echo $row['Id']; ?>" class="btn-act btn-act-danger" onclick="return confirmDelete(event)">
                  <i class="bi bi-trash"></i> Delete
                </a>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="empty-state"><i class="bi bi-inbox"></i><p class="text-muted">No posts found.</p></div>
    <?php endif; ?>
  </div>

  <!-- USERS PANEL -->
  <div class="panel-card mb-5">
    <div class="panel-card-header">
      <div class="d-flex align-items-center gap-3">
        <h5 class="section-heading text-primary mb-0">Manage Users</h5>
        <span class="tag badge-user"><?php echo $total_users; ?> members</span>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Filter tabs -->
        <div class="filter-tabs">
          <button class="f-btn active"   onclick="filterUsers('all',this)">All</button>
          <button class="f-btn found"    onclick="filterUsers('active',this)">Active</button>
          <button class="f-btn banned"   onclick="filterUsers('banned',this)">Banned</button>
        </div>
        <!-- Search -->
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input type="text" id="userSearch" placeholder="Search users..." oninput="searchTable('userSearch','usersTable','user-name')">
        </div>
      </div>
    </div>

    <?php if($total_users > 0): ?>
    <div class="table-responsive">
      <table id="usersTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($ur = mysqli_fetch_assoc($users_q2)):
          $isBanned = isset($ur['status']) && strtolower($ur['status']) == 'banned';
          $initials = strtoupper(substr($ur['name'], 0, 2));
        ?>
          <tr data-status="<?php echo $isBanned ? 'banned' : 'active'; ?>"
              data-role="<?php echo strtolower($ur['role']); ?>">
            <td style="color:#adb5bd;font-size:12px;">#<?php echo $ur['Id']; ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="u-avatar"><?php echo $initials; ?></div>
                <div>
                  <div class="fw-semibold user-name"><?php echo htmlspecialchars($ur['name']); ?></div>
                  <div style="font-size:12px;color:#adb5bd;"><?php echo htmlspecialchars($ur['email'] ?? '—'); ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="tag <?php echo $ur['role']=='admin'?'badge-admin':'badge-user'; ?>">
                <?php echo htmlspecialchars($ur['role']); ?>
              </span>
            </td>
            <td>
              <?php if($isBanned): ?>
                <span class="tag badge-banned"><i class="bi bi-shield-slash"></i> Banned</span>
              <?php else: ?>
                <span class="tag badge-active"><i class="bi bi-shield-check"></i> Active</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <!-- Warn -->
                <button class="btn-act btn-act-warn"
                  onclick="openWarnModal(<?php echo $ur['Id']; ?>,'<?php echo htmlspecialchars($ur['name'],ENT_QUOTES); ?>','<?php echo htmlspecialchars($ur['email']??'',ENT_QUOTES); ?>')">
                  <i class="bi bi-envelope-exclamation"></i> Warn
                </button>
                <!-- Ban / Unban -->
                <?php if($isBanned): ?>
                  <a href="toggle_ban.php?id=<?php echo $ur['Id']; ?>&action=unban"
                     class="btn-act btn-act-success" onclick="return confirmBan(event,'unban')">
                    <i class="bi bi-unlock"></i> Unban
                  </a>
                <?php else: ?>
                  <a href="toggle_ban.php?id=<?php echo $ur['Id']; ?>&action=ban"
                     class="btn-act btn-act-danger" onclick="return confirmBan(event,'ban')">
                    <i class="bi bi-slash-circle"></i> Ban
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="empty-state"><i class="bi bi-person-x"></i><p class="text-muted">No users found.</p></div>
    <?php endif; ?>
  </div>

</div><!-- /container -->

<!-- ════ WARN MODAL ════ -->
<div class="modal-backdrop-custom" id="warnBackdrop">
  <div class="warn-modal">
    <div class="d-flex align-items-center gap-3 mb-3">
      <div style="width:42px;height:42px;border-radius:10px;background:#fffbeb;color:#f59e0b;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
        <i class="bi bi-envelope-exclamation"></i>
      </div>
      <div>
        <div class="fw-bold" style="font-size:1rem;">Send Warning</div>
        <div id="warnSubtitle" style="font-size:12px;color:#6c757d;"></div>
      </div>
      <button onclick="closeWarnModal()" style="margin-left:auto;background:none;border:none;color:#adb5bd;font-size:1.3rem;cursor:pointer;line-height:1;">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- Warning Type -->
    <div class="mb-3">
      <div style="font-size:.75rem;color:#6c757d;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Warning Type</div>
      <div class="d-flex gap-2 flex-wrap" id="warnTypeGroup">
        <button class="warn-type-btn active" onclick="pickType(this,'Policy Violation')">Policy Violation</button>
        <button class="warn-type-btn" onclick="pickType(this,'Spam')">Spam</button>
        <button class="warn-type-btn" onclick="pickType(this,'Fake Listing')">Fake Listing</button>
        <button class="warn-type-btn" onclick="pickType(this,'Harassment')">Harassment</button>
        <button class="warn-type-btn" onclick="pickType(this,'Other')">Other</button>
      </div>
    </div>

    <!-- Severity -->
    <div class="mb-3">
      <div style="font-size:.75rem;color:#6c757d;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Severity</div>
      <div class="d-flex gap-2" id="severityGroup">
        <button class="warn-type-btn active" onclick="pickSeverity(this,'Low')">🟢 Low</button>
        <button class="warn-type-btn" onclick="pickSeverity(this,'Medium')">🟡 Medium</button>
        <button class="warn-type-btn" onclick="pickSeverity(this,'High')">🔴 High</button>
      </div>
    </div>

    <!-- Message -->
    <div class="mb-3">
      <div style="font-size:.75rem;color:#6c757d;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Message</div>
      <textarea id="warnMessage" rows="4" placeholder="Describe the policy violation clearly. This message will be reviewed by the user..."></textarea>
      <div style="font-size:11px;color:#adb5bd;text-align:right;margin-top:4px;" id="warnCount">0 / 500</div>
    </div>

    <!-- Notify via email -->
    <div class="d-flex align-items-center gap-2 mb-3">
      <input type="checkbox" id="notifyEmail" checked style="accent-color:#0d6efd;">
      <label for="notifyEmail" style="font-size:13px;color:#555;cursor:pointer;">Also notify user via email</label>
    </div>

    <!-- Buttons -->
    <div class="d-flex gap-2 justify-content-end">
      <button onclick="closeWarnModal()" class="btn btn-outline-secondary rounded-pill px-4" style="font-size:13px;">Cancel</button>
      <button onclick="submitWarn()" class="btn btn-warning rounded-pill px-4 fw-semibold" style="font-size:13px;color:#fff;">
        <i class="bi bi-send me-1"></i> Send Warning
      </button>
    </div>
  </div>
</div>

<!-- ════ FOOTER (same as index.php) ════ -->
<footer class="py-5 mt-2">
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-4">
        <h5 class="fw-bold text-white mb-3">Lost&amp;Found</h5>
        <p style="color:#8892a4;line-height:1.7;">A smart platform to help people report lost items and return found items quickly and safely.</p>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="fw-bold text-white mb-3">Quick Links</h6>
        <a href="index.php" class="d-block mb-2">Home</a>
        <a href="admin.php" class="d-block mb-2">Admin Panel</a>
        <a href="dashboard.php" class="d-block mb-2">Dashboard</a>
        <a href="logout.php" class="d-block mb-2 text-danger">Logout</a>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="fw-bold text-white mb-3">Contact</h6>
        <p class="mb-2">📍 Narayanganj, Bangladesh</p>
        <p class="mb-2">📧 support@lostfound.com</p>
        <p class="mb-0">📞 +880 15334-77264</p>
      </div>
    </div>
    <hr style="border-color:#2a2a3e;">
    <div class="text-center" style="font-family:'Arial Black',sans-serif;font-size:clamp(2.5rem,7vw,5rem);font-weight:900;letter-spacing:6px;text-transform:uppercase;background:linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.01));-webkit-background-clip:text;-webkit-text-fill-color:transparent;user-select:none;line-height:1;margin-bottom:-8px;">LOST & FOUND</div>
    <p class="text-center mb-0" style="color:#555d6b;">© 2026 Lost &amp; Found System | Developed by Farhan Islam Rafid</p>
  </div>
</footer>

<!-- BACK TO TOP -->
<button id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
  <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── STATE ──
let warnUserId = null, warnType = 'Policy Violation', warnSeverity = 'Low';

// ── POST FILTER ──
function filterPosts(type, btn) {
  document.querySelectorAll('#postsTable tbody tr').forEach(r => {
    r.style.display = (type === 'All' || r.dataset.type === type) ? '' : 'none';
  });
  btn.closest('.filter-tabs').querySelectorAll('.f-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

// ── USER FILTER ──
function filterUsers(type, btn) {
  document.querySelectorAll('#usersTable tbody tr').forEach(r => {
    const show = type === 'all'
      || (type === 'active' && r.dataset.status === 'active')
      || (type === 'banned' && r.dataset.status === 'banned');
    r.style.display = show ? '' : 'none';
  });
  btn.closest('.filter-tabs').querySelectorAll('.f-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

// ── SEARCH ──
function searchTable(inputId, tableId, cellClass) {
  const val = document.getElementById(inputId).value.toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr').forEach(r => {
    const name = r.querySelector('.' + cellClass);
    r.style.display = (!val || (name && name.textContent.toLowerCase().includes(val))) ? '' : 'none';
  });
}

// ── DELETE ──
function confirmDelete(e) {
  e.preventDefault();
  const url = e.currentTarget.getAttribute('href');
  Swal.fire({
    title: 'Delete this post?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete!'
  }).then(r => { if (r.isConfirmed) window.location.href = url; });
  return false;
}

// ── BAN CONFIRM ──
function confirmBan(e, action) {
  e.preventDefault();
  const url = e.currentTarget.getAttribute('href');
  Swal.fire({
    title: action === 'ban' ? 'Ban this user?' : 'Unban this user?',
    text: action === 'ban' ? 'User will lose platform access immediately.' : 'User account will be restored.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: action === 'ban' ? '#dc3545' : '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, proceed!'
  }).then(r => { if (r.isConfirmed) window.location.href = url; });
  return false;
}

// ── WARN MODAL ──
function openWarnModal(id, name, email) {
  warnUserId = id;
  document.getElementById('warnSubtitle').textContent = name + (email ? ' · ' + email : '');
  document.getElementById('warnMessage').value = '';
  document.getElementById('warnCount').textContent = '0 / 500';
  document.getElementById('notifyEmail').checked = true;
  // Reset type/severity selections
  document.querySelectorAll('#warnTypeGroup .warn-type-btn').forEach((b,i) => b.classList.toggle('active', i===0));
  document.querySelectorAll('#severityGroup .warn-type-btn').forEach((b,i) => b.classList.toggle('active', i===0));
  warnType = 'Policy Violation'; warnSeverity = 'Low';
  document.getElementById('warnBackdrop').classList.add('show');
}
function closeWarnModal() {
  document.getElementById('warnBackdrop').classList.remove('show');
}
document.getElementById('warnBackdrop').addEventListener('click', function(e) {
  if (e.target === this) closeWarnModal();
});
document.getElementById('warnMessage').addEventListener('input', function() {
  if (this.value.length > 500) this.value = this.value.slice(0, 500);
  document.getElementById('warnCount').textContent = this.value.length + ' / 500';
});
function pickType(btn, type) {
  warnType = type;
  document.querySelectorAll('#warnTypeGroup .warn-type-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
function pickSeverity(btn, sev) {
  warnSeverity = sev;
  document.querySelectorAll('#severityGroup .warn-type-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
function submitWarn() {
  const msg = document.getElementById('warnMessage').value.trim();
  if (!msg) {
    document.getElementById('warnMessage').style.borderColor = '#dc3545';
    document.getElementById('warnMessage').placeholder = '⚠ Please enter a warning message!';
    return;
  }
  const notify = document.getElementById('notifyEmail').checked ? 1 : 0;
  const url = `send_warning.php?id=${warnUserId}&type=${encodeURIComponent(warnType)}&severity=${encodeURIComponent(warnSeverity)}&notify=${notify}&msg=${encodeURIComponent(msg)}`;
  closeWarnModal();
  window.location.href = url;
}

// ── BACK TO TOP ──
window.addEventListener('scroll', () => {
  document.getElementById('backTop').classList.toggle('show', window.scrollY > 300);
});
</script>
</body>
</html>