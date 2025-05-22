<?php
// app/views/order/history.php
$this->view("templates/header", $data);
?>

<div class="container mt-5">
    <h1 class="mb-4"><?= htmlspecialchars($data['judul']); ?></h1>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['orders'] as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']); ?></td>
                            <td>$<?= number_format($order['total'], 2); ?></td>
                            <td><?= htmlspecialchars($order['status']); ?></td>
                            <td><?= htmlspecialchars($order['created_at']); ?></td>
                            <td><?= htmlspecialchars($order['item_count']); ?></td>
                            <td>
                                <a href="<?= BASEURL ?>/order/detail/<?= htmlspecialchars($order['id']); ?>" class="btn btn-primary btn-sm">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                        <li class="page-item <?= $i == $data['currentPage'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?= BASEURL ?>/order/history/<?= $i; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php
$this->view("templates/footer");
?>
