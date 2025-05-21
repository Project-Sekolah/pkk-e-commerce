<?php
$user = $data["user"];
$addresses = $data["addresses"];
?>

<head>
  <style type="text/css" media="all">
    .bungkus {
      background-image: url("/assets/img/background.jpg");
      background-size: cover;
      background-position: center;
      transition: all 0.5s ease;
      position: relative;
      z-index: 1;
      width: 100vw;
    }

    .bungkus::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background-color: rgba(0, 0, 0, 0);
      transition: background-color 0.5s ease;
      z-index: -1;
    }

    .bungkus:hover {
      background-size: 106%;
    }

    .bungkus:hover::before {
      background-color: rgba(0, 0, 0, 0.4);
    }

    .profile-card {
      background-color: #847e7b;
      border-radius: 20px;
      color: #fff;
      width: 340px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .bungkus a {
      text-decoration: none;
      color: #fff;
    }
  </style>
</head>

<div class="bungkus d-flex justify-content-center align-items-center min-vh-100">
  <div class="card profile-card p-4 position-relative">

    <!-- Tombol Kembali -->
    <a href="<?= BASEURL ?>">
      <i class="bi bi-arrow-bar-left fs-4 position-absolute top-0 start-0 m-3" role="button"></i>
    </a>

    <h3 class="fw-bold text-center mb-4">My Profile</h3>

    <!-- Gambar Profil -->
    <img src="<?php if (!empty($user["image"])) {
      echo strpos($user["image"], "res.cloudinary.com") !== false
        ? htmlspecialchars($user["image"])
        : BASEURL . "/" . ltrim(htmlspecialchars($user["image"]), "/");
    } else {
      echo BASEURL . "/assets/img/default.jpg";
    } ?>" class="rounded-circle mx-auto mb-3" width="100" height="100" alt="avatar">

    <!-- Info User -->
    <h5 class="fw-bold mb-1 text-center"><?= htmlspecialchars(
      $user["username"]
    ) ?></h5>
    <p class="text-muted mb-1 text-center"><?= htmlspecialchars(
      $user["email"]
    ) ?></p>
    <p class="text-muted mb-4 text-center"><?= htmlspecialchars(
      $user["full_name"]
    ) ?></p>

    <!-- Tombol Aksi -->
    <div class="text-start mb-4">
      <p class="text-muted">
        <a href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal">
          <i class="bi bi-person-fill-gear me-2"></i> Edit Profile
        </a>
      </p>
      <p class="text-muted">
        <a href="#" data-bs-toggle="modal" data-bs-target="#addAddressModal">
          <i class="bi bi-geo-alt-fill me-2"></i> Add New Address
        </a>
      </p>
    </div>

    <!-- Tombol Seller & Logout -->
    <a href="#" class="btn btn-dark rounded-pill px-4 mb-2">
      <i class="bi bi-shop me-1"></i> Become Seller
    </a>

    <a href="<?= BASEURL ?>/auth/logout" class="btn btn-outline-dark rounded-pill px-4">
      <i class="bi bi-box-arrow-in-left me-1"></i> Log Out
    </a>

    <!-- Alamat User -->
    <div class="mt-4">
      <h4 class="fw-bold mb-3">My Addresses</h4>

      <?php foreach ($addresses as $address): ?>
        <div class="card p-3 mb-3">
          <strong class="fw-bold"><?= htmlspecialchars(
            $address["label"]
          ) ?></strong>
          <div class="text-muted mb-2">
            <?= htmlspecialchars($address["address_line_1"]) ?><br>
            <?= !empty($address["address_line_2"])
              ? htmlspecialchars($address["address_line_2"]) . "<br>"
              : "" ?>
            <?= htmlspecialchars($address["city"]) ?>, <?= htmlspecialchars(
  $address["country"]
) ?><br>
            Phone: <?= htmlspecialchars($address["phone_number"]) ?>
          </div>
          <div class="d-flex justify-content-end mt-2">
           <button class="btn btn-sm btn-outline-primary me-2" style="min-width: 80px;" data-bs-toggle="modal" data-bs-target="#editAddressModal<?= $address[
             "id"
           ] ?>">
  <i class="bi bi-pencil"></i> Edit
</button>

<form action="<?= BASEURL ?>/user/deleteAddress/<?= $address[
  "id"
] ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this address?')">
  <button type="submit" class="btn btn-sm btn-outline-danger" style="min-width: 80px;">
    <i class="bi bi-trash"></i> Delete
  </button>
</form>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Modal Edit Profil -->
<div class="modal fade userModal" id="editProfileModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" action="<?= BASEURL ?>/user/updateProfile" method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3 p-4">
        <div class="col-md-6">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars(
            $user["username"]
          ) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars(
            $user["full_name"]
          ) ?>" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars(
            $user["email"]
          ) ?>" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Profile Image</label>
          <input type="file" name="image" class="form-control">
          <?php if (!empty($user["image"])): ?>
            <img src="<?= htmlspecialchars(
              $user["image"]
            ) ?>" class="rounded-circle mt-2" width="80" height="80" style="object-fit: cover;">
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Tambah Alamat -->
<div class="modal fade userModal" id="addAddressModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" action="<?= BASEURL ?>/user/addAddress" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">Add New Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3 p-4">
        <div class="col-md-6">
          <label class="form-label">Label</label>
          <input type="text" class="form-control" name="label" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone Number</label>
          <input type="text" class="form-control" name="phone_number" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Address Line 1</label>
          <input type="text" class="form-control" name="address_line_1" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Address Line 2</label>
          <input type="text" class="form-control" name="address_line_2">
        </div>
        <div class="col-md-4">
          <label class="form-label">City</label>
          <input type="text" class="form-control" name="city" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Postal Code</label>
          <input type="text" class="form-control" name="postal_code" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Country</label>
          <input type="text" class="form-control" name="country" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Add Address</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
</form>
  </div>
</div>

<!-- Modal Edit Alamat (Per alamat) -->
<?php foreach ($addresses as $address): ?>
  <div class="modal fade userModal" id="editAddressModal<?= $address[
    "id"
  ] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" action="<?= BASEURL ?>/user/updateAddress/<?= $address[
  "id"
] ?>" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Edit Address - <?= htmlspecialchars(
            $address["label"]
          ) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3 p-4">
          <div class="col-md-6">
            <label class="form-label">Label</label>
            <input type="text" class="form-control" name="label" value="<?= htmlspecialchars(
              $address["label"]
            ) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone_number" value="<?= htmlspecialchars(
              $address["phone_number"]
            ) ?>" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Address Line 1</label>
            <input type="text" class="form-control" name="address_line_1" value="<?= htmlspecialchars(
              $address["address_line_1"]
            ) ?>" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Address Line 2</label>
            <input type="text" class="form-control" name="address_line_2" value="<?= htmlspecialchars(
              $address["address_line_2"]
            ) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" class="form-control" name="city" value="<?= htmlspecialchars(
              $address["city"]
            ) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Postal Code</label>
            <input type="text" class="form-control" name="postal_code" value="<?= htmlspecialchars(
              $address["postal_code"]
            ) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Country</label>
            <input type="text" class="form-control" name="country" value="<?= htmlspecialchars(
              $address["country"]
            ) ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary">Save Changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>
