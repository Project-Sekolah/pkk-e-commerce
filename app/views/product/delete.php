<?php
// product/delete.php
?>

<div class="container my-5">
  <h4 id="deleteProduct" class="mb-4">Hapus Produk</h4>
  <p>Apakah Anda yakin ingin menghapus produk ini?</p>
  <form action="<?= BASEURL ?>/product/delete/<?= $data["product"]["id"] ?>" method="POST">
    <button type="submit" class="btn btn-danger">Hapus</button>
    <a href="<?= BASEURL ?>/product" class="btn btn-secondary">Batal</a>
  </form>
</div>
