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
                    id, name, percentage, start_date, end_date, is_active
                  ) VALUES (
                    :id, :name, :percentage, :start_date, :end_date, :is_active
                  )";

    $this->db->query($query);
    $this->db->bind("id", $data["id"]);
    $this->db->bind("name", $data["name"]);
    $this->db->bind("percentage", $data["percentage"]);
    $this->db->bind("start_date", $data["start_date"]);
    $this->db->bind("end_date", $data["end_date"]);
    $this->db->bind("is_active", $data["is_active"]);

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
    $query =
      "INSERT INTO product_discounts (id, product_id, discount_id) VALUES (UUID(), :product_id, :discount_id)";
    $this->db->query($query);
    $this->db->bind("product_id", $productId);
    $this->db->bind("discount_id", $discountId);
    return $this->db->execute();
  }


  public function getProductDiscounts($productId)
  {
    $query =
      "SELECT d.* FROM product_discounts pd JOIN discounts d ON pd.discount_id = d.id WHERE pd.product_id = :product_id AND d.deleted_at IS NULL AND d.is_active = 1";
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

  public function deleteProductDiscount($productId, $discountId)
  {
    $query =
      "DELETE FROM product_discounts WHERE product_id = :product_id AND discount_id = :discount_id";
    $this->db->query($query);
    $this->db->bind("product_id", $productId);
    $this->db->bind("discount_id", $discountId);
    return $this->db->execute();
  }
}
