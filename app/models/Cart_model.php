<?php
class Cart_model
{
    private $cartTable = "carts";
    private $itemTable = "cart_items";
    private $db;

    public function __construct()
    {
        $this->db = new Database();
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

    public function getCartItems($cartId)
    {
        $this->db->query("SELECT 
            ci.id as item_id,  
            ci.product_id,
            ci.quantity,
            p.title, 
            p.price, 
            pi.image_url as image 
            FROM $this->itemTable ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_images pi ON p.id = pi.product_id
            WHERE ci.cart_id = :cart_id
            GROUP BY ci.id"
        );
        $this->db->bind(":cart_id", $cartId);
        return $this->db->resultSet();
    }

    public function addItem($cartId, $productId, $quantity)
    {
        $this->db->query(
            "SELECT * FROM $this->itemTable WHERE cart_id = :cart_id AND product_id = :product_id"
        );
        $this->db->bind(":cart_id", $cartId);
        $this->db->bind(":product_id", $productId);
        $item = $this->db->single();

        if ($item) {
            $newQty = $item["quantity"] + $quantity;
            $this->db->query(
                "UPDATE $this->itemTable SET quantity = :quantity WHERE id = :id"
            );
            $this->db->bind(":quantity", $newQty);
            $this->db->bind(":id", $item["id"]);
            return $this->db->execute();
        }

        $this->db->query(
            "INSERT INTO $this->itemTable (id, cart_id, product_id, quantity) VALUES (UUID(), :cart_id, :product_id, :quantity)"
        );
        $this->db->bind(":cart_id", $cartId);
        $this->db->bind(":product_id", $productId);
        $this->db->bind(":quantity", $quantity);
        return $this->db->execute();
    }

    public function clearCart($cartId)
    {
        $this->db->query("DELETE FROM $this->itemTable WHERE cart_id = :cart_id");
        $this->db->bind(":cart_id", $cartId);
        return $this->db->execute();
    }

    public function deleteCartItemById($itemId)
    {
        $this->db->query("DELETE FROM $this->itemTable WHERE id = :id");
        $this->db->bind(":id", $itemId);
        return $this->db->execute();
    }

    public function increaseItemQuantity($itemId, $amount = 1)
    {
        $this->db->query(
            "UPDATE $this->itemTable SET quantity = quantity + :amount WHERE id = :id"
        );
        $this->db->bind(":amount", $amount);
        $this->db->bind(":id", $itemId);
        return $this->db->execute();
    }

    public function decreaseItemQuantity($itemId, $amount = 1)
    {
        $item = $this->getCartItemById($itemId);
        if (!$item) {
            return false;
        }

        $newQty = $item["quantity"] - $amount;

        if ($newQty > 0) {
            $this->db->query(
                "UPDATE $this->itemTable SET quantity = :quantity WHERE id = :id"
            );
            $this->db->bind(":quantity", $newQty);
            $this->db->bind(":id", $itemId);
            return $this->db->execute();
        }

        return $this->deleteCartItemById($itemId);
    }

    public function getCartItemById($itemId)
    {
        $this->db->query("SELECT * FROM $this->itemTable WHERE id = :id");
        $this->db->bind(":id", $itemId);
        return $this->db->single();
    }
}
