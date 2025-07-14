<?php
// product/add.php
?>

<div class="container my-5">
  <h4 id="addProduct" class="mb-4">Tambah Produk</h4>
  <form action="<?= BASEURL ?>/product/store" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">Nama Produk</label>
      <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Deskripsi</label>
      <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
    </div>
    <div class="mb-3">
      <label for="price" class="form-label">Harga</label>
      <input type="number" class="form-control" id="price" name="price" step="0.01" required>
    </div>
    <div class="mb-3">
      <label for="stock" class="form-label">Stok</label>
      <input type="number" class="form-control" id="stock" name="stock" required>
    </div>
    <div class="mb-3">
      <label for="category_id" class="form-label">Kategori</label>
      <select class="form-select" id="category_id" name="category_id" required>
        <option value="">Pilih Kategori</option>
        <?php foreach ($data["categories"] as $category): ?>
          <option value="<?= $category["id"] ?>"><?= $category["name"] ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="gender" class="form-label">Gender</label>
      <select class="form-select" id="gender" name="gender" required>
        <option value="pria">Pria</option>
        <option value="wanita">Wanita</option>
        <option value="all">Pria & Wanita</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="images" class="form-label">Gambar Produk</label>
      <input type="file" class="form-control" id="images" name="images[]" multiple>
      <small class="text-muted">Anda dapat mengunggah beberapa gambar.</small>
    </div>
    <button type="submit" class="btn btn-primary">Tambah Produk</button>
  </form>
</div>
