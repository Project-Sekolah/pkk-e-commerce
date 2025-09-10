<?php
class Discount_model
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  // === Existing Discount Methods ===

  public function getAllDiscounts()
  {
    $sql = "SELECT * FROM discounts WHERE deleted_at IS NULL AND is_active = 1";
    $this->db->query($sql);
    return $this->db->resultSet();
  }

  public function getDiscountById($id)
  {
    $this->db->query("SELECT * FROM discounts WHERE id = :id");
    $this->db->bind("id", $id);
    return $this->db->single();
  }

  public function addDiscount($data)
  {
    $data["id"] = $this->generateUUID();

    $query = "INSERT INTO discounts (
                    id, name, percentage, start_date, end_date, is_active, user_id
                  ) VALUES (
                    :id, :name, :percentage, :start_date, :end_date, :is_active, :user_id
                  )";

    $this->db->query($query);
    $this->db->bind("id", $data["id"]);
    $this->db->bind("name", $data["name"]);
    $this->db->bind("percentage", $data["percentage"]);
    $this->db->bind("start_date", $data["start_date"]);
    $this->db->bind("end_date", $data["end_date"]);
    $this->db->bind("is_active", $data["is_active"]);
    $this->db->bind("user_id", $data["user_id"]);

    $this->db->execute();
    return $data["id"];
  }

  private function generateUUID()
  {
    return sprintf(
      "%04x%04x-%04x-%04x-%04x-%04x%04x%04x",
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
  }

  public function updateDiscount($data)
  {
    $query = "UPDATE discounts 
                  SET name = :name, 
                      percentage = :percentage, 
                      start_date = :start_date, 
                      end_date = :end_date,
                      is_active = :is_active,
                      updated_at = NOW()
                  WHERE id = :id";

    $this->db->query($query);
    $this->db->bind("id", $data["id"]);
    $this->db->bind("name", $data["name"]);
    $this->db->bind("percentage", $data["percentage"]);
    $this->db->bind("start_date", $data["start_date"]);
    $this->db->bind("end_date", $data["end_date"]);
    $this->db->bind("is_active", $data["is_active"]);

    return $this->db->execute();
  }

  public function deleteDiscount($id)
  {
    $query = "UPDATE discounts SET deleted_at = NOW() WHERE id = :id";
    $this->db->query($query);
    $this->db->bind("id", $id);
    return $this->db->execute();
  }

  // === Product-Discount Relationship Methods ===

  public function addProductDiscount($productId, $discountId)
  {
    // Periksa apakah diskon dan produk ada
    $discount = $this->getDiscountById($discountId);
    // Buat instance Product_model secara manual
    $productModel = new Product_model();
    $product = $productModel->getProductById($productId);

    if (!$discount || !$product) {
      return false;
    }

    // Periksa apakah relasi sudah ada
    $existingQuery =
      "SELECT id FROM product_discounts WHERE product_id = :product_id AND discount_id = :discount_id";
    $this->db->query($existingQuery);
    $this->db->bind("product_id", $productId);
    $this->db->bind("discount_id", $discountId);
    $existing = $this->db->single();

    if ($existing) {
      return false; // Relasi sudah ada
    }

    $query =
      "INSERT INTO product_discounts (id, product_id, discount_id, start_date, end_date, is_active) VALUES (UUID(), :product_id, :discount_id, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), 1)";
    $this->db->query($query);
    $this->db->bind("product_id", $productId);
    $this->db->bind("discount_id", $discountId);
    return $this->db->execute();
  }

  public function deleteProductDiscount($productId, $discountId)
  {
    $query =
      "DELETE FROM product_discounts WHERE product_id = :product_id AND discount_id = :discount_id";
    $this->db->query($query);
    $this->db->bind("product_id", $productId);
    $this->db->bind("discount_id", $discountId);
    return $this->db->execute();
  }

  public function getProductDiscounts($productId)
  {
    $query = "SELECT d.* FROM product_discounts pd 
       JOIN discounts d ON pd.discount_id = d.id 
       WHERE pd.product_id = :product_id 
       AND d.deleted_at IS NULL 
       AND d.is_active = 1
       AND d.start_date <= NOW() 
       AND d.end_date >= NOW()";
    $this->db->query($query);
    $this->db->bind("product_id", $productId);
    return $this->db->resultSet();
  }

  // Mendapatkan daftar produk yang memiliki diskon tertentu
  public function getDiscountProducts($discountId)
  {
    $query =
      "SELECT p.* FROM product_discounts pd JOIN products p ON pd.product_id = p.id WHERE pd.discount_id = :discount_id AND p.deleted_at IS NULL AND p.is_active = 1";
    $this->db->query($query);
    $this->db->bind("discount_id", $discountId);
    return $this->db->resultSet();
  }
/*
  public function deleteProductDiscount($productId, $discountId)
  {
    $query =
      "DELETE FROM product_discounts WHERE product_id = :product_id AND discount_id = :discount_id";
    $this->db->query($query);
    $this->db->bind("product_id", $productId);
    $this->db->bind("discount_id", $discountId);
    return $this->db->execute();
  }
*/
  public function getDiscountByCode($name)
  {
    $this->db->query("SELECT * FROM discounts WHERE name = :name");
    $this->db->bind("name", $name);
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

  // Validasi kepemilikan diskon
  public function isDiscountOwner($discountId, $userId)
  {
    $this->db->query("SELECT id FROM discounts WHERE id = :id AND user_id = :user_id");
    $this->db->bind("id", $discountId);
    $this->db->bind("user_id", $userId);
    return $this->db->single() ? true : false;
  }

  // Batasi akses detail diskon untuk produk yang dimiliki pengguna
  public function getDiscountsForUserProducts($userId)
  {
    $query = "
        SELECT d.* 
        FROM product_discounts pd
        JOIN discounts d ON pd.discount_id = d.id
        JOIN products p ON pd.product_id = p.id
        WHERE p.user_id = :user_id
          AND d.deleted_at IS NULL
          AND d.is_active = 1
          AND d.start_date <= NOW()
          AND d.end_date >= NOW()
    ";
    $this->db->query($query);
    $this->db->bind("user_id", $userId);
    return $this->db->resultSet();
  }

  public function getDiscountsByUserId($userId)
  {
    $sql = "SELECT * FROM discounts WHERE user_id = :user_id AND deleted_at IS NULL AND is_active = 1";
    $this->db->query($sql);
    $this->db->bind(":user_id", $userId);
    return $this->db->resultSet();
  }
}
