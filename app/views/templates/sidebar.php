<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
  <div class="offcanvas-header">
    <img src="img/logotoko.png" alt="Lunerburg & Co" class="img-fluid" style="height: 40px;">
    <h5 class="ms-3">Lunerburg & Co</h5>
  </div>
  <!-- Garis Pemisah -->
  <hr>
  
  <!-- Menu Register -->
<div class="px-3 py-2">
  <h6>Registrasi</h6>
  <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">Registrasi gagal. Silakan coba lagi.</div>
      <?php endif; ?>
  <form id="register-form">
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" class="form-control" id="username" placeholder="Masukkan username" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Gmail</label>
      <input type="email" class="form-control" id="email" placeholder="Masukkan email" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" placeholder="Masukkan password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Daftar</button>
  </form>
  <div id="register-message" class="mt-2"></div>
  <a href="login.html" style="font-size:14px; display:block; margin-top:8px;">Sudah punya akun? Login di sini</a>
</div>

  
  <!-- User Info Section -->
  <div class="px-3 py-2">
    <h6>Informasi Lengkap</h6>
    <div id="user-info" class="text-success"></div>
    <button class="btn btn-link w-100" data-bs-toggle="modal" data-bs-target="#userInfoModal">Lihat Informasi Lengkap</button>
  </div>
  <hr>
  
  <!-- Navigation Links -->
  <div class="px-3">
    <a href="index.html" class="d-flex align-items-center mb-3">
      <i class="bi bi-house-door-fill"></i>
      <span class="ms-2">Home</span>
    </a>
    <a href="#produk" class="d-flex align-items-center mb-3">
      <i class="bi bi-bag-fill"></i>
      <span class="ms-2">Produk</span>
    </a>
    <a href="women.html" class="d-flex align-items-center mb-3">
      <i class="bi bi-gender-female" style="color: deeppink;"></i>
      <span class="ms-2">Produk Wanita</span>
    </a>
    <a href="men.html" class="d-flex align-items-center mb-3">
      <i class="bi bi-gender-male" style="color: dodgerblue;"></i>
      <span class="ms-2">Produk Pria</span>
    </a>
    <a href="#" class="d-flex align-items-center mb-3">
      <i class="bi bi-credit-card-fill"></i>
      <span class="ms-2">Pembayaran</span>
    </a>
    <hr>
    <a href="logout.html" class="d-flex align-items-center mb-3">
      <i class="bi bi-box-arrow-right"></i>
      <span class="ms-2">Logout</span>
    </a>
  </div>
</div>
