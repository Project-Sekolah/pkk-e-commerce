<!DOCTYPE html>
<html lang="en">  
<head>  
  <meta charset="utf-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  <title><?= $data['judul']; ?></title>  
   <link rel="icon" href="<?= BASEURL; ?>/assets/img/logotoko.png" type="image/x-icon">
  <!-- Bootstrap CSS -->
  <link href="<?= BASEURL; ?>/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">   

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/styles.css">  


  <!-- Font Awesome -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">


  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>  
<body>  

  <!-- Sidebar (Offcanvas) -->
<div class="offcanvas offcanvas-start overflow-auto" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
  <div class="offcanvas-header">
    <img src="<?= BASEURL; ?>/assets/img/logotoko.png" alt="Lunerburg & Co" class="img-fluid" style="height: 40px;">
    <h5 class="sidebar-title">Lunerburg & Co</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <hr>

  <!-- Login/Register Section -->
  <div class="px-3 py-2">
    <!-- Login Form -->
    <div id="login-section">
      <h5 class="mb-3">Login</h5>
      <form action="" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
    <div class="mb-3">
        <label for="login-email" class="form-label">Email</label>
        <input type="email" class="form-control" id="login-email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="login-password" class="form-label">Password</label>
        <input type="password" class="form-control" id="login-password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Login</button>
</form>
      <div class="mt-2 text-center">
        <span class="toggle-link" onclick="showRegister()" style="font-size: 14px; cursor: pointer; color: blue;">Belum punya akun? Daftar</span>
      </div>
    </div>

    <!-- Register Form -->
    <div id="register-section" style="display: none;">
      <h5 class="mb-3">Register</h5>
      <form action="" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
    <div class="mb-3">
        <label for="reg-username" class="form-label">Username</label>
        <input type="text" class="form-control" id="reg-username" name="username" required>
    </div>
    <div class="mb-3">
        <label for="reg-email" class="form-label">Email</label>
        <input type="email" class="form-control" id="reg-email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="reg-password" class="form-label">Password</label>
        <input type="password" class="form-control" id="reg-password" name="password" required>
    </div>
    <input type="hidden" name="role" value="buyer">
    <button type="submit" class="btn btn-success w-100">Daftar</button>
</form>
      <div class="mt-2 text-center">
        <span class="toggle-link" onclick="showLogin()" style="font-size: 14px; cursor: pointer; color: blue;">Sudah punya akun? Login</span>
      </div>
    </div>
  </div>

  <hr>

  <!-- User Info Section -->
  <div class="px-3 py-2">
    <h6>Informasi Lengkap</h6>
    <div id="user-info" class="text-success"></div>
    <button class="btn btn-link w-100" data-bs-toggle="modal" data-bs-target="#userInfoModal">
      Lihat Informasi Lengkap
    </button>
  </div>

  <hr>

  <!-- Navigation Links -->
  <div class="px-3">
    <a href="<?= BASEURL ?>/" class="d-flex align-items-center mb-3">
      <i class="bi bi-house-door-fill"></i>
      <span class="ms-2">Home</span>
    </a>
    <a href="<?= BASEURL ?>/about" class="d-flex align-items-center mb-3">
      <i class="bi bi-info-circle-fill"></i>
      <span class="ms-2">About</span>
    </a>
    <a href="<?= BASEURL ?>/product" class="d-flex align-items-center mb-3">
      <i class="bi bi-bag-fill"></i>
      <span class="ms-2">Produk</span>
    </a>
    <a href="<?= BASEURL ?>/pembayaran" class="d-flex align-items-center mb-3">
      <i class="bi bi-credit-card-fill"></i>
      <span class="ms-2">Pembayaran</span>
    </a>
    <a href="<?= BASEURL ?>/faq" class="d-flex align-items-center mb-3">
      <i class="bi bi-question-circle-fill"></i>
      <span class="ms-2">Faq</span>
    </a>
    <hr>
    <a href="<?= BASEURL ?>/user/logout"class="d-flex align-items-center mb-3">
      <i class="bi bi-door-closed-fill"></i>
      <span class="ms-2">Logout</span>
    </a>
  </div>
</div>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg topbar fixed-top">  
    <div class="container-fluid">  
      <button class="btn" id="menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">☰</button>  
      <a class="navbar-brand ms-4">Lunerburg & Co</a>  

      <!-- Cart Icon -->
      <button class="btn btn-cart ms-auto" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">  
        <i class="fas fa-shopping-cart"></i>  
        <span id="cart-count" class="badge rounded-circle">0</span>  
      </button>  
    </div>  
  </nav>    

  <!-- Cart Offcanvas -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">  
    <div class="offcanvas-header">  
      <h5 id="offcanvasCartLabel">Shopping Cart</h5>  
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>  
    </div>  
    <div class="offcanvas-body">  
      <ul class="list-group" id="cart-items">  
        <!-- Cart items will be dynamically added here -->  
      </ul>  yout may be impa
      <div class="mt-4 d-flex justify-content-between">  
        <span><strong>Total:</strong> $<span id="total-price">0.00</span></span>  
        <button class="btn btn-primary">Checkout</button>  
      </div>  
    </div>  
  </div>  
