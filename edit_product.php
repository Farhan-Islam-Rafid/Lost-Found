<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM products WHERE Id='$id'");
$row = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location    = mysqli_real_escape_string($conn, $_POST['location']);
    $contact     = mysqli_real_escape_string($conn, $_POST['contact']);
    $question    = mysqli_real_escape_string($conn, $_POST['question']);
    $answer      = mysqli_real_escape_string($conn, $_POST['answer']);
    $type        = mysqli_real_escape_string($conn, $_POST['type']);

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
        $query = "UPDATE products SET name='$name', description='$description', location='$location',
                    contact='$contact', question='$question', answer='$answer',
                    type='$type', image='$image' WHERE Id='$id'";
    } else {
        $query = "UPDATE products SET name='$name', description='$description', location='$location',
                    contact='$contact', question='$question', answer='$answer',
                    type='$type' WHERE Id='$id'";
    }

    mysqli_query($conn, $query);
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Item | Lost&Found</title>
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

    /* TOP BAR */
    .top-bar {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      padding: 14px 0;
    }
    .top-bar a {
      color: rgba(255,255,255,0.85);
      text-decoration: none;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: color 0.2s;
    }
    .top-bar a:hover { color: #fff; }
    .top-bar .brand { color: #fff; font-weight: 700; font-size: 1.1rem; }

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
      max-width: 680px;
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
    .form-control, .form-select {
      border-radius: var(--radius);
      border: 1.5px solid #e2e6f0;
      padding: 10px 14px;
      font-size: 0.93rem;
      color: #2c3050;
      background: #fafbff;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
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

    /* IMAGE PREVIEW */
    .img-current {
      border-radius: var(--radius);
      border: 2px solid #e2e6f0;
      background: #f8faff;
      text-align: center;
      padding: 10px;
      margin-bottom: 12px;
    }
    .img-current img {
      max-height: 200px;
      width: 100%;
      object-fit: cover;
      border-radius: 8px;
    }
    .img-label {
      font-size: 0.75rem;
      color: #8898b0;
      margin-top: 6px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }

    /* DIVIDER */
    .form-divider { height: 1px; background: #eef0f5; margin: 24px 0; }

    /* BUTTONS */
    .btn-update {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 13px 20px;
      font-weight: 600;
      font-size: 0.95rem;
      width: 100%;
      transition: opacity 0.2s, transform 0.15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .btn-update:hover { opacity: 0.9; transform: scale(1.01); }

    .btn-cancel {
      border-radius: 50px;
      padding: 13px 20px;
      font-weight: 600;
      font-size: 0.95rem;
      color: #6b7280;
      border: 1.5px solid #e2e6f0;
      background: #fff;
      width: 100%;
      transition: 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-decoration: none;
    }
    .btn-cancel:hover { background: #f0f4ff; border-color: #bbb; color: #333; }

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

<!-- TOP BAR -->
<div class="top-bar">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="dashboard.php">
      <i class="bi bi-arrow-left-circle-fill fs-5"></i> Back to Dashboard
    </a>
    <span class="brand">Lost&amp;Found</span>
  </div>
</div>

<!-- PAGE -->
<div class="page-wrap">
  <div class="form-card">

    <!-- HEADER -->
    <div class="card-header-custom">
      <h4><i class="bi bi-pencil-square me-2"></i>Edit Item</h4>
      <p>Update the details of your post below</p>
    </div>

    <!-- BODY -->
    <div class="card-body-custom">
      <form method="POST" enctype="multipart/form-data">

        <!-- Basic Info -->
        <div class="section-label">Basic Information</div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-tag-fill"></i>Item Name</label>
          <input type="text" name="name" class="form-control"
                 placeholder="e.g. Blue Backpack"
                 value="<?php echo htmlspecialchars($row['name']); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-card-text"></i>Description</label>
          <textarea name="description" class="form-control" rows="4"
                    placeholder="Describe the item in detail..."><?php echo htmlspecialchars($row['description']); ?></textarea>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-sm-6">
            <label class="form-label"><i class="bi bi-geo-alt-fill"></i>Location</label>
            <input type="text" name="location" class="form-control"
                   placeholder="Where lost or found"
                   value="<?php echo htmlspecialchars($row['location']); ?>">
          </div>
          <div class="col-sm-6">
            <label class="form-label"><i class="bi bi-telephone-fill"></i>Contact</label>
            <input type="text" name="contact" class="form-control"
                   placeholder="Your phone number"
                   value="<?php echo htmlspecialchars($row['contact']); ?>">
          </div>
        </div>

        <div class="form-divider"></div>

        <!-- Type -->
        <div class="section-label">Item Type</div>

        <div class="mb-3">
          <div class="type-toggle">
            <input type="radio" name="type" id="typeLost"  value="Lost"
                   <?php if ($row['type'] == "Lost")  echo "checked"; ?>>
            <label for="typeLost">
              <i class="bi bi-exclamation-circle-fill"></i> Lost
            </label>

            <input type="radio" name="type" id="typeFound" value="Found"
                   <?php if ($row['type'] == "Found") echo "checked"; ?>>
            <label for="typeFound">
              <i class="bi bi-check-circle-fill"></i> Found
            </label>
          </div>
        </div>

        <!-- Verify Box -->
        <div id="verifyBox">
          <div class="verify-inner">
            <div class="section-label" style="border:none; margin-bottom:14px;">
              <i class="bi bi-shield-lock-fill me-1" style="color:var(--primary);"></i>
              Verification — For Found Items Only
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="bi bi-question-circle-fill"></i>Verification Question</label>
              <input type="text" name="question" class="form-control"
                     placeholder="e.g. What color is the strap?"
                     value="<?php echo htmlspecialchars($row['question']); ?>">
            </div>
            <div>
              <label class="form-label"><i class="bi bi-check2-square"></i>Correct Answer</label>
              <input type="text" name="answer" class="form-control"
                     placeholder="e.g. Red"
                     value="<?php echo htmlspecialchars($row['answer']); ?>">
            </div>
          </div>
        </div>

        <div class="form-divider"></div>

        <!-- Image -->
        <div class="section-label">Item Image</div>

        <div class="img-current">
          <img id="imgPreview"
               src="uploads/<?php echo htmlspecialchars($row['image']); ?>"
               alt="Current item image">
          <div class="img-label">
            <i class="bi bi-image me-1"></i>Current Image
          </div>
        </div>

        <div class="mb-1">
          <label class="form-label"><i class="bi bi-upload"></i>Upload New Image</label>
          <input type="file" name="image" class="form-control" accept="image/*"
                 onchange="previewImage(this)">
        </div>
        <small class="text-muted d-block mb-4">
          <i class="bi bi-info-circle me-1"></i>Leave empty to keep the current image
        </small>

        <!-- Action Buttons -->
        <div class="row g-3">
          <div class="col-sm-4">
            <a href="dashboard.php" class="btn-cancel">
              <i class="bi bi-x-circle"></i> Cancel
            </a>
          </div>
          <div class="col-sm-8">
            <button type="submit" name="update" class="btn-update">
              <i class="bi bi-check-circle-fill"></i> Save Changes
            </button>
          </div>
        </div>

      </form>
    </div>

  </div>
</div>

<script>
  function previewImage(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => document.getElementById('imgPreview').src = e.target.result;
      reader.readAsDataURL(input.files[0]);
    }
  }

  const radios    = document.querySelectorAll('input[name="type"]');
  const verifyBox = document.getElementById('verifyBox');

  function toggleVerify() {
    const isFound = document.querySelector('input[name="type"]:checked').value === 'Found';
    verifyBox.style.maxHeight = isFound ? '320px' : '0px';
    verifyBox.style.opacity   = isFound ? '1'     : '0';
  }

  radios.forEach(r => r.addEventListener('change', toggleVerify));
  toggleVerify();
</script>

</body>
</html>