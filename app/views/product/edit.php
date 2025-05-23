<?php
// product/edit.php
$product = $data["product"];
$categories = $data["categories"];
$productImages = $data["product_images"] ?? [];
$productDiscounts =
  isset($data["productDiscounts"]) && is_array($data["productDiscounts"])
    ? array_column($data["productDiscounts"], "id")
    : [];
$discounts = $data["discounts"];
$genders = [
  "pria" => "Pria",
  "wanita" => "Wanita",
  "all" => "Pria & Wanita",
];
?>


<div class="container my-5">
  <h4 id="editProduct" class="mb-4">Edit Produk</h4>
  <form action="<?= BASEURL ?>/product/update/<?= $data["product"][
  "id"
] ?>" method="POST" enctype="multipart/form-data">
    <!-- Form fields for product details -->
    <div class="mb-3">
      <label for="title" class="form-label">Nama Produk</label>
      <input type="text" class="form-control" id="title" name="title" value="<?= $data[
        "product"
      ]["title"] ?>" required>
    </div>
    <!-- Other form fields -->
   <div class="mb-3">
      <label for="title" class="form-label">Nama Produk</label>
      <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars(
        $product["title"]
      ) ?>" required>
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Deskripsi</label>
      <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars(
        $product["description"]
      ) ?></textarea>
    </div>

    <div class="mb-3">
      <label for="price" class="form-label">Harga</label>
      <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars(
        $product["price"]
      ) ?>" step="0.01" required>
    </div>

    <div class="mb-3">
      <label for="stock" class="form-label">Stok</label>
      <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars(
        $product["stock"]
      ) ?>" required>
    </div>

    <div class="mb-3">
      <label for="category_id" class="form-label">Kategori</label>
      <select class="form-select" id="category_id" name="category_id" required>
        <option value="">Pilih Kategori</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= $category["id"] ?>" <?= $product["category_id"] ==
$category["id"]
  ? "selected"
  : "" ?>>
            <?= htmlspecialchars($category["name"]) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="gender" class="form-label">Gender</label>
      <select class="form-select" id="gender" name="gender" required>
        <?php foreach ($genders as $value => $label): ?>
          <option value="<?= $value ?>" <?= $product["gender"] == $value
  ? "selected"
  : "" ?>>
            <?= $label ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
  <label for="discounts" class="form-label">Diskon</label>
  <select class="form-select" id="discounts" name="discounts[]" multiple>
    <?php foreach ($discounts as $discount): ?>
      <option value="<?= $discount["id"] ?>" <?= in_array(
        $discount["id"],
        $productDiscounts
      ) ? "selected" : "" ?>>
        <?= htmlspecialchars($discount["name"]) ?> (<?= $discount[
       "percentage"
     ] ?>%)
      </option>
    <?php endforeach; ?>
  </select>
</div>

    
    <!-- Existing Product Images -->
<div class="mb-3">
  <label class="form-label">Gambar Produk yang Sudah Ada</label>
  <div class="row">
    <?php if (!empty($data["product_images"])): ?>
      <?php foreach ($data["product_images"] as $image): ?>
        <div class="col-md-3">
            <img src="<?= htmlspecialchars(
              $image["image_url"]
            ) ?>" class="img-fluid mb-2" alt="product">
            <a href="<?= BASEURL ?>/product/deleteImage/<?= $image[
  "id"
] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?')">Hapus</a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-md-12">
          <p>Tidak ada gambar produk yang tersedia.</p>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
            Tambah Gambar
          </button>
      </div>
    <?php endif; ?>
  </div>
</div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
  </form>
</div>


<!-- Modal -->
<div class="modal fade" id="addImageModal" tabindex="-1" aria-labelledby="addImageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addImageModalLabel">Tambah Gambar Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="<?= BASEURL ?>/product/addImage/<?= $data["product"][
  "id"
] ?>" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="new_image" class="form-label">Unggah Gambar Baru</label>
            <input type="file" class="form-control" id="new_image" name="new_image" required>
          </div>
          <button type="submit" class="btn btn-primary">Tambah</button>
        </form>
      </div>
    </div>
  </div>
</div>






