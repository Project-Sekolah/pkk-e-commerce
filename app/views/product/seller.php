<?php
// product/seller.php
?>

<div class="container my-5">
  <h4 id="produk" class="mb-4">Daftar Produk Saya</h4>
  <a href="<?= BASEURL ?>/product/add" class="btn btn-primary mb-3">Tambah Produk</a>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="productGrid">
    <?php foreach ($data["products"] as $product): ?>
      <div class="col product-item">
        <div class="card border card-3d interactive h-100">
          <img src="<?= htmlspecialchars($product["images"]) ?>" class="card-img-top product-img" alt="product">
          <div class="card-body text-center" style="background-color: #847e7b;">
            <h6 class="card-title"><?= htmlspecialchars($product["title"]) ?></h6>
            <p class="card-text">$<?= number_format($product["price"], 2) ?></p>
            <a href="<?= BASEURL ?>/product/edit/<?= $product["id"] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="<?= BASEURL ?>/product/delete/<?= $product["id"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <nav class="mt-5 d-flex justify-content-center">
    <ul class="pagination">
      <li class="page-item <?= $data["currentPage"] > 1 ? "" : "disabled" ?>">
        <a class="page-link" href="<?= BASEURL ?>/product/seller/<?= $data["currentPage"] - 1 ?>">Previous</a>
      </li>

      <?php for ($i = 1; $i <= $data["totalPages"]; $i++): ?>
        <li class="page-item <?= $i == $data["currentPage"] ? "active" : "" ?>">
          <a class="page-link" href="<?= BASEURL ?>/product/seller/<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= $data["currentPage"] < $data["totalPages"] ? "" : "disabled" ?>">
        <a class="page-link" href="<?= BASEURL ?>/product/seller/<?= $data["currentPage"] + 1 ?>">Next</a>
      </li>
    </ul>
  </nav>
</div>
