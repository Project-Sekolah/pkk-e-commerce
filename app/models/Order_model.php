<?php

class Order_model
{
    // ...existing code...

    // Total transaksi
    public function getTotalOrders()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Total pendapatan
    public function getTotalRevenue()
    {
        $stmt = $this->db->prepare("SELECT SUM(total) as revenue FROM orders WHERE status = 'completed'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['revenue'] ?? 0;
    }

    // Grafik penjualan bulanan (jumlah transaksi per bulan)
    public function getMonthlySalesChart($year = null)
    {
        if (!$year) $year = date('Y');
        $stmt = $this->db->prepare("SELECT MONTH(created_at) as month, COUNT(*) as total FROM orders WHERE YEAR(created_at) = :year GROUP BY MONTH(created_at) ORDER BY month ASC");
        $stmt->execute(['year' => $year]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    private $table = "orders";
    private $db;

    public function __construct()
    {
        $host = DB_HOST;
        $port = DB_PORT;
        $dbname = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;

        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];
            $this->db = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    public function createOrderFromCart(string $userId): ?string
    {
        try {
            $this->db->beginTransaction();

            $cartItems = $this->getCartWithItems($userId);
            if (empty($cartItems)) {
                throw new Exception("Cart kosong atau tidak ditemukan");
            }

            $this->validateProductAvailability($cartItems);
            $total = $this->calculateOrderTotal($cartItems);
            $orderId = $this->createOrderRecord($userId, $total);
            $this->createOrderItems($orderId, $cartItems);
            $this->clearCart($cartItems[0]['cart_id']);

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return null;
        }
    }

    public function getOrderById(string $userId, string $orderId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.id, 
                o.total, 
                o.status, 
                o.created_at,
                o.user_id
            FROM orders o
            WHERE o.id = :order_id AND o.user_id = :user_id
        ");
        $stmt->execute(["order_id" => $orderId, "user_id" => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus(string $orderId, string $status): bool
    {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET status = :status, updated_at = NOW()
            WHERE id = :order_id
        ");
        return $stmt->execute(["status" => $status, "order_id" => $orderId]);
    }

    public function getOrderHistory(string $userId, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.id, 
                o.total, 
                o.status, 
                o.created_at,
                COUNT(oi.id) AS item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = :user_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->execute([
            "user_id" => $userId,
            "limit" => (int)$limit,
            "offset" => (int)$offset
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCartWithItems(string $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.id AS cart_id,
                ci.id AS item_id,
                ci.product_id,
                ci.quantity,
                p.price,
                p.stock
            FROM carts c
            JOIN cart_items ci ON c.id = ci.cart_id
            JOIN products p ON ci.product_id = p.id
            WHERE c.user_id = :user_id AND c.deleted_at IS NULL
        ");
        $stmt->execute(["user_id" => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function validateProductAvailability(array $cartItems): void
    {
        foreach ($cartItems as $item) {
            if ($item['quantity'] > $item['stock']) {
                throw new Exception("Produk " . $item['product_id'] . " stok tidak mencukupi");
            }
        }
    }

    private function calculateOrderTotal(array $cartItems): float
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item["quantity"] * $item["price"];
        }
        return round($total, 2);
    }

    private function createOrderRecord(string $userId, float $total): string
    {
        $stmt = $this->db->prepare("
            INSERT INTO orders (id, user_id, total, status, created_at)
            VALUES (UUID(), :user_id, :total, 'pending', NOW())
        ");
        $stmt->execute(["user_id" => $userId, "total" => $total]);
        return $this->db->lastInsertId();
    }

    private function createOrderItems(string $orderId, array $cartItems): void
    {
        $sql = "
            INSERT INTO order_items (id, order_id, product_id, quantity, price)
            VALUES " . rtrim(str_repeat('(:id, :order_id, :product_id, :quantity, :price),', count($cartItems)), ',');
        
        $stmt = $this->db->prepare($sql);
        foreach ($cartItems as $index => $item) {
            $stmt->bindValue(":id_$index", uniqid());
            $stmt->bindValue(":order_id_$index", $orderId);
            $stmt->bindValue(":product_id_$index", $item['product_id']);
            $stmt->bindValue(":quantity_$index", $item['quantity']);
            $stmt->bindValue(":price_$index", $item['price']);
        }
        $stmt->execute();
    }

    private function clearCart(string $cartId): void
    {
        $this->db->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id")
            ->execute(["cart_id" => $cartId]);
    }

    public function getOrderItems(string $orderId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                oi.id, 
                oi.product_id, 
                oi.quantity, 
                oi.price, 
                p.title AS name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute(["order_id" => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
