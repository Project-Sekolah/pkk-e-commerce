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
   * Ambil keranjang milik user. Jika tidak ada, buat yang baru.
   *
   * @param string $userId
   * @return array|null
   */
  public function getOrCreateCart($userId)
  {
    $this->db->query("SELECT * FROM $this->cartTable WHERE user_id = :user_id AND deleted_at IS NULL");
    $this->db->bind(":user_id", $userId);
    $cart = $this->db->single();

    if ($cart) return $cart;

    // Buat keranjang baru
    $this->db->query("INSERT INTO $this->cartTable (id, user_id) VALUES (UUID(), :user_id)");
    $this->db->bind(":user_id", $userId);
    $this->db->execute();

    // Ambil keranjang terbaru yang barusan dibuat
    $this->db->query("SELECT * FROM $this->cartTable WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
    $this->db->bind(":user_id", $userId);
    return $this->db->single();
  }

  /**
   * Tambahkan produk ke dalam keranjang.
   * Jika produk sudah ada, tambahkan quantity-nya.
   *
   * @param string $cartId
   * @param string $productId
   * @param int $quantity
   * @return bool
   */
  public function addItem($cartId, $productId, $quantity)
  {
    $this->db->query("SELECT * FROM $this->itemTable WHERE cart_id = :cart_id AND product_id = :product_id");
    $this->db->bind(":cart_id", $cartId);
    $this->db->bind(":product_id", $productId);
    $item = $this->db->single();

    if ($item) {
      $newQty = $item["quantity"] + $quantity;
      $this->db->query("UPDATE $this->itemTable SET quantity = :quantity WHERE id = :id");
      $this->db->bind(":quantity", $newQty);
      $this->db->bind(":id", $item["id"]);
      return $this->db->execute();
    }

    $this->db->query("INSERT INTO $this->itemTable (id, cart_id, product_id, quantity) VALUES (UUID(), :cart_id, :product_id, :quantity)");
    $this->db->bind(":cart_id", $cartId);
    $this->db->bind(":product_id", $productId);
    $this->db->bind(":quantity", $quantity);
    return $this->db->execute();
  }

  /**
   * Ambil seluruh item dalam keranjang.
   *
   * @param string $cartId
   * @return array
   */
  // Di method getCartItems()
public function getCartItems($cartId)
{
  $this->db->query("SELECT 
    ci.*, 
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

  /**
   * Ambil item berdasarkan array ID.
   *
   * @param array $itemIds
   * @return array
   */
  public function getCartItemsByIds($itemIds = [])
  {
    if (empty($itemIds)) return [];

    $placeholders = implode(",", array_fill(0, count($itemIds), "?"));
    $this->db->query("SELECT * FROM $this->itemTable WHERE id IN ($placeholders)");

    foreach ($itemIds as $i => $id) {
      $this->db->bind($i + 1, $id); // positional binding
    }

    return $this->db->resultSet();
  }

  /**
   * Hapus semua item dalam keranjang.
   *
   * @param string $cartId
   * @return bool
   */
  public function clearCart($cartId)
  {
    $this->db->query("DELETE FROM $this->itemTable WHERE cart_id = :cart_id");
    $this->db->bind(":cart_id", $cartId);
    return $this->db->execute();
  }

  /**
   * Hapus item dari keranjang berdasarkan ID.
   *
   * @param string $itemId
   * @return bool
   */
  public function deleteCartItemById($itemId)
  {
    $this->db->query("DELETE FROM $this->itemTable WHERE id = :id");
    $this->db->bind(":id", $itemId);
    return $this->db->execute();
  }

  /**
   * Tambah quantity item tertentu.
   *
   * @param string $itemId
   * @param int $amount
   * @return bool
   */
  public function increaseItemQuantity($itemId, $amount = 1)
  {
    $this->db->query("UPDATE $this->itemTable SET quantity = quantity + :amount WHERE id = :id");
    $this->db->bind(":amount", $amount);
    $this->db->bind(":id", $itemId);
    return $this->db->execute();
  }

  /**
   * Kurangi quantity item. Jika 0, hapus item-nya.
   *
   * @param string $itemId
   * @param int $amount
   * @return bool
   */
  public function decreaseItemQuantity($itemId, $amount = 1)
  {
    $item = $this->getCartItemById($itemId);
    if (!$item) return false;

    $newQty = $item["quantity"] - $amount;

    if ($newQty > 0) {
      $this->db->query("UPDATE $this->itemTable SET quantity = :quantity WHERE id = :id");
      $this->db->bind(":quantity", $newQty);
      $this->db->bind(":id", $itemId);
      return $this->db->execute();
    }

    return $this->deleteCartItemById($itemId);
  }

  /**
   * Ambil item berdasarkan ID.
   *
   * @param string $itemId
   * @return array|null
   */
  public function getCartItemById($itemId)
  {
    $this->db->query("SELECT * FROM $this->itemTable WHERE id = :id");
    $this->db->bind(":id", $itemId);
    return $this->db->single();
  }
}