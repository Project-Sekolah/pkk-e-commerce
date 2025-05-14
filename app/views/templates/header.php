<!DOCTYPE html>
<html lang="en">  
<head>  
  <meta charset="utf-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  <title><?= $data['judul']; ?></title>  
  <link rel="icon" href="<?= BASEURL; ?>/assets/img/logotoko.png" type="image/x-icon">

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const isLoggedIn = <?= isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true ? 'true' : 'false' ?>;
  </script>

  <!-- Bootstrap CSS -->
  <link href="<?= BASEURL; ?>/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">   

  <!-- Custom Styles -->
  <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/styles.css">  

  <!-- Icons & Fonts -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
</head>  
<body>  

<!-- Sidebar (Offcanvas) -->
<div class="offcanvas offcanvas-start overflow-auto" tabindex="-1" id="sidebar">
  <div class="offcanvas-header">
    <img src="<?= BASEURL; ?>/assets/img/logotoko.png" alt="Lunerburg & Co" class="img-fluid" style="height: 40px;">
    <h5 class="sidebar-title">Lunerburg & Co</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <hr>

  <!-- Login/Register Section -->
  <div id="auth-section" <?= isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true ? 'style="display: none;"' : '' ?>>
    <div class="px-3 py-2">

      <!-- Login Form -->
      <div id="login-section" <?= isset($_POST['register']) ? 'style="display: none;"' : '' ?>>
        <h5 class="mb-3">Login</h5>
        <?php if (!empty($data['error']) && empty($_POST['register'])): ?>
          <p class="text-danger"><?= $data['error']; ?></p>
        <?php endif; ?>

        <form action="<?= BASEURL; ?>/user/login" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
          <div class="mb-3">
            <label for="login-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="login-email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="login-password" class="form-label">Password</label>
            <input type="password" class="form-control" id="login-password" name="password" required>
          </div>
          <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>

        <div class="mt-2 text-center">
          <span class="toggle-link text-primary" onclick="showRegister()" style="cursor: pointer;">
            Belum punya akun? Daftar
          </span>
        </div>
      </div>

      <!-- Register Form -->
      <div id="register-section" <?= isset($_POST['register']) ? '' : 'style="display: none;"' ?>>
        <h5 class="mb-3">Register</h5>
        <?php if (!empty($data['error']) && !empty($_POST['register'])): ?>
          <p class="text-danger"><?= $data['error']; ?></p>
        <?php endif; ?>

        <form action="<?= BASEURL; ?>/user/register" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
          <div class="mb-3">
            <label for="reg-username" class="form-label">Username</label>
            <input type="text" class="form-control" id="reg-username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="reg-fullname" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="reg-fullname" name="full_name" required>
          </div>
          <div class="mb-3">
            <label for="reg-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="reg-email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="reg-password" class="form-label">Password</label>
            <input type="password" class="form-control" id="reg-password" name="password" required>
          </div>
          <div class="mb-3">
            <label for="reg-confirm-password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="reg-confirm-password" name="confirm_password" required>
          </div>
          <input type="hidden" name="role" value="buyer">
          <button type="submit" name="register" class="btn btn-success w-100">Daftar</button>
        </form>

        <div class="mt-2 text-center">
          <span class="toggle-link text-primary" onclick="showLogin()" style="cursor: pointer;">
            Sudah punya akun? Login
          </span>
        </div>
      </div>

    </div>
    <hr>
  </div>

  <!-- SweetAlert Notification -->
  <?php if (isset($_SESSION['alert'])): ?>
    <script>
      Swal.fire({
        title: "<?= $_SESSION['alert']['type'] === 'success' ? 'Success!' : 'Error!' ?>",
        text: "<?= $_SESSION['alert']['message'] ?>",
        icon: "<?= $_SESSION['alert']['type'] ?>",
        confirmButtonText: "OK"
      });
    </script>
    <?php unset($_SESSION['alert']); ?>
  <?php endif; ?>

  <!-- User Info -->
  <div class="px-3 py-2" id="user-info-section" style="display: none;">
    <h6>Informasi Lengkap</h6>
    <div id="user-info" class="text-success"></div>
    <a href="<?= BASEURL ?>/user/profile">
      <button class="btn btn-link w-100" data-bs-toggle="modal" data-bs-target="#userInfoModal">
      Lihat Informasi Lengkap
     </button>
    </a>
    <hr>
  </div>

  <!-- Navigation Links -->
  <div class="px-3">
    <a href="<?= BASEURL ?>/" class="d-flex align-items-center mb-3"><i class="bi bi-house-door-fill"></i><span class="ms-2">Home</span></a>
    <a href="<?= BASEURL ?>/about" class="d-flex align-items-center mb-3"><i class="bi bi-info-circle-fill"></i><span class="ms-2">About</span></a>
    <a href="<?= BASEURL ?>/product" class="d-flex align-items-center mb-3"><i class="bi bi-bag-fill"></i><span class="ms-2">Produk</span></a>
    <a href="<?= BASEURL ?>/pembayaran" class="d-flex align-items-center mb-3"><i class="bi bi-credit-card-fill"></i><span class="ms-2">Pembayaran</span></a>
    <a href="<?= BASEURL ?>/faq" class="d-flex align-items-center mb-3"><i class="bi bi-question-circle-fill"></i><span class="ms-2">Faq</span></a>
    <hr>
    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
      <a href="<?= BASEURL ?>/user/logout" class="d-flex align-items-center mb-3"><i class="bi bi-door-closed-fill"></i><span class="ms-2">Logout</span></a>
    <?php endif; ?>
  </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg topbar fixed-top shadow-sm">
  <div class="container-fluid d-flex justify-content-between px-3">
    <!-- Tombol hamburger (menu) -->
    <button class="btn border-0 me-2" id="menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
      <i class="bi bi-list" style="font-size: 1.5rem;"></i>
    </button>

    <!-- Nama brand yang posisinya di sebelah tombol hamburger -->
    <a class="navbar-brand fw-bold" href="#">Lunerburg & Co</a>

    <!-- Cart button -->
    <div class="me-3">
      <button class="btn btn-cart position-relative" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-warning text-white px-35">0</span>
      </button>
    </div>
  </div>
</nav>



<!-- Shopping Cart Offcanvas -->
<div class="offcanvas offcanvas-end" id="offcanvasCart">
  <div class="offcanvas-header">
    <h5>Shopping Cart</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="list-group" id="cart-items"></ul>
    <div class="mt-4 d-flex justify-content-between">
      <span><strong>Total:</strong> $<span id="total-price">0.00</span></span>
      <button class="btn btn-primary">Checkout</button>
    </div>
  </div>
</div>




