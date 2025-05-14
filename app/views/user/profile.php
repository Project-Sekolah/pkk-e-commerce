<div class="container profile-container">
  <h2 class="mb-4 text-center">My Profile</h2>

  <!-- Profile Section -->
  <div id="profile-display">
    <div class="p-4 border rounded shadow-sm bg-white mb-5">
      <div class="d-flex justify-content-between">
        <div>
          <strong>Username:</strong> <?= htmlspecialchars($data['user']['username']) ?><br>
          <strong>Full Name:</strong> <?= htmlspecialchars($data['user']['full_name']) ?><br>
          <strong>Email:</strong> <?= htmlspecialchars($data['user']['email']) ?><br>
        </div>
        <div>
          <?php if (!empty($data['user']['image'])): ?>
            <img src="<?= htmlspecialchars($data['user']['image']) ?>" alt="Profile Image" width="100" class="rounded shadow">
          <?php else: ?>
            <img src="default-profile.png" alt="Profile Image" width="100" class="rounded shadow">
          <?php endif; ?>
        </div>
      </div>

      <!-- Edit Button -->
      <button class="btn btn-primary mt-3" id="editProfileBtn">Edit Profile</button>
    </div>
  </div>

  <!-- Profile Edit Form -->
  <div id="profile-edit" class="d-none">
    <form action="<?= BASEURL ?>/user/updateProfile" method="post" enctype="multipart/form-data" class="p-4 border rounded shadow-sm bg-white mb-5">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($data['user']['username']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($data['user']['full_name']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($data['user']['email']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="image" class="form-label">Profile Image</label>
        <input class="form-control" type="file" id="image" name="image">
        <?php if (!empty($data['user']['image'])): ?>
          <img src="<?= htmlspecialchars($data['user']['image']) ?>" alt="Profile Picture" width="100" class="mt-3 rounded shadow">
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary">Update Profile</button>
      <button type="button" class="btn btn-secondary" id="cancelProfileBtn">Cancel</button>
    </form>
  </div>


  <hr class="my-5">

  <!-- Address Section -->
  <h3 class="mb-4">My Addresses</h3>
  <div id="address-list">
    <?php foreach ($data['addresses'] as $address): ?>
      <div class="p-4 mb-4 border rounded bg-white shadow-sm">
        <strong><?= htmlspecialchars($address['label']) ?></strong><br>
        <?= htmlspecialchars($address['address_line_1']) ?><br>
        <?= htmlspecialchars($address['address_line_2']) ?><br>
        <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['country']) ?><br>
        <small>Phone: <?= htmlspecialchars($address['phone_number']) ?></small>

        <button class="btn btn-warning mt-3 editAddressBtn" data-address-id="<?= $address['id'] ?>">Edit Address</button>
        <form action="<?= BASEURL ?>/user/deleteAddress/<?= htmlspecialchars($address['id']) ?>" method="POST" onsubmit="return confirm('Hapus alamat ini?')" class="mt-2">
          <button type="submit" class="btn btn-danger">Delete Address</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Add Address Button -->
  <button class="btn btn-success mt-3" id="addAddressBtn">Add New Address</button>

  <!-- New Address Form -->
  <div id="add-address-form" class="d-none mt-4 p-4 border rounded bg-white shadow-sm">
    <h4>Add New Address</h4>
    <form action="<?= BASEURL ?>/user/addAddress" method="POST">
      <div class="mb-3">
        <label for="label" class="form-label">Label</label>
        <input type="text" class="form-control" name="label" placeholder="Home, Office, etc." required>
      </div>

      <div class="mb-3">
        <label for="address_line_1" class="form-label">Address Line 1</label>
        <input type="text" class="form-control" name="address_line_1" required>
      </div>

      <div class="mb-3">
        <label for="address_line_2" class="form-label">Address Line 2</label>
        <input type="text" class="form-control" name="address_line_2" required>
      </div>

      <div class="mb-3">
        <label for="city" class="form-label">City</label>
        <input type="text" class="form-control" name="city" required>
      </div>

      <div class="mb-3">
        <label for="postal_code" class="form-label">Postal Code</label>
        <input type="text" class="form-control" name="postal_code" required>
      </div>

      <div class="mb-3">
        <label for="country" class="form-label">Country</label>
        <input type="text" class="form-control" name="country" required>
      </div>

      <div class="mb-3">
        <label for="phone_number" class="form-label">Phone Number</label>
        <input type="text" class="form-control" name="phone_number" required>
      </div>

      <div class="mb-3">
        <label for="is_default" class="form-check-label">Set as Default Address</label>
        <input type="checkbox" class="form-check-input" name="is_default" value="1">
      </div>

      <button type="submit" class="btn btn-success">Save Address</button>
      <button type="button" class="btn btn-secondary" id="cancelAddAddressBtn">Cancel</button>
    </form>
  </div>
</div>

<script>
  // Toggle profile edit form
  document.getElementById('editProfileBtn').addEventListener('click', function() {
    document.getElementById('profile-display').classList.add('d-none');
    document.getElementById('profile-edit').classList.remove('d-none');
  });

  document.getElementById('cancelProfileBtn').addEventListener('click', function() {
    document.getElementById('profile-display').classList.remove('d-none');
    document.getElementById('profile-edit').classList.add('d-none');
  });

  // Toggle address add form
  document.getElementById('addAddressBtn').addEventListener('click', function() {
    document.getElementById('add-address-form').classList.remove('d-none');
  });

  document.getElementById('cancelAddAddressBtn').addEventListener('click', function() {
    document.getElementById('add-address-form').classList.add('d-none');
  });
</script>
