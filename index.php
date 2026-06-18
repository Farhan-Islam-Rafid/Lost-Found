<?php
session_start();
include 'db.php';

$search = "";
$where  = "";

// SEARCH LOGIC
if (isset($_GET['search']) && $_GET['search'] != "") {
  $search = mysqli_real_escape_string($conn, $_GET['search']);
  $where  = "WHERE name LIKE '%$search%' OR description LIKE '%$search%' OR location LIKE '%$search%'";
}

// FILTER (Lost / Found / All)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';

// QUERIES
$result = mysqli_query($conn, "SELECT * FROM products $where ORDER BY Id DESC");
$lost   = mysqli_query($conn, "SELECT * FROM products WHERE type='Lost'"  . ($where ? " AND (name LIKE '%$search%' OR description LIKE '%$search%' OR location LIKE '%$search%')" : "") . " ORDER BY Id DESC");
$found  = mysqli_query($conn, "SELECT * FROM products WHERE type='Found'" . ($where ? " AND (name LIKE '%$search%' OR description LIKE '%$search%' OR location LIKE '%$search%')" : "") . " ORDER BY Id DESC");

// COUNTS
$total_count = mysqli_num_rows($result);
$lost_count  = mysqli_num_rows($lost);
$found_count = mysqli_num_rows($found);




?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lost & Found System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    html {
      scroll-behavior: smooth;
    }

    body {
      background: #f0f4ff;
      font-family: 'Segoe UI', sans-serif;
    }

    /* ── NAVBAR ── */
    .navbar {
      backdrop-filter: blur(10px);
      background: rgba(18, 18, 30, 0.97) !important;
    }

    /* ── HERO ── */
    .hero {
      min-height: 75vh;
      background: linear-gradient(135deg, #0d6efd, #0944CE);
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
    }

    .hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 30% 50%, rgba(255, 255, 255, 0.07) 0%, transparent 60%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
    }

    /* particles */
    .particle {
      position: absolute;
      border-radius: 50%;
      background: #fff;
      pointer-events: none;
      animation: neonFloat linear infinite;
      opacity: 0.7;
    }

    @keyframes neonFloat {
      0% {
        transform: translateY(0) scale(1);
        opacity: .7;
      }

      50% {
        transform: translateY(-20px) scale(1.2);
        opacity: 1;
      }

      100% {
        transform: translateY(-55px) scale(.8);
        opacity: 0;
      }
    }

    /* search bar */
    .search-wrap {
      position: relative;
      max-width: 520px;
      margin: 0 auto;
    }

    .search-wrap input {
      border-radius: 50px;
      border: none;
      padding: 14px 55px 14px 22px;
      font-size: 1rem;
      width: 100%;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      transition: box-shadow 0.3s;
    }

    .search-wrap input:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5);
    }

    .search-wrap button {
      position: absolute;
      right: 6px;
      top: 50%;
      transform: translateY(-50%);
      border-radius: 50px;
      border: none;
      padding: 8px 18px;
      background: #0d6efd;
      color: #fff;
      font-weight: 600;
      transition: background 0.2s;
    }

    .search-wrap button:hover {
      background: #0944CE;
    }

    /* ── STAT CARDS ── */
    .stat-card {
      background: #fff;
      border-radius: 14px;
      padding: 22px 16px;
      text-align: center;
      box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
      transition: transform 0.2s;
    }

    .stat-card:hover {
      transform: translateY(-4px);
    }

    .stat-card .number {
      font-size: 2rem;
      font-weight: 700;
    }

    /* ── FILTER TABS ── */
    .filter-tabs {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .f-btn {
      border-radius: 50px;
      padding: 7px 20px;
      font-size: 0.88rem;
      font-weight: 500;
      border: 2px solid #dee2e6;
      background: #fff;
      color: #555;
      cursor: pointer;
      transition: 0.2s;
    }

    .f-btn.active,
    .f-btn:hover {
      background: #0d6efd;
      color: #fff;
      border-color: #0d6efd;
    }

    .f-btn.lost.active,
    .f-btn.lost:hover {
      background: #dc3545;
      border-color: #dc3545;
    }

    .f-btn.found.active,
    .f-btn.found:hover {
      background: #198754;
      border-color: #198754;
    }

    /* ── ITEM CARDS ── */
    .item-card {
      border: none;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
      transition: transform 0.22s, box-shadow 0.22s;
    }

    .item-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
    }

    .item-card img {
      height: 200px;
      width: 100%;
      object-fit: cover;
    }

    /* desc clamp */
    .clamp {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* ── NOTICE BAR ── */
    .notice-bar {
      background: linear-gradient(90deg, #0944CE, #0d6efd);
      color: #fff;
      font-weight: 600;
      padding: 8px 0;
      font-size: 0.9rem;
    }

    /* ── SECTION HEADING ── */
    .section-heading {
      font-weight: 700;
      font-size: 1.4rem;
      position: relative;
      padding-bottom: 8px;
      margin-bottom: 24px;
    }

    .section-heading::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      border-radius: 10px;
      background: currentColor;
      opacity: 0.5;
    }

    /* ── FOOTER ── */
    footer {
      background: #12121e;
      color: #cdd2dc;
    }

    footer a {
      color: #cdd2dc;
      text-decoration: none;
      transition: 0.2s;
    }

    footer a:hover {
      color: #0d6efd;
      padding-left: 5px;
    }

    /* BACK TO TOP */
    #backTop {
      position: fixed;
      bottom: 28px;
      right: 28px;
      background: #0d6efd;
      color: #fff;
      border: none;
      border-radius: 50%;
      width: 44px;
      height: 44px;
      font-size: 1.2rem;
      display: none;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
      cursor: pointer;
      z-index: 999;
      transition: background 0.2s;
    }

    #backTop:hover {
      background: #0944CE;
    }

    #backTop.show {
      display: flex;
    }
  </style>
