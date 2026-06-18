<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    die("No item selected");
}

$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT products.*, users.name as uploader_name 
    FROM products 
    LEFT JOIN users ON CAST(products.user_id AS UNSIGNED) = users.Id 
    WHERE products.Id='$id'");
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Item not found");
}

// Verify ownership
$verify_success = false;
$verify_wrong   = false;
if (isset($_POST['verify'])) {
    $user_answer = $_POST['user_answer'];
    if (strtolower(trim($user_answer)) == strtolower(trim($row['answer']))) {
        $verify_success = true;
        $_SESSION['verified_item_' . $id] = true;
    } else {
        $verify_wrong = true;
    }
}

$already_verified = isset($_SESSION['verified_item_' . $id]) && $_SESSION['verified_item_' . $id];

// Claim request
if (isset($_POST['claim'])) {
    $claimer_name    = mysqli_real_escape_string($conn, $_POST['claimer_name']);
    $claimer_contact = mysqli_real_escape_string($conn, $_POST['claimer_contact']);
    $claimer_note    = mysqli_real_escape_string($conn, $_POST['claimer_note']);
    $product_id      = $row['Id'];
    $sender          = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $claimer_name;

    $claim_msg = "CLAIM REQUEST — Name: $claimer_name | Contact: $claimer_contact | Note: $claimer_note";
    mysqli_query($conn, "INSERT INTO messages (product_id, sender_name, message) VALUES ('$product_id', '$sender', '$claim_msg')");

    $_SESSION['claim_success'] = true;
    unset($_SESSION['verified_item_' . $id]);
    header("Location: cardpage.php?id=$product_id");
    exit();
}

