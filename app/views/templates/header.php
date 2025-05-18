<!DOCTYPE html>
<html lang="en">  
<head>  
  <meta charset="utf-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  <title><?= $data["judul"] ?></title>  
  <link rel="icon" href="<?= BASEURL ?>/assets/img/logotoko.png" type="image/x-icon">

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    const isLoggedIn = <?= isset($_SESSION["logged_in"]) &&
    $_SESSION["logged_in"] === true
      ? "true"
      : "false" ?>;
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


<!-- Shopping Cart Offcanvas -->  <div class="offcanvas offcanvas-end" id="offcanvasCart">  
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
    <?php Flasher::flash() ?>
  </div>
</div>