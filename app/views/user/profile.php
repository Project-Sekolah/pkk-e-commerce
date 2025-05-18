<?php
$user = $data['user'];
$addresses = $data['addresses'];
?>

<div class="container profile-container">
  <h2 class="mb-4 text-center">My Profile</h2>

  <!-- Profile Display -->
  <div class="p-4 border rounded shadow-sm bg-white mb-5 d-flex justify-content-between align-items-center">
    <div>
      <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['username']) ?></h5>
      <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small><br>
      <span><?= htmlspecialchars($user['full_name']) ?></span>
    </div>
    <div>
      <img src="<?= !empty($user['image']) ? htmlspecialchars($user['image']) : BASEURL . '/assets/images/default-profile.png' ?>" 
           alt="Profile Image" width="64" height="64" class="rounded-circle shadow" style="object-fit: cover;">
    </div>
    <button class="btn btn-sm btn-outline-primary ms-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit</button>
  </div>

  <!-- Addresses -->
  <h4 class="mb-3">My Addresses</h4>
  <?php foreach ($addresses as $address): ?>
    <div class="p-4 mb-4 border rounded bg-white shadow-sm position-relative">
      <strong><?= htmlspecialchars($address['label']) ?></strong><br>
      <?= htmlspecialchars($address['address_line_1']) ?><br>
      <?= htmlspecialchars($address['address_line_2']) ?><br>
      <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['country']) ?><br>
      <small>Phone: <?= htmlspecialchars($address['phone_number']) ?></small><br>

      <div class="mt-3">
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editAddressModal<?= $address['id'] ?>">Edit</button>
        <form action="<?= BASEURL ?>/user/deleteAddress/<?= $address['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus alamat ini?')">
          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
      </div>
    </div>

    <!-- Edit Address Modal -->
    <div class="modal fade userModal" id="editAddressModal<?= $address['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" action="<?= BASEURL ?>/user/updateAddress/<?= $address['id'] ?>">
          <div class="modal-header">
            <h5 class="modal-title">Edit Address - <?= htmlspecialchars($address['label']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body row g-3">
            <div class="col-md-6">
              <label class="form-label">Label</label>
              <input type="text" class="form-control" name="label" value="<?= htmlspecialchars($address['label']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="text" class="form-control" name="phone_number" value="<?= htmlspecialchars($address['phone_number']) ?>" required>
            </div>
            <div class="col-md-12">
              <label class="form-label">Address Line 1</label>
              <input type="text" class="form-control" name="address_line_1" value="<?= htmlspecialchars($address['address_line_1']) ?>" required>
            </div>
            <div class="col-md-12">
              <label class="form-label">Address Line 2</label>
              <input type="text" class="form-control" name="address_line_2" value="<?= htmlspecialchars($address['address_line_2']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">City</label>
              <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($address['city']) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Postal Code</label>
              <input type="text" class="form-control" name="postal_code" value="<?= htmlspecialchars($address['postal_code']) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Country</label>
              <input type="text" class="form-control" name="country" value="<?= htmlspecialchars($address['country']) ?>" required>
            </div>
            <div class="col-12 form-check">
              <input type="checkbox" class="form-check-input" name="is_default" value="1" <?= $address['is_default'] ? 'checked' : '' ?>>
              <label class="form-check-label">Set as default address</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Update Address</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- Add New Address Button -->
  <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">Add New Address</button>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade userModal" id="editProfileModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="<?= BASEURL ?>/user/updateProfile" method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Profile Image</label>
          <input type="file" name="image" class="form-control">
          <?php if (!empty($user['image'])): ?>
            <img src="<?= htmlspecialchars($user['image']) ?>" class="rounded-circle mt-2" width="80" height="80" style="object-fit: cover;">
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

<!-- Add Address Modal -->
<div class="modal fade userModal" id="addAddressModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" action="<?= BASEURL ?>/user/addAddress" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">Add New Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
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
        <div class="col-12 form-check">
          <input type="checkbox" class="form-check-input" name="is_default" value="1">
          <label class="form-check-label">Set as default address</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success">Save Address</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
