<?php
// app/views/order/detail.php
$this->view("templates/header", $data);
?>

<div class="container mt-5">
    <h1 class="mb-4"><?= htmlspecialchars($data['judul']); ?></h1>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Order ID: <?= htmlspecialchars($data['order']['id']); ?></h5>
                    <p class="card-text">Total: $<?= number_format($data['order']['total'], 2); ?></p>
                    <p class="card-text">Status: <?= htmlspecialchars($data['order']['status']); ?></p>
                    <p class="card-text">Date: <?= htmlspecialchars($data['order']['created_at']); ?></p>
                </div>
            </div>

            <h2 class="mt-4">Order Items</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['order']['items'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']); ?></td>
                            <td><?= htmlspecialchars($item['quantity']); ?></td>
                            <td>$<?= number_format($item['price'], 2); ?></td>
                            <td>$<?= number_format($item['quantity'] * $item['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$this->view("templates/footer");
?>
