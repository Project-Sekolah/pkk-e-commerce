<?php
$order = $data['order'] ?? [];
$order_items = $data['order_items'] ?? [];
?>
<div class="container py-4">
    <h2>Detail Order</h2>
    <?php Flasher::flash(); ?>
    <?php if (!empty($order)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Order #<?= htmlspecialchars($order['id']); ?></h5>
                <p>Status: <span class="badge bg-info text-dark"><?= htmlspecialchars($order['status']); ?></span></p>
                <p>Alamat Pengiriman: <?= htmlspecialchars($order['customer_address']); ?></p>
                <p>Tanggal: <?= htmlspecialchars($order['created_at']); ?></p>
                <p>Total: <strong>Rp <?= number_format($order['total'], 0, ',', '.'); ?></strong></p>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Produk yang Dibeli</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']); ?></td>
                                <td><?= $item['quantity']; ?></td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Order tidak ditemukan.</div>
    <?php endif; ?>
</div>
