<?php
class Cart_model {
    private $cartTable = "carts";
    private $itemTable = "cart_items";
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

        /**
     * Hapus semua item keranjang berdasarkan product_id
     */
    public function deleteItemsByProductId($productId)
    {
    // Hapus item keranjang jika produk sudah tidak aktif atau sudah dihapus (soft delete)
    $this->db->query("DELETE FROM cart_items WHERE product_id = :product_id OR product_id IN (SELECT id FROM products WHERE is_active = 0 OR deleted_at IS NOT NULL)");
    $this->db->bind(":product_id", $productId);
    return $this->db->execute();
    }

    public function getOrCreateCart($userId)
    {
        $this->db->query(
            "SELECT * FROM $this->cartTable WHERE user_id = :user_id AND deleted_at IS NULL"
        );
        $this->db->bind(":user_id", $userId);
        $cart = $this->db->single();

        if ($cart) {
            return $cart;
        }

        $this->db->query(
            "INSERT INTO $this->cartTable (id, user_id) VALUES (UUID(), :user_id)"
        );
        $this->db->bind(":user_id", $userId);
        $this->db->execute();

        $this->db->query(
            "SELECT * FROM $this->cartTable WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1"
        );
        $this->db->bind(":user_id", $userId);
        return $this->db->single();
    }

