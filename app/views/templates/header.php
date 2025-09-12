<!DOCTYPE html>
<html lang="en">  
<head>  
  
      <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-P7BL3D5J');</script>
<!-- End Google Tag Manager -->
  
  <!-- Charset & Viewport -->
  <meta charset="utf-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

  <!-- Warna tema -->
  <meta name="theme-color" content="#F0E8DA">

  <!-- SEO dasar -->
  <meta name="google-site-verification" content="ef5zoyHmuCvzG_XZiq3Fdr_5d_2fu0cSOJRL2Z0SZ5g" />
  <meta name="description" content="Toko online Lunerburg & Co. Menyediakan produk-produk terbaik dengan layanan checkout, login, register, dan FAQ.">
  <meta name="keywords" content="lunerburg, e-commerce, produk, checkout, login, register, FAQ, pengujian, belanja online">
  <meta name="author" content="Tim Pengembang Lunerburg & Co">


  
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-W44SYM7XQM"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-W44SYM7XQM');
  </script>
  <!-- End Google tag -->



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
 <!--  <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https://*; font-src 'self' https://*;"> -->

 <!-- Favicon -->
<link rel="icon" href="https://res.cloudinary.com/dripo32vz/image/upload/v1757645375/8dcd840d-3393-4a92-b52a-d2d116a63a51.png" type="image/png">



  <!-- Judul Halaman -->
  <title><?= $data["judul"] ?></title>  

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tambahkan ini di HTML kamu -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

  
  <!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  
  <script>
    const isLoggedIn = <?= isset($_SESSION["logged_in"]) &&
    $_SESSION["logged_in"] === true
      ? "true"
      : "false" ?>;
  </script>


  <!-- Bootstrap CSS -->
  <link href="<?= BASEURL ?>/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">   

  <!-- Custom Styles -->
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/styles.css?v<?= time() ?>">  

  <!-- Icons & Fonts -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
</head>  
<body>

  <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P7BL3D5J"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
  
  <nav class="navbar navbar-expand-lg topbar fixed-top shadow-sm">
    <div class="container-fluid d-flex justify-content-between px-3">
      <button class="btn border-0 me-2" id="menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        <i class="bi bi-list" style="font-size: 1.5rem;"></i>
      </button>
      <a class="navbar-brand fw-bold" href="#">Lunerburg & Co</a>
      <div class="me-3 position-relative">
        <button class="btn btn-light position-relative" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
          <i class="bi bi-cart3 fs-5"></i>
          <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill">0</span>
        </button>
      </div>
    </div>
  </nav>
  
  <script>
    const IS_LOGGED_IN = <?= isset($_SESSION["user"]) ? "true" : "false" ?>;
</script>

  <!-- OFFCANVAS CART -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">  
  <div class="offcanvas-header">  
    <h5 id="offcanvasCartLabel">Shopping Cart</h5>  
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>  
  </div>    <div class="offcanvas-body">  
  <!-- List Keranjang -->  
  <ul class="list-group mb-4" id="cart-items"></ul>    <!-- Ringkasan Pembayaran -->    <div class="summary-box p-4 border rounded shadow-sm bg-light">  
    <h4 class="mb-3">Ringkasan Belanja</h4>  <div class="d-flex justify-content-between mb-2">  
  <span>Subtotal</span>  
  <span id="subtotal">Rp 0</span>  
</div>  

<div class="d-flex justify-content-between mb-2">  
  <span>Ongkir & Penanganan</span>  
  <span id="delivery">Rp 0</span>  
</div>  

<div class="d-flex justify-content-between mb-2">  
  <span>Pajak & Biaya</span>  
  <span id="taxes">Rp 0</span>  
</div>  

<div class="form-check mb-3">
    <label for="discountInput" class="form-label">Discount Name</label>
    <div class="input-group">
        <input type="text" id="discountInput" class="form-control" placeholder="Enter discount name">
        <button class="btn btn-outline-primary" id="applyDiscountBtn">Apply</button>
    </div>
</div>


<div class="d-flex justify-content-between mb-2">  
  <span>Diskon</span>  
  <span id="discount">- Rp 0</span>  
</div>  

<hr>  

<div class="d-flex justify-content-between mb-3 fw-bold">  
  <span>Total</span>  
  <span id="total">Rp 0</span>  
</div>  

<div class="mb-3">
  <label for="phoneInput" class="form-label">Nomor HP (aktif Dana)</label>
  <input type="text" id="phoneInput" class="form-control" placeholder="Masukkan nomor HP aktif Dana Anda" value="<?= isset($_SESSION['user']['phone_number']) ? htmlspecialchars($_SESSION['user']['phone_number']) : '' ?>" required>
</div>
<div class="mb-3">
  <label for="passwordInput" class="form-label">Password Akun Dana</label>
  <input type="password" id="passwordInput" class="form-control" placeholder="Masukkan password akun Anda">
</div>

<div class="form-check mb-3">  
  <input class="form-check-input" type="checkbox" id="agreeTerms" />  
  <label class="form-check-label" for="agreeTerms">  
    Saya menyetujui <a href="#">syarat & ketentuan</a>  
  </label>  
</div>

<button class="btn btn-primary w-100 mb-2" onclick="checkout()" >Checkout</button>  


  </div>  
</div>  </div> 


  <div class="row">
    <div class="col-lg-6">
      <?php Flasher::flash(); ?>
    </div>
  </div>
