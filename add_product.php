<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Item | Lost&Found</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #0d6efd;
      --primary-dark: #0944CE;
      --radius: 12px;
    }

    body {
      background: #f0f4ff;
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }

    /* NAVBAR */
    .navbar { background: rgba(18,18,30,0.97) !important; }

    /* PAGE WRAP */
    .page-wrap {
      display: flex;
      justify-content: center;
      padding: 40px 16px 60px;
    }

    /* FORM CARD */
    .form-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.10);
      overflow: hidden;
      width: 100%;
      max-width: 640px;
      animation: fadeUp 0.35s ease;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* CARD HEADER */
    .card-header-custom {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      padding: 28px 32px;
      color: #fff;
    }
    .card-header-custom h4 { font-weight: 700; margin: 0; font-size: 1.3rem; }
    .card-header-custom p  { margin: 5px 0 0; opacity: 0.8; font-size: 0.88rem; }

    /* CARD BODY */
    .card-body-custom { padding: 32px; }

    /* SECTION LABEL */
    .section-label {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: #8898b0;
      margin-bottom: 14px;
      padding-bottom: 8px;
      border-bottom: 1px solid #eef0f5;
    }

    /* FORM LABELS */
    .form-label {
      font-weight: 600;
      font-size: 0.83rem;
      color: #3a3f5c;
      margin-bottom: 5px;
    }
    .form-label i { color: var(--primary); margin-right: 5px; }

    /* INPUTS */
    .form-control {
      border-radius: var(--radius);
      border: 1.5px solid #e2e6f0;
      padding: 10px 14px;
      font-size: 0.93rem;
      color: #2c3050;
      background: #fafbff;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(13,110,253,0.10);
      background: #fff;
    }
    textarea.form-control { resize: none; }

    /* TYPE TOGGLE */
    .type-toggle { display: flex; gap: 12px; }
    .type-toggle input[type="radio"] { display: none; }
    .type-toggle label {
      flex: 1;
      text-align: center;
      padding: 12px 10px;
      border-radius: var(--radius);
      border: 2px solid #e2e6f0;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.9rem;
      color: #6b7280;
      background: #fafbff;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .type-toggle label:hover { border-color: #bbb; background: #f0f4ff; }
    .type-toggle input[value="Lost"]:checked + label {
      background: #fff1f2; color: #dc3545;
      border-color: #dc3545;
      box-shadow: 0 2px 10px rgba(220,53,69,0.12);
    }
    .type-toggle input[value="Found"]:checked + label {
      background: #f0fdf4; color: #198754;
      border-color: #198754;
      box-shadow: 0 2px 10px rgba(25,135,84,0.12);
    }

    /* VERIFY BOX */
    #verifyBox {
      overflow: hidden;
      transition: max-height 0.4s ease, opacity 0.35s ease;
    }
    .verify-inner {
      background: #f8faff;
      border: 1.5px solid #e2e6f0;
      border-radius: var(--radius);
      padding: 20px;
      margin-top: 12px;
    }

    /* IMAGE DROP ZONE */
    .image-drop {
      border: 2px dashed #d0d7e8;
      border-radius: var(--radius);
      padding: 28px 20px;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      background: #fafbff;
      position: relative;
    }
    .image-drop:hover { border-color: var(--primary); background: #f0f4ff; }
    .image-drop input[type="file"] {
      position: absolute; inset: 0;
      opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .drop-icon { font-size: 2.2rem; color: #b0bcd4; margin-bottom: 8px; }
    .drop-text { font-size: 0.88rem; color: #8898b0; }
    #imgPreview {
      max-height: 160px;
      width: 100%;
      object-fit: cover;
      border-radius: 8px;
      margin-top: 12px;
      display: none;
    }

    /* DIVIDER */
    .form-divider { height: 1px; background: #eef0f5; margin: 22px 0; }

    /* BUTTONS */
    .btn-submit {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 13px 20px;
      font-weight: 600;
      font-size: 0.95rem;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: opacity 0.2s, transform 0.15s;
    }
    .btn-submit:hover { opacity: 0.9; transform: scale(1.01); }

    .btn-cancel {
      border-radius: 50px;
      padding: 13px 20px;
      font-weight: 600;
      font-size: 0.95rem;
      color: #6b7280;
      border: 1.5px solid #e2e6f0;
      background: #fff;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-decoration: none;
      transition: 0.2s;
    }
    .btn-cancel:hover { background: #f0f4ff; border-color: #bbb; color: #333; }

    /* FOOTER */
    footer { background: #12121e; color: #cdd2dc; }
    footer a { color: #cdd2dc; text-decoration: none; transition: 0.2s; }
    footer a:hover { color: #0d6efd; padding-left: 5px; }

    /* RESPONSIVE */
    @media (max-width: 576px) {
      .card-header-custom { padding: 20px; }
      .card-body-custom   { padding: 20px; }
      .type-toggle label  { font-size: 0.82rem; padding: 10px 6px; }
    }
    @media (min-width: 1200px) {
      .form-card { max-width: 780px; }
      .card-body-custom { padding: 44px 48px; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4" href="index.php">Lost&amp;Found</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item">
          <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- PAGE -->
<div class="page-wrap">
  <div class="form-card">

    <!-- HEADER -->
    <div class="card-header-custom">
      <h4><i class="bi bi-plus-circle-fill me-2"></i>Add New Item</h4>
      <p>Fill in the details below to post your lost or found item</p>
    </div>

    <!-- BODY -->
    <div class="card-body-custom">
      <form action="save_product.php" method="POST" enctype="multipart/form-data">

        <!-- SECTION 1: Basic Info -->
        <div class="section-label">Basic Information</div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-tag-fill"></i>Item Name</label>
          <input type="text" name="name" class="form-control"
                 placeholder="e.g. Blue Backpack, Student ID Card" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-card-text"></i>Description</label>
          <textarea name="description" class="form-control" rows="4"
                    placeholder="Describe the item clearly — color, brand, size, etc." required></textarea>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-sm-6">
            <label class="form-label"><i class="bi bi-geo-alt-fill"></i>Location</label>
            <input type="text" name="location" class="form-control"
                   placeholder="e.g. ENT Lab, Main Gate" required>
          </div>
          <div class="col-sm-6">
            <label class="form-label"><i class="bi bi-telephone-fill"></i>Contact Number</label>
            <input type="text" name="contact" class="form-control"
                   placeholder="Your phone number" required>
          </div>
        </div>

        <div class="form-divider"></div>

        <!-- SECTION 2: Type -->
        <div class="section-label">Item Type</div>

        <div class="mb-3">
          <div class="type-toggle">
            <input type="radio" name="type" id="typeLost" value="Lost" checked>
            <label for="typeLost">
              <i class="bi bi-exclamation-circle-fill"></i> Lost
            </label>
            <input type="radio" name="type" id="typeFound" value="Found">
            <label for="typeFound">
              <i class="bi bi-check-circle-fill"></i> Found
            </label>
          </div>
        </div>

        <!-- VERIFY BOX (Found only) -->
        <div id="verifyBox">
          <div class="verify-inner">
            <div class="section-label" style="border:none; margin-bottom:14px;">
              <i class="bi bi-shield-lock-fill me-1" style="color:var(--primary);"></i>
              Verification — For Found Items Only
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="bi bi-question-circle-fill"></i>Verification Question</label>
              <input type="text" name="question" class="form-control"
                     placeholder="e.g. What color is the strap?">
            </div>
            <div>
              <label class="form-label"><i class="bi bi-check2-square"></i>Correct Answer</label>
              <input type="text" name="answer" class="form-control"
                     placeholder="e.g. Red">
            </div>
          </div>
        </div>

        <div class="form-divider"></div>

        <!-- SECTION 3: Image -->
        <div class="section-label">Item Image</div>

        <div class="image-drop">
          <input type="file" name="image" accept="image/*"
                 onchange="previewImage(this)" required>
          <div class="drop-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
          <div class="drop-text">
            Click to upload an image<br>
            <span style="font-size:0.8rem; color:#c0c8d8;">JPG, PNG, WEBP supported</span>
          </div>
          <img id="imgPreview" src="" alt="Preview">
        </div>

        <div class="form-divider"></div>

        <!-- ACTION BUTTONS -->
        <div class="row g-3">
          <div class="col-sm-4">
            <a href="dashboard.php" class="btn-cancel">
              <i class="bi bi-x-circle"></i> Cancel
            </a>
          </div>
          <div class="col-sm-8">
            <button type="submit" name="add" class="btn-submit">
              <i class="bi bi-cloud-upload-fill"></i> Submit Item
            </button>
          </div>
        </div>

      </form>
    </div>

  </div>
</div>

<!-- FOOTER (kept as original) -->
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
          <a href="logout.php"    class="d-block mb-2" style="color:#dc3545;">Logout</a>
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

    <!-- Big low-opacity text -->
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
      &copy; 2026 Lost &amp; Found System | Developed by Farhan Islam Rafid
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Type toggle
  const radios    = document.querySelectorAll('input[name="type"]');
  const verifyBox = document.getElementById('verifyBox');

  function toggleVerify() {
    const isFound = document.querySelector('input[name="type"]:checked').value === 'Found';
    verifyBox.style.maxHeight = isFound ? '320px' : '0px';
    verifyBox.style.opacity   = isFound ? '1'     : '0';
  }

  radios.forEach(r => r.addEventListener('change', toggleVerify));
  toggleVerify();

  // Image preview
  function previewImage(input) {
    const preview  = document.getElementById('imgPreview');
    const icon     = document.querySelector('.drop-icon');
    const dropText = document.querySelector('.drop-text');
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => {
        preview.src          = e.target.result;
        preview.style.display = 'block';
        icon.style.display    = 'none';
        dropText.style.display = 'none';
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>
</body>
</html>