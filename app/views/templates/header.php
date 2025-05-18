<!DOCTYPE html>
<html lang="en">  
<head>  
  <!-- Charset & Viewport -->
  <meta charset="utf-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Warna tema -->
  <meta name="theme-color" content="#F0E8DA">

  <!-- SEO dasar -->
  <meta name="description" content="Toko online Lunerburg & Co. Menyediakan produk-produk terbaik dengan layanan checkout, login, register, dan FAQ.">
  <meta name="keywords" content="lunerburg, e-commerce, produk, checkout, login, register, FAQ, pengujian, belanja online">
  <meta name="author" content="Tim Pengembang Lunerburg & Co">

  <!-- Open Graph (sosial media) -->
  <meta property="og:title" content="Lunerburg & Co - Toko Online Terpercaya">
  <meta property="og:description" content="Jelajahi produk terbaik dan nikmati pengalaman belanja yang mudah dan aman bersama Lunerburg & Co.">
  <meta property="og:image" content="<?= BASEURL ?>/assets/img/preview.jpg">
  <meta property="og:url" content="<?= BASEURL ?>">
  <meta property="og:type" content="website">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Lunerburg & Co - Toko Online Terpercaya">
  <meta name="twitter:description" content="Belanja aman dan cepat di Lunerburg & Co. Dapatkan produk berkualitas dengan layanan terbaik.">
  <meta name="twitter:image" content="<?= BASEURL ?>/assets/img/preview.jpg">

  <!-- Keamanan -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https://*; font-src 'self' https://*;">

  <!-- Favicon -->
  <link rel="icon" href="<?= BASEURL ?>/assets/img/logotoko.png" type="image/x-icon">

  <!-- Judul Halaman -->
  <title><?= $data["judul"] ?></title>  

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    const isLoggedIn = <?= isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true ? "true" : "false" ?>;
  </script>

  <!-- Bootstrap CSS -->
  <link href="<?= BASEURL ?>/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">   

  <!-- Custom Styles -->
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/styles.css">  

  <!-- Icons & Fonts -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
</head>  
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg topbar fixed-top shadow-sm">
  <div class="container-fluid d-flex justify-content-between px-3">
    <!-- Tombol hamburger -->
    <button class="btn border-0 me-2" id="menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
      <i class="bi bi-list" style="font-size: 1.5rem;"></i>
    </button>

    <!-- Brand -->
    <a class="navbar-brand fw-bold" href="#">Lunerburg & Co</a>

    <!-- Cart -->
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

<div class="row">
  <div class="col-lg-6">
    <?php Flasher::flash(); ?>
  </div>
</div>