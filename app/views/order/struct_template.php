<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Struct</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .info, .items { margin-bottom: 10px; }
        .items th, .items td { border: 1px solid #ccc; padding: 6px; }
        .items { border-collapse: collapse; width: 100%; }
        .total { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Order Struct</h2>
        <p>Order ID: <?= htmlspecialchars($order['id']) ?></p>
        <p>Date: <?= htmlspecialchars($order['created_at']) ?></p>
    </div>
    <div class="info">
        <strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?><br>
        <strong>Address:</strong> <?= htmlspecialchars($order['customer_address']) ?><br>
    </div>
    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($order['items'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['price'], 2) ?></td>
                <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="total">Total: Rp <?= number_format($order['total'], 2) ?></p>
</body>
</html>
