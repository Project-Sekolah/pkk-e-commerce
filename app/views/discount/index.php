<div class="container my-5">
    <h4 id="discount" class="mb-4">Daftar Diskon</h4>
    <a href="<?= BASEURL ?>/discount/add" class="btn btn-primary mb-3">Tambah Diskon</a>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="discountGrid">
        <?php foreach ($data["discounts"] as $discount): ?>
            <div class="col discount-item">
                <div class="card border card-3d interactive h-100">
                    <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($discount["name"]) ?></h6>
                        <p class="card-text">Persentase: <?= $discount["percentage"] ?>%</p>
                        <p class="card-text">Tanggal Mulai: <?= date("d/m/Y", strtotime($discount["start_date"])) ?></p>
                        <p class="card-text">Tanggal Berakhir: <?= date("d/m/Y", strtotime($discount["end_date"])) ?></p>
                        <a href="<?= BASEURL ?>/discount/edit/<?= $discount["id"] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?= BASEURL ?>/discount/delete/<?= $discount["id"] ?>" class="btn btn-sm btn-danger btn-hapus-diskon">Hapus</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