// Send message
if (isset($_POST['send_msg'])) {
    $message    = mysqli_real_escape_string($conn, $_POST['message']);
    $sender     = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Guest";
    $product_id = $row['Id'];
    mysqli_query($conn, "INSERT INTO messages (product_id, sender_name, message) VALUES ('$product_id', '$sender', '$message')");
    $_SESSION['msg_success'] = true;
    header("Location: cardpage.php?id=$product_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($row['name']); ?> | Lost&Found</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --primary: #0d6efd;
      --primary-dark: #0944CE;
      --radius: 14px;
    }

    body {
      background: #f0f4ff;
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }

    .navbar { background: rgba(18,18,30,0.97) !important; }

    .item-image-wrap {
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: 0 8px 30px rgba(0,0,0,0.12);
      background: #e9ecef;
    }
    .item-image-wrap img {
      width: 100%; max-height: 460px;
      object-fit: cover; display: block;
      transition: transform 0.4s ease;
    }
    .item-image-wrap:hover img { transform: scale(1.02); }

    .detail-card, .msg-box {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      padding: 28px;
      animation: fadeUp 0.35s ease;
    }
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(16px); }
      to   { opacity:1; transform:translateY(0); }
    }

    .section-label {
      font-size: 0.72rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 1.2px;
      color: #8898b0; margin-bottom: 12px;
      padding-bottom: 8px; border-bottom: 1px solid #eef0f5;
    }

    .info-row {
      display: flex; align-items: flex-start;
      gap: 12px; padding: 12px 0;
      border-bottom: 1px solid #f0f2f8;
    }
    .info-row:last-child { border-bottom: none; }
    .info-icon {
      width: 36px; height: 36px; border-radius: 9px;
      background: #f0f4ff;
      display: flex; align-items: center; justify-content: center;
      color: var(--primary); font-size: 1rem; flex-shrink: 0;
    }
    .info-label { font-size: 0.75rem; color: #8898b0; font-weight: 600; margin-bottom: 2px; }
    .info-value { font-size: 0.95rem; color: #2c3050; font-weight: 500; }

    .verify-box {
      background: #f8faff;
      border: 1.5px solid #e2e6f0;
      border-radius: var(--radius);
      padding: 22px;
    }

    .claim-box {
      background: linear-gradient(135deg, #f0fff8, #e6f9f0);
      border: 2px solid #a3e6c5;
      border-radius: var(--radius);
      padding: 24px;
      animation: fadeUp 0.4s ease;
    }
    .claim-box .claim-title {
      font-weight: 700; font-size: 1rem;
      color: #0d6e40; margin-bottom: 4px;
      display: flex; align-items: center; gap: 8px;
    }
    .claim-box .claim-sub {
      font-size: 0.83rem; color: #4a7c62; margin-bottom: 16px;
    }

    .form-control {
      border-radius: 10px;
      border: 1.5px solid #e2e6f0;
      padding: 10px 14px;
      font-size: 0.93rem;
      transition: border-color 0.2s, box-shadow 0.2s;
      background: #fafbff;
    }
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(13,110,253,0.10);
      background: #fff;
    }
    textarea.form-control { resize: none; }

    .btn-primary-custom {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #fff; border: none; border-radius: 50px;
      padding: 11px 22px; font-weight: 600; font-size: 0.92rem;
      display: inline-flex; align-items: center; gap: 7px;
      transition: opacity 0.2s, transform 0.15s; text-decoration: none;
      width: 100%; justify-content: center;
    }
    .btn-primary-custom:hover { opacity: 0.9; transform: scale(1.01); color: #fff; }

    .btn-success-custom {
      background: linear-gradient(135deg, #198754, #0f5c38);
      color: #fff; border: none; border-radius: 50px;
      padding: 12px 22px; font-weight: 600; font-size: 0.92rem;
      display: inline-flex; align-items: center; gap: 7px;
      transition: opacity 0.2s, transform 0.15s;
      width: 100%; justify-content: center;
    }
    .btn-success-custom:hover { opacity: 0.9; transform: scale(1.01); }

    .btn-outline-custom {
      border-radius: 50px; padding: 11px 24px;
      font-weight: 600; font-size: 0.92rem;
      display: inline-flex; align-items: center; gap: 7px;
      border: 1.5px solid #e2e6f0;
      color: #555; background: #fff; transition: 0.2s; text-decoration: none;
    }
    .btn-outline-custom:hover { background: #f0f4ff; border-color: #bbb; color: #333; }

    .claim-steps {
      display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap;
    }
    .claim-step {
      display: flex; align-items: center; gap: 6px;
      font-size: 0.78rem; font-weight: 600; color: #4a7c62;
    }
    .claim-step i { font-size: 1rem; color: #198754; }

    .alert { border-radius: 10px; font-weight: 500; }

    /* Posted By badge style */
    .uploader-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: linear-gradient(135deg, #e8edff, #f0f4ff);
      border: 1px solid #c7d4ff;
      border-radius: 50px;
      padding: 4px 14px;
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--primary);
    }

    footer { background: #12121e; color: #cdd2dc; }
    footer a { color: #cdd2dc; text-decoration: none; transition: 0.2s; }
    footer a:hover { color: var(--primary); padding-left: 5px; }

    @media (max-width: 576px) {
      .detail-card, .msg-box { padding: 18px; }
      .verify-box, .claim-box { padding: 16px; }
    }
    @media (min-width: 1200px) {
      .detail-card { padding: 38px; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4" href="index.php">Lost&amp;Found</a>
    <div class="ms-auto d-flex gap-2">
      <a href="index.php" class="btn btn-outline-light rounded-pill px-4 btn-sm">
        <i class="bi bi-house-fill me-1"></i>Home
      </a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php" class="btn btn-primary rounded-pill px-4 btn-sm">
          <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- MAIN -->
<div class="container py-5">
  <div class="row justify-content-center g-4">

    <!-- LEFT: Image + Details -->
    <div class="col-lg-7">

      <div class="item-image-wrap mb-4">
        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>"
             alt="<?php echo htmlspecialchars($row['name']); ?>">
      </div>

      <div class="detail-card">
        <div class="mb-3">
          <?php if ($row['type'] == "Lost"): ?>
            <span class="badge bg-danger px-3 py-2 rounded-pill mb-2">
              <i class="bi bi-exclamation-circle-fill me-1"></i>Lost Item
            </span>
          <?php else: ?>
            <span class="badge bg-success px-3 py-2 rounded-pill mb-2">
              <i class="bi bi-check-circle-fill me-1"></i>Found Item
            </span>
          <?php endif; ?>

          <h2 class="fw-bold text-dark mt-2 mb-2">
            <?php echo htmlspecialchars($row['name']); ?>
          </h2>

          <!-- Posted By badge under title -->
          <span class="uploader-badge">
            <i class="bi bi-person-circle"></i>
            Posted by: <?php echo htmlspecialchars($row['uploader_name'] ?? 'Unknown'); ?>
          </span>
        </div>

        <div style="height:1px;background:#eef0f5;margin:16px 0;"></div>

        <div class="section-label">Item Details</div>

        <div class="info-row">
          <div class="info-icon"><i class="bi bi-card-text"></i></div>
          <div>
            <div class="info-label">Description</div>
            <div class="info-value" style="line-height:1.7;">
              <?php echo nl2br(htmlspecialchars($row['description'])); ?>
            </div>
          </div>
        </div>

        <div class="info-row">
          <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
          <div>
            <div class="info-label">Location</div>
            <div class="info-value"><?php echo htmlspecialchars($row['location'] ?? '—'); ?></div>
          </div>
        </div>

        <div class="info-row">
          <div class="info-icon"><i class="bi bi-telephone-fill"></i></div>
          <div>
            <div class="info-label">Contact</div>
            <div class="info-value"><?php echo htmlspecialchars($row['contact'] ?? '—'); ?></div>
          </div>
        </div>

        <div class="info-row">
          <div class="info-icon"><i class="bi bi-person-fill"></i></div>
          <div>
            <div class="info-label">Posted By</div>
            <div class="info-value">
              <?php echo htmlspecialchars($row['uploader_name'] ?? 'Unknown'); ?>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <a href="index.php" class="btn-outline-custom">
            <i class="bi bi-arrow-left-circle"></i> Back to Home
          </a>
        </div>
      </div>
    </div>

    <!-- RIGHT: Verify / Claim / Message -->
    <div class="col-lg-5">

      <?php if ($row['type'] == "Found"): ?>

        <?php if (!$already_verified && !$verify_success): ?>
        <div class="verify-box mb-4">
          <div class="section-label">
            <i class="bi bi-shield-lock-fill me-1" style="color:var(--primary);"></i>
            Step 1 — Verify Ownership
          </div>

          <?php if ($verify_wrong): ?>
            <div class="alert alert-danger py-2">
              <i class="bi bi-x-circle-fill me-2"></i>Wrong answer. Please try again.
            </div>
          <?php endif; ?>

          <p class="fw-semibold mb-1" style="font-size:0.9rem; color:#3a3f5c;">
            <i class="bi bi-question-circle me-1 text-primary"></i>
            <?php echo htmlspecialchars($row['question']); ?>
          </p>
          <p class="text-muted small mb-3">Answer this question to prove you are the owner.</p>

          <form method="POST">
            <div class="mb-3">
              <input type="text" name="user_answer" class="form-control"
                     placeholder="Type your answer here" required>
            </div>
            <button type="submit" name="verify" class="btn-primary-custom">
              <i class="bi bi-patch-check-fill"></i> Verify Ownership
            </button>
          </form>
        </div>
        <?php endif; ?>

        <?php if ($verify_success || $already_verified): ?>
        <div class="claim-box mb-4">
          <div class="claim-title">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            Identity Verified Successfully
          </div>
          <div class="claim-sub">You answered correctly. Fill in your details to claim this item.</div>

          <div class="claim-steps">
            <div class="claim-step">
              <i class="bi bi-check2-circle"></i> Verified
            </div>
            <div class="claim-step" style="color:#aaa;">
              <i class="bi bi-chevron-right" style="color:#aaa; font-size:0.7rem;"></i>
            </div>
            <div class="claim-step">
              <i class="bi bi-person-fill-check"></i> Claim Details
            </div>
            <div class="claim-step" style="color:#aaa;">
              <i class="bi bi-chevron-right" style="color:#aaa; font-size:0.7rem;"></i>
            </div>
            <div class="claim-step" style="color:#aaa;">
              <i class="bi bi-box-seam" style="color:#aaa;"></i> Collect Item
            </div>
          </div>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label" style="font-size:0.83rem; font-weight:600; color:#2c3050;">
                <i class="bi bi-person-fill me-1" style="color:#198754;"></i>Your Full Name
              </label>
              <input type="text" name="claimer_name" class="form-control"
                     placeholder="Enter your full name"
                     value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>"
                     required>
            </div>
            <div class="mb-3">
              <label class="form-label" style="font-size:0.83rem; font-weight:600; color:#2c3050;">
                <i class="bi bi-telephone-fill me-1" style="color:#198754;"></i>Your Contact Number
              </label>
              <input type="text" name="claimer_contact" class="form-control"
                     placeholder="Enter your phone number" required>
            </div>
            <div class="mb-4">
              <label class="form-label" style="font-size:0.83rem; font-weight:600; color:#2c3050;">
                <i class="bi bi-chat-right-text-fill me-1" style="color:#198754;"></i>Additional Note
                <span style="font-weight:400; color:#aaa;">(optional)</span>
              </label>
              <textarea name="claimer_note" class="form-control" rows="3"
                        placeholder="e.g. I can collect from campus tomorrow between 10am–2pm"></textarea>
            </div>
            <button type="submit" name="claim" class="btn-success-custom">
              <i class="bi bi-box-arrow-in-down-right"></i> Submit Claim Request
            </button>
          </form>
        </div>
        <?php endif; ?>

      <?php endif; ?>

      <!-- MESSAGE BOX -->
      <div class="msg-box">
        <div class="section-label">
          <i class="bi bi-chat-left-text-fill me-1" style="color:var(--primary);"></i>
          Send a Message
        </div>

        <p class="text-muted small mb-3">
          <i class="bi bi-info-circle me-1"></i>
          <?php if (isset($_SESSION['user_name'])): ?>
            Sending as <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
          <?php else: ?>
            Sending as <strong>Guest</strong> —
            <a href="login.php" style="color:var(--primary);">Login</a> to use your name.
          <?php endif; ?>
        </p>

        <form method="POST">
          <div class="mb-3">
            <textarea name="message" class="form-control" rows="4"
                      placeholder="Write your message to the poster..." required></textarea>
          </div>
          <button type="submit" name="send_msg" class="btn-primary-custom">
            <i class="bi bi-send-fill"></i> Send Message
          </button>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="py-5 mt-5">
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-4">
        <h5 class="fw-bold text-white mb-3">Lost&amp;Found</h5>
        <p style="color:#8892a4; line-height:1.7;">A smart platform to help people report lost items and return found items quickly and safely.</p>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="fw-bold text-white mb-3">Quick Links</h6>
        <a href="index.php" class="d-block mb-2">Home</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="login.php"    class="d-block mb-2">Login</a>
          <a href="register.php" class="d-block mb-2">Register</a>
        <?php else: ?>
          <a href="dashboard.php" class="d-block mb-2">Dashboard</a>
          <a href="logout.php" class="d-block mb-2" style="color:#dc3545;">Logout</a>
        <?php endif; ?>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="fw-bold text-white mb-3">Contact</h6>
        <p class="mb-2"><i class="bi bi-geo-alt-fill me-2"></i>Narayanganj, Bangladesh</p>
        <p class="mb-2"><i class="bi bi-envelope-fill me-2"></i>support@lostfound.com</p>
        <p class="mb-0"><i class="bi bi-telephone-fill me-2"></i>+880 15334-77264</p>
      </div>
    </div>
    <hr style="border-color:#2a2a3e;">
    <div class="text-center" style="font-family:'Arial Black',sans-serif;font-size:clamp(3rem,8vw,6rem);font-weight:900;letter-spacing:6px;text-transform:uppercase;background:linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.01));-webkit-background-clip:text;-webkit-text-fill-color:transparent;user-select:none;line-height:1;margin-bottom:-10px;">
      LOST & FOUND
    </div>
    <p class="text-center mb-0" style="color:#555d6b;">
      &copy; 2026 Lost &amp; Found System | Developed by Farhan Islam Rafid
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  <?php if (isset($_SESSION['msg_success'])): ?>
  Swal.fire({
    icon: 'success', title: 'Message Sent!',
    text: 'Your message has been delivered to the poster.',
    confirmButtonColor: '#0d6efd',
    timer: 3000, timerProgressBar: true
  });
  <?php unset($_SESSION['msg_success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['claim_success'])): ?>
  Swal.fire({
    icon: 'success',
    title: 'Claim Submitted!',
    html: 'Your claim request has been sent to the finder.<br><small class="text-muted">They will contact you shortly.</small>',
    confirmButtonColor: '#198754',
    confirmButtonText: 'Got it'
  });
  <?php unset($_SESSION['claim_success']); ?>
  <?php endif; ?>
</script>
</body>
</html>