<!-- Sidebar (Offcanvas) -->
<div class="offcanvas offcanvas-start overflow-auto" tabindex="-1" id="sidebar">
  <div class="offcanvas-header">
    <img src="<?= BASEURL ?>/assets/img/logotoko.png" alt="Lunerburg & Co" class="img-fluid" style="height: 40px;">
    <h5 class="sidebar-title">Lunerburg & Co</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
    <hr>

  <!-- Login/Register Section -->
  <div id="auth-section" <?= isset($_SESSION["logged_in"]) &&
  $_SESSION["logged_in"] === true
    ? 'style="display: none;"'
    : "" ?>>
    <div class="px-3 py-2">

      <!-- Login Form -->
      <div id="login-section" <?= isset($_POST["register"])
        ? 'style="display: none;"'
        : "" ?>>
        <h5 class="mb-3">Login</h5>
        <?php if (!empty($data["error"]) && empty($_POST["register"])): ?>
          <p class="text-danger"><?= $data["error"] ?></p>
        <?php endif; ?>

        <form action="<?= BASEURL ?>/user/login" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION[
            "csrf_token"
          ] ?? "" ?>">
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
      <div id="register-section" <?= isset($_POST["register"])
        ? ""
        : 'style="display: none;"' ?>>
        <h5 class="mb-3">Register</h5>
        <?php if (!empty($data["error"]) && !empty($_POST["register"])): ?>
          <p class="text-danger"><?= $data["error"] ?></p>
        <?php endif; ?>

        <form action="<?= BASEURL ?>/user/register" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION[
            "csrf_token"
          ] ?? "" ?>">
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
  <?php if (isset($_SESSION["alert"])): ?>
    <script>
      Swal.fire({
        title: "<?= $_SESSION["alert"]["type"] === "success"
          ? "Success!"
          : "Error!" ?>",
        text: "<?= $_SESSION["alert"]["message"] ?>",
        icon: "<?= $_SESSION["alert"]["type"] ?>",
        confirmButtonText: "OK"
      });
    </script>
    <?php unset($_SESSION["alert"]); ?>
  <?php endif; ?>

  <!-- User Info -->
  <?php if (isset($_SESSION["user"])): ?>
  <?php $user = $data["user"]; ?>
  <div class="px-3 py-2" id="user-info-section" style="display: block;">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h6 class="mb-0 fw-semibold"><?= htmlspecialchars(
          $user["username"]
        ) ?></h6>
        <small class="text-muted"><?= htmlspecialchars(
          $user["email"]
        ) ?></small>
      </div>
<img src="<?php if (!empty($user["image"])) {
  echo strpos($user["image"], "res.cloudinary.com") !== false
    ? htmlspecialchars($user["image"])
    : BASEURL . "/" . ltrim(htmlspecialchars($user["image"]), "/");
} else {
  echo BASEURL . "/assets/images/default.png";
} ?>"
alt="Foto Profil <?= htmlspecialchars($user["username"]) ?>"
class="rounded-circle"
width="48"
height="48"
style="object-fit: cover;"
loading="lazy">
    </div>

    <div class="mt-3 text-success" id="user-info"></div>

    <!-- Jika ingin menggunakan modal, jangan gunakan <a href>. Pilih salah satu -->
    <a href="<?= BASEURL ?>/user/profile">
      <button class="btn btn-link w-100" data-bs-toggle="modal" data-bs-target="#userInfoModal">
      Lihat Informasi Lengkap
    </button>
    </a>

    <hr>
  </div>
  <?php endif; ?>



  <!-- Navigation Links -->
  <div class="px-3">
    <a href="<?= BASEURL ?>/" class="d-flex align-items-center mb-3"><i class="bi bi-house-door-fill"></i><span class="ms-2">Home</span></a>
    <a href="<?= BASEURL ?>/about" class="d-flex align-items-center mb-3"><i class="bi bi-info-circle-fill"></i><span class="ms-2">About</span></a>
    <a href="<?= BASEURL ?>/product" class="d-flex align-items-center mb-3"><i class="bi bi-bag-fill"></i><span class="ms-2">Produk</span></a>
    <a href="<?= BASEURL ?>/order" class="d-flex align-items-center mb-3"><i class="bi bi-credit-card-fill"></i><span class="ms-2">Pembayaran</span></a>
    <a href="<?= BASEURL ?>/faq" class="d-flex align-items-center mb-3"><i class="bi bi-question-circle-fill"></i><span class="ms-2">Faq</span></a>
    <hr>
   <?php if (
     isset($_SESSION["logged_in"]) &&
     $_SESSION["logged_in"] === true
   ): ?>
      <?php if (
        $_SESSION["user"]["role"] === "admin" ||
        $_SESSION["user"]["role"] === "seller"
      ): ?>
        <a href="<?= BASEURL ?>/product/seller" class="d-flex align-items-center mb-3"><i class="bi bi-cart-fill"></i><span class="ms-2">Produk Saya</span></a>
        <a href="<?= BASEURL ?>/discount" class="d-flex align-items-center
        mb-3"><i class="bi bi-tag-fill"></i><span class="ms-2">Discount
        Saya</span></a>
      <?php endif; ?>
      <a href="<?= BASEURL ?>/user/logout" class="d-flex align-items-center mb-3"><i class="bi bi-door-closed-fill" id="logout-link"></i><span class="ms-2">Logout</span></a>
    <?php endif; ?>
  </div>
</div>





