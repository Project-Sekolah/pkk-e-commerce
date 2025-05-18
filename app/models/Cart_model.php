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

  /**
   * Ambil keranjang user. Jika belum ada, buat baru.
   */
  public function getOrCreateCart($userId)
  {
    $this->db->query(
      "SELECT * FROM $this->cartTable WHERE user_id = :user_id AND deleted_at IS NULL"
    );
    $this->db->bind(":user_id", $userId);
    $cart = $this->db->single();

    if (!$cart) {
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

    return $cart;
  }

  /**
   * Tambahkan item ke keranjang. Jika produk sudah ada, tambahkan quantity.
   */
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
    } else {
      $this->db->query(
        "INSERT INTO $this->itemTable (id, cart_id, product_id, quantity) 
         VALUES (UUID(), :cart_id, :product_id, :quantity)"
      );
      $this->db->bind(":cart_id", $cartId);
      $this->db->bind(":product_id", $productId);
      $this->db->bind(":quantity", $quantity);
      return $this->db->execute();
    }
  }

  /**
   * Ambil semua item dalam keranjang.
   */
  public function getCartItems($cartId)
  {
    $sql = "SELECT * FROM $this->itemTable WHERE cart_id = :cart_id";
    $this->db->query($sql);
    $this->db->bind(":cart_id", $cartId);
    return $this->db->resultSet();
  }

  /**
   * Ambil banyak item berdasarkan array ID.
   */
  public function getCartItemsByIds($itemIds = [])
  {
    if (empty($itemIds)) {
      return [];
    }

    // Buat placeholder untuk binding
    $placeholders = implode(",", array_fill(0, count($itemIds), "?"));

    $sql = "SELECT * FROM $this->itemTable WHERE id IN ($placeholders)";
    $this->db->query($sql);

    // Bind setiap ID secara berurutan
    foreach ($itemIds as $index => $id) {
      $this->db->bind($index + 1, $id); // bind by position
    }

    return $this->db->resultSet();
  }

  /**
   * Hapus semua item dari keranjang.
   */
  public function clearCart($cartId)
  {
    $sql = "DELETE FROM $this->itemTable WHERE cart_id = :cart_id";
    $this->db->query($sql);
    $this->db->bind(":cart_id", $cartId);
    return $this->db->execute();
  }

  /**
   * Hapus item tertentu dari keranjang berdasarkan ID item.
   */
  public function deleteCartItemById($itemId)
  {
    $sql = "DELETE FROM $this->itemTable WHERE id = :id";
    $this->db->query($sql);
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
    // Ambil dulu quantity-nya
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
    } else {
      return $this->deleteCartItemById($itemId);
    }
  }
}


