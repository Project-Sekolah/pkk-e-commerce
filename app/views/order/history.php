<?php
// Ambil data orders dari $data
$orders = $data['orders'] ?? [];
?>
<div class="container py-4">
    <h2>Riwayat Order</h2>
    <?php Flasher::flash(); ?>
    <?php if (!empty($orders)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Order</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Jumlah Item</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']); ?></td>
                        <td><?= htmlspecialchars($order['created_at']); ?></td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($order['status']); ?></span></td>
                        <td>Rp <?= number_format($order['total'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($order['item_count']); ?></td>
                        <td>
                            <a href="<?= BASEURL . '/order/detail/' . $order['id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">Belum ada order.</div>
    <?php endif; ?>
</div>
