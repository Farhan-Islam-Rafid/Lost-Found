<?php
include 'db.php';
$msg = "";


// ````````````REGISTER ``````````
if (isset($_POST['register'])) {

  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $sql = "INSERT INTO users (name, email, phone, password)
          VALUES ('$name', '$email', '$phone', '$password')";

  if (mysqli_query($conn, $sql)) {
    header("Location: login.php");
    exit();
  } else {
    $msg = "Register Failed";
  }
}


?>
<!-- main html part -->
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login & Register | Lost & Found</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
        footer { background: #12121e; color: #cdd2dc; }
    footer a { color: #cdd2dc; text-decoration: none; transition: 0.2s; }
    footer a:hover { color: #0d6efd; padding-left: 5px; }
  </style>
</head>

<body class="bg-light">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark ">
    <div class="container">


      <div class="navbar-brand text-white fw-bold" style="font-size: 24px;">
        Lost&Found
      </div>


      </a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto align-items-center">

          <!-- Menu Items -->
          <li class="nav-item me-3"><a class="nav-link" href="index.php">Home</a></li>


          <!-- SPACE CREATE (IMPORTANT) -->
          <li class="nav-item d-none d-lg-block" style="width:30px;"></li>

          <!-- PHP PART START -->
          <?php if (isset($_SESSION['user_name'])): ?>

            <li class="nav-item dropdown">

              <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2 px-3 py-2"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                style="
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        border-radius: 25px;
        font-weight: 500;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
     ">

                👤 <?php echo $_SESSION['user_name']; ?>

              </a>

              <ul class="dropdown-menu dropdown-menu-end shadow"
                style="border-radius:12px; border:none;">

                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="dashboard.php">
                    <i class="bi bi-speedometer2" style="font-size:18px;"></i>
                    Dashboard
                  </a>
                </li>

                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="add_product.php">
                    <i class="bi bi-plus-circle" style="font-size:18px;"></i>
                    Add Product
                  </a>
                </li>

                <li>
                  <hr class="dropdown-divider">
                </li>

                <li>
                  <a class="dropdown-item text-danger fw-bold d-flex align-items-center gap-2" href="logout.php">
                    <i class="bi bi-box-arrow-right" style="font-size:18px;"></i>
                    Logout
                  </a>
                </li>

              </ul>

            </li>

            ?>

          <?php else: ?>

            <li class="nav-item me-2 mb-2 mb-lg-0">
              <a href="login.php"
                class="btn btn-success btn-sm w-100 w-lg-auto"
                style="border-radius:20px; padding:6px 14px;">
                Login
              </a>
            </li>

            <li class="nav-item mb-2 mb-lg-0">
              <a href="register.php"
                class="btn btn-outline-success btn-sm w-100 w-lg-auto"
                style="border-radius:20px; padding:6px 14px;">
                Register
              </a>
            </li>

          <?php endif; ?>


        </ul>
      </div>
    </div>
  </nav>



  <!--echo message -->
  <div class="text-center mt-3 fw-bold">
    <?php echo $msg; ?>
  </div>

  <!-- register section -->
  <section class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">

          <div class="card shadow p-4">

            <div class="row">



              <!-- REGISTER -->
              <div class="col-md-6 ps-4 mx-auto">

                <h4 class="text-success mb-4 text-center">Register</h4>

                <form method="POST">

                  <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>

                  <button class="btn btn-success w-100" name="register">Register</button>

                  <p class="text-center mt-4 text-muted">
                    Already have an account?
                    <a href="login.php" class="fw-semibold text-decoration-none ms-1">
                      login here
                    </a>
                  </p>

                </form>

              </div>

            </div>

          </div>
        </div>
      </div>
    </div>
  </section>

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
        <a href="index.php"  class="d-block mb-2">Home</a>
       
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="login.php"    class="d-block mb-2">Login</a>
          <a href="register.php" class="d-block mb-2">Register</a>
        <?php else: ?>
          <a href="dashboard.php" class="d-block mb-2">Dashboard</a>
          <a href="logout.php"    class="d-block mb-2 text-danger">Logout</a>
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



  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>