    public function getCartItems($cartId) {
    $this->db->query("SELECT 
        ci.id as item_id,  
        ci.product_id,
        ci.quantity,
        p.title, 
        p.price, 
        pi.image_url as image,
        d.name as discount_name,
        d.percentage as discount_percentage
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id
        LEFT JOIN product_discounts pd ON p.id = pd.product_id
        LEFT JOIN discounts d ON pd.discount_id = d.id
        WHERE ci.cart_id = :cart_id
        GROUP BY ci.id");
    $this->db->bind(":cart_id", $cartId);
    return $this->db->resultSet();
}



    public function addItem($cartId, $productId, $quantity)
    {
        // 1. Validasi produk
        $this->db->query("SELECT stock FROM products WHERE id = :product_id");
        $this->db->bind(":product_id", $productId);
        $product = $this->db->single();
        if (!$product) {
            return ["success" => false, "message" => "Produk tidak ditemukan."];
        }
        if ($product['stock'] < $quantity) {
            return ["success" => false, "message" => "Stok produk tidak cukup."];
        }

        // 2. Cek apakah item sudah ada di cart
        $this->db->query("SELECT * FROM $this->itemTable WHERE cart_id = :cart_id AND product_id = :product_id");
        $this->db->bind(":cart_id", $cartId);
        $this->db->bind(":product_id", $productId);
        $item = $this->db->single();

        if ($item) {
            $newQty = $item["quantity"] + $quantity;
            if ($product['stock'] < ($newQty - $item['quantity'])) {
                return ["success" => false, "message" => "Stok produk tidak cukup."];
            }
            $this->db->query("UPDATE $this->itemTable SET quantity = :quantity WHERE id = :id");
            $this->db->bind(":quantity", $newQty);
            $this->db->bind(":id", $item["id"]);
            $this->db->execute();
        } else {
            $this->db->query("INSERT INTO $this->itemTable (id, cart_id, product_id, quantity) VALUES (UUID(), :cart_id, :product_id, :quantity)");
            $this->db->bind(":cart_id", $cartId);
            $this->db->bind(":product_id", $productId);
            $this->db->bind(":quantity", $quantity);
            $this->db->execute();
        }

        // 3. Kurangi stok produk
        $this->db->query("UPDATE products SET stock = stock - :quantity WHERE id = :product_id AND stock >= :quantity");
        $this->db->bind(":quantity", $quantity);
        $this->db->bind(":product_id", $productId);
        $this->db->execute();

        return ["success" => true, "message" => "Produk berhasil ditambahkan ke keranjang."];
    }

    public function clearCart($cartId)
    {
        $this->db->query("DELETE FROM $this->itemTable WHERE cart_id = :cart_id");
        $this->db->bind(":cart_id", $cartId);
        return $this->db->execute();
    }

    public function increaseItemQuantity($itemId, $amount = 1)
    {
        $item = $this->getCartItemById($itemId);
        if (!$item) return ["success" => false, "message" => "Item tidak ditemukan."];
        // Cek stok produk
        $this->db->query("SELECT stock FROM products WHERE id = :product_id");
        $this->db->bind(":product_id", $item['product_id']);
        $product = $this->db->single();
        if (!$product || $product['stock'] < $amount) {
            return ["success" => false, "message" => "Stok produk tidak cukup."];
        }
        $this->db->query("UPDATE $this->itemTable SET quantity = quantity + :amount WHERE id = :id");
        $this->db->bind(":amount", $amount);
        $this->db->bind(":id", $itemId);
        $this->db->execute();
        // Kurangi stok produk
        $this->db->query("UPDATE products SET stock = stock - :amount WHERE id = :product_id AND stock >= :amount");
        $this->db->bind(":amount", $amount);
        $this->db->bind(":product_id", $item['product_id']);
        $this->db->execute();
        return ["success" => true, "message" => "Jumlah item berhasil ditambah."];
    }

    public function decreaseItemQuantity($itemId, $amount = 1)
    {
        $item = $this->getCartItemById($itemId);
        if (!$item) {
            return ["success" => false, "message" => "Item tidak ditemukan."];
        }
        $newQty = $item["quantity"] - $amount;
        // Kembalikan stok produk
        $this->db->query("UPDATE products SET stock = stock + :amount WHERE id = :product_id");
        $this->db->bind(":amount", $amount);
        $this->db->bind(":product_id", $item['product_id']);
        $this->db->execute();
        if ($newQty > 0) {
            $this->db->query("UPDATE $this->itemTable SET quantity = :quantity WHERE id = :id");
            $this->db->bind(":quantity", $newQty);
            $this->db->bind(":id", $itemId);
            $this->db->execute();
            return ["success" => true, "message" => "Jumlah item dikurangi."];
        } else {
            $this->deleteCartItemById($itemId);
            return ["success" => true, "message" => "Item dihapus dari keranjang."];
        }
    }

    public function deleteCartItemById($itemId)
    {
        $item = $this->getCartItemById($itemId);
        if ($item) {
            // Kembalikan stok produk
            $this->db->query("UPDATE products SET stock = stock + :quantity WHERE id = :product_id");
            $this->db->bind(":quantity", $item['quantity']);
            $this->db->bind(":product_id", $item['product_id']);
            $this->db->execute();
        }
        $this->db->query("DELETE FROM $this->itemTable WHERE id = :id");
        $this->db->bind(":id", $itemId);
        $this->db->execute();
        return ["success" => true, "message" => "Item dihapus dari keranjang."];
    }

    public function getCartItemById($itemId)
    {
        $this->db->query("SELECT * FROM $this->itemTable WHERE id = :id");
        $this->db->bind(":id", $itemId);
        return $this->db->single();
    }
    
    
    public function validateDiscount($discountName, $cartItems)
{
    // Validasi kode diskon
    $this->db->query("SELECT * FROM discounts 
                      WHERE name = :name 
                      AND is_active = 1 
                      AND start_date <= NOW() 
                      AND end_date >= NOW()");
    $this->db->bind("name", $discountName);
    $discount = $this->db->single();

    if (!$discount) {
        return [
            "success" => false,
            "message" => "Invalid or expired discount name",
        ];
    }

    // Ambil semua produk yang berlaku untuk diskon ini
    $this->db->query("SELECT pd.product_id 
                      FROM product_discounts pd 
                      JOIN discounts d ON pd.discount_id = d.id 
                      WHERE d.name = :name 
                      AND pd.is_active = 1 
                      AND pd.start_date <= NOW() 
                      AND pd.end_date >= NOW()");
    $this->db->bind("name", $discountName);
    $validProducts = $this->db->resultSet();

    $validProductIds = array_column($validProducts, "product_id");

    // Periksa apakah produk di keranjang termasuk produk yang berlaku
    $cartProductIds = array_column($cartItems, "product_id");
    $matchingProducts = array_intersect($cartProductIds, $validProductIds);

    if (empty($matchingProducts)) {
        return [
            "success" => false,
            "message" => "No products in your cart match this discount.",
        ];
    }

    return [
        "success" => true,
        "discount_percentage" => $discount["percentage"],
        "applicable_products" => array_values($matchingProducts),
    ];
}




}
