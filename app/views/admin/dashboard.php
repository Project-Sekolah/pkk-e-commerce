

<div class="main-content d-flex flex-column">
    <div class="d-block d-lg-none w-100 mb-3">
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <header class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h2 class="mb-2 mb-md-0">Beranda</h2>
        <div class="d-flex flex-column flex-md-row align-items-md-center">
            <label for="date-start" class="me-2 text-md-end">Periode:</label>
            <input type="date" id="date-start" class="form-control me-2 mb-2 mb-md-0" value="<?php echo date('Y-m-d'); ?>">
            <span class="d-none d-md-block">-</span>
            <input type="date" id="date-end" class="form-control ms-md-2" value="<?php echo date('Y-m-d'); ?>">
        </div>
    </header>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <div class="card-icon bg-success">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase mb-0">Total User</h6>
                        <h4 class="fw-bold mt-1"><?php echo htmlspecialchars($data['total_users']); ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <div class="card-icon bg-info">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase mb-0">Total Produk</h6>
                        <h4 class="fw-bold mt-1"><?php echo htmlspecialchars($data['total_products']); ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <div class="card-icon bg-primary">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase mb-0">Total Transaksi</h6>
                        <h4 class="fw-bold mt-1"><?php echo htmlspecialchars($data['total_orders']); ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-3">
                <div class="d-flex align-items-center">
                    <div class="card-icon bg-warning">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase mb-0">Pendapatan Bersih</h6>
                        <h4 class="fw-bold mt-1">Rp <?php echo number_format($data['total_revenue'], 0, ',', '.'); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-4 h-100">
                <h5 class="mb-3">Grafik Penjualan Bulanan</h5>
                <div style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-4 h-100">
                <h5 class="mb-3">Jumlah Pengguna per Tipe</h5>
                <div style="height: 300px;">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-4">
                <h5 class="mb-3">5 Produk Terlaris</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="top-selling-products-table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nama Produk</th>
                                <th scope="col">Nama Penjual</th>
                                <th scope="col">Penjualan</th>
                                <th scope="col">Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($data['top_products'] as $i => $product): ?>
                            <tr>
                                <th scope="row"><?php echo $i + 1; ?></th>
                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['sold']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