</head>

<body>

  <!-- ════════════ NAVBAR ════════════ -->
  <nav class="navbar navbar-expand-lg navbar-dark   sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold fs-4" href="index.php">Lost&amp;Found</a>

      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto align-items-center gap-2">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#lost-section">Lost Items</a></li>
          <li class="nav-item"><a class="nav-link" href="#found-section">Found Items</a></li>

          <?php if (isset($_SESSION['user_name'])): ?>
            <li class="nav-item dropdown ms-2">
              <a class="nav-link dropdown-toggle text-white px-3 py-2 rounded-pill"
                href="#" role="button" data-bs-toggle="dropdown"
                style="background:linear-gradient(135deg,#0d6efd,#0944ce);">
                👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:12px; border:none;">
                <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item" href="add_product.php"><i class="bi bi-plus-circle me-2"></i>Add Item</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>


              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item"><a href="login.php" class="btn btn-primary   btn-sm rounded-pill px-3">Login</a></li>
            <li class="nav-item"><a href="register.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Register</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php if (!empty($_SESSION['role']) && strtolower($_SESSION['role']) === "admin"): ?>
  <a href="admin.php" class="btn btn-danger ms-2">
    Admin Panel
  </a>
<?php endif; ?>
  </nav>

  <!-- ════════════ HERO ════════════ -->
  <section class="hero text-white text-center">

    <!-- particles -->
    <span class="particle" style="width:8px;height:8px; top:15%;left:20%; box-shadow:0 0 15px #00e5ff; animation-duration:6s;"></span>
    <span class="particle" style="width:6px;height:6px; top:60%;left:70%; box-shadow:0 0 12px #66ffcc; animation-duration:7s;"></span>
    <span class="particle" style="width:10px;height:10px;top:75%;left:30%; box-shadow:0 0 18px #ff4dff; animation-duration:8s;"></span>
    <span class="particle" style="width:7px;height:7px; top:35%;left:85%; box-shadow:0 0 14px #ffd700; animation-duration:9s;"></span>

    <div class="container position-relative" style="z-index:2;">
      <h1 class="fw-bold display-4 mb-3">Lost Something? Found Something?</h1>
      <p class="lead mb-4" style="color:#e9f2ff;">Connect with people and recover your lost items quickly, safely and easily.</p>

      <!-- SEARCH -->
      <form method="GET" action="index.php" class="search-wrap mb-4">
        <input type="text" name="search" placeholder=" Search lost or found items..."
          value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
      </form>

      <!-- SEARCH RESULT BADGE -->
      <?php if ($search): ?>
        <p class="mb-3">
          <span class="badge bg-light text-dark px-3 py-2 fs-6">
            <?php echo $total_count; ?> result(s) for "<?php echo htmlspecialchars($search); ?>"
            <a href="index.php" class="ms-2 text-danger">✕ Clear</a>
          </span>
        </p>
      <?php endif; ?>

      <!-- REPORT BUTTON -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="add_product.php" class="btn btn-light px-4 py-2 rounded-pill fw-semibold text-danger">
          Report Lost or Found
        </a>
      <?php else: ?>
        <button onclick="checkLogin()" class="btn btn-light px-4 py-2 rounded-pill fw-semibold text-danger">
          Report Lost or Found
        </button>
      <?php endif; ?>
    </div>
  </section>

  <!-- NOTICE BAR -->
  <div class="notice-bar text-center">
    <marquee>⚠ Notice: Please verify ownership before collecting found items. Stay safe and responsible.</marquee>
  </div>


  <!-- categories -->
  <div class="container my-5">
    <div class="row text-center">
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h5 class="text-danger">Lost Items</h5>
          <p>Report items you have lost</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h5 class="text-success">Found Items</h5>
          <p>Post items you have found</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h5 class="text-primary">Resolved</h5>
          <p>Items successfully returned</p>
        </div>
      </div>
    </div>
  </div>



  <!-- ════════════ ALL POSTS (with JS filter) ════════════ -->
  <div class="container my-5">

    <!-- heading + filter -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
      <h3 class="section-heading text-primary mb-0">Latest Posts</h3>
      <div class="filter-tabs">
        <button class="f-btn active" onclick="jsFilter('All',  this)">All</button>
        <button class="f-btn lost" onclick="jsFilter('Lost', this)">Lost</button>
        <button class="f-btn found" onclick="jsFilter('Found',this)">Found</button>
      </div>
    </div>

    <div class="row" id="allGrid">
      <?php
      // Reset pointer
      mysqli_data_seek($result, 0);
      while ($row = mysqli_fetch_assoc($result)):
      ?>
        <div class="col-md-4 mb-4 grid-item" data-type="<?php echo $row['type']; ?>">
          <div class="card item-card h-100">
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="item">
            <div class="card-body">
              <?php if ($row['type'] == "Lost"): ?>
                <span class="badge bg-danger px-3 py-1 mb-2">Lost</span>
              <?php else: ?>
                <span class="badge bg-success px-3 py-1 mb-2">Found</span>
              <?php endif; ?>
              <h5 class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></h5>
              <p class="text-muted small clamp"><?php echo htmlspecialchars($row['description']); ?></p>
              <a href="cardpage.php?id=<?php echo $row['Id']; ?>"
                class="btn btn-primary btn-sm rounded-pill px-3 mt-auto">See More →</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>

      <?php if ($total_count == 0): ?>
        <div class="text-center text-muted col-12 py-5">
          <h5>No items found <?php echo $search ? 'for "' . htmlspecialchars($search) . '"' : ''; ?></h5>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ════════════ LOST SECTION ════════════ -->
  <div class="bg-white py-5">
    <div class="container">
      <h3 id="lost-section" class="section-heading text-danger"> Lost Items</h3>
      <div class="row">
        <?php if ($lost_count > 0): ?>
          <?php mysqli_data_seek($lost, 0);
          while ($row = mysqli_fetch_assoc($lost)): ?>
            <div class="col-md-4 mb-4">
              <div class="card item-card h-100">
                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="item">
                <div class="card-body">
                  <span class="badge bg-danger px-3 py-1 mb-2">Lost</span>
                  <h5 class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></h5>
                  <p class="text-muted small clamp"><?php echo htmlspecialchars($row['description']); ?></p>
                  <a href="cardpage.php?id=<?php echo $row['Id']; ?>"
                    class="btn btn-primary btn-sm rounded-pill px-3">See More →</a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-muted">No lost items available.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ════════════ FOUND SECTION ════════════ -->
  <div class="container py-5">
    <h3 id="found-section" class="section-heading text-success"> Found Items</h3>
    <div class="row">
      <?php if ($found_count > 0): ?>
        <?php mysqli_data_seek($found, 0);
        while ($row = mysqli_fetch_assoc($found)): ?>
          <div class="col-md-4 mb-4">
            <div class="card item-card h-100">
              <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="item">
              <div class="card-body">
                <span class="badge bg-success px-3 py-1 mb-2">Found</span>
                <h5 class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></h5>
                <p class="text-muted small clamp"><?php echo htmlspecialchars($row['description']); ?></p>
                <a href="cardpage.php?id=<?php echo $row['Id']; ?>"
                  class="btn btn-primary btn-sm rounded-pill px-3">See More →</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted">No found items available.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ════════════ FOOTER ════════════ -->
  <footer class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4">
          <h5 class="fw-bold text-white mb-3">Lost&amp;Found</h5>
          <p style="color:#8892a4; line-height:1.7;">A smart platform to help people report lost items and return found items quickly and safely.</p>
        </div>
        <div class="col-md-4 mb-4">
          <h6 class="fw-bold text-white mb-3">Quick Links</h6>
          <a href="index.php" class="d-block mb-2">Home</a>
          <a href="#lost-section" class="d-block mb-2">Lost Items</a>
          <a href="#found-section" class="d-block mb-2">Found Items</a>
          <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login.php" class="d-block mb-2">Login</a>
            <a href="register.php" class="d-block mb-2">Register</a>
          <?php else: ?>
            <a href="dashboard.php" class="d-block mb-2">Dashboard</a>
            <a href="logout.php" class="d-block mb-2 text-danger">Logout</a>
          <?php endif; ?>
        </div>
        <div class="col-md-4 mb-4">
          <h6 class="fw-bold text-white mb-3">Contact</h6>
          <p class="mb-2">📍 Narayanganj, Bangladesh</p>
          <p class="mb-2">📧 support@lostfound.com</p>
          <p class="mb-0">📞 +880 15334-77264</p>
        </div>
      </div>
      <hr style="border-color:#2a2a3e;">


      <!-- New Low-Opacity Cool Big Text Section -->
      <div class="text-center unselectable" style="
      font-family: 'Montserrat', 'Arial Black', sans-serif; 
      font-size: clamp(3rem, 8vw, 6rem); 
      font-weight: 900; 
      letter-spacing: 6px; 
      text-transform: uppercase; 
      background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      user-select: none;
      line-height: 1;
      margin-bottom: -10px;
    ">
        LOST & FOUND
      </div>

      <p class="text-center mb-0" style="color:#555d6b;">
        © 2026 Lost &amp; Found System | Developed by Farhan Islam Rafid
      </p>
    </div>
  </footer>

  <!-- BACK TO TOP -->
  <button id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="bi bi-arrow-up"></i>
  </button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Login alert
    function checkLogin() {
      Swal.fire({
        icon: 'warning',
        title: 'Login Required',
        text: '⚠️ Please login first to post an item!',
        confirmButtonText: 'Login Now',
        confirmButtonColor: '#0d6efd'
      }).then(r => {
        if (r.isConfirmed) window.location.href = "login.php";
      });
    }

    // JS filter for Latest Posts
    function jsFilter(type, btn) {
      document.querySelectorAll('.f-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('.grid-item').forEach(el => {
        el.style.display = (type === 'All' || el.dataset.type === type) ? '' : 'none';
      });
    }

    // Back to top visibility
    window.addEventListener('scroll', () => {
      document.getElementById('backTop').classList.toggle('show', window.scrollY > 300);
    });
  </script>
</body>

</html>