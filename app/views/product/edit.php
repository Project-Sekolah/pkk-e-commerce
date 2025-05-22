<?php
// product/edit.php
?>

<div class="container my-5">
  <h4 id="editProduct" class="mb-4">Edit Produk</h4>
  <form action="<?= BASEURL ?>/product/update/<?= $data["product"]["id"] ?>" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">Nama Produk</label>
      <input type="text" class="form-control" id="title" name="title" value="<?= $data["product"]["title"] ?>" required>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Deskripsi</label>
      <textarea class="form-control" id="description" name="description" rows="3" required><?= $data["product"]["description"] ?></textarea>
    </div>
    <div class="mb-3">
      <label for="price" class="form-label">Harga</label>
      <input type="number" class="form-control" id="price" name="price" value="<?= $data["product"]["price"] ?>" step="0.01" required>
    </div>
    <div class="mb-3">
      <label for="stock" class="form-label">Stok</label>
      <input type="number" class="form-control" id="stock" name="stock" value="<?= $data["product"]["stock"] ?>" required>
    </div>
    <div class="mb-3">
      <label for="category_id" class="form-label">Kategori</label>
      <select class="form-select" id="category_id" name="category_id" required>
        <option value="">Pilih Kategori</option>
        <?php foreach ($data["categories"] as $category): ?>
          <option value="<?= $category["id"] ?>" <?= $data["product"]["category_id"] == $category["id"] ? "selected" : "" ?>>
            <?= $category["name"] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="gender" class="form-label">Gender</label>
      <select class="form-select" id="gender" name="gender" required>
        <option value="pria" <?= $data["product"]["gender"] == "pria" ? "selected" : "" ?>>Pria</option>
        <option value="wanita" <?= $data["product"]["gender"] == "wanita" ? "selected" : "" ?>>Wanita</option>
        <option value="all" <?= $data["product"]["gender"] == "all" ? "selected" : "" ?>>Pria & Wanita</option>
      </select>
    </div>
    <div class="mb-3">
  <label class="form-label">Gambar Produk yang Sudah Ada</label>
  <div class="row">
    <?php foreach ($data["product_images"] as $image): ?>
      <div class="col-md-3">
        <img src="<?= htmlspecialchars($image["image_url"]) ?>" class="img-fluid mb-2" alt="product">
        <a href="<?= BASEURL ?>/product/deleteImage/<?= $image["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?')">Hapus</a>
      </div>
    <?php endforeach; ?>
  </div>
</div>

    <div class="mb-3">
      <label class="form-label">Gambar Produk yang Sudah Ada</label>
      <div class="row">
        <?php foreach ($data["product_images"] as $image): ?>
          <div class="col-md-3">
            <img src="<?= htmlspecialchars($image["image_url"]) ?>" class="img-fluid mb-2" alt="product">
            <a href="<?= BASEURL ?>/product/deleteImage/<?= $image["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?')">Hapus</a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
  </form>
</div>
