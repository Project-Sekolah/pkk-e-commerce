<?php

class Product_model
{
  private $table = "products";
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function getFilteredProductsPaginated(
    $categorySlugs = [],
    $genders = [],
    $search = "",
    $limit = 8,
    $offset = 0
  ) {
    $sql = "
    SELECT 
      p.*, 
      c.name AS category_name, 
      c.slug AS category_slug,
      GROUP_CONCAT(pi.image_url) AS images,
      COALESCE(ratings_summary.average_rating, 0) AS average_rating,
      COALESCE(ratings_summary.total_ratings, 0) AS total_ratings,
      u.full_name AS owner_name,
      u.phone_number AS owner_phone
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON pi.product_id = p.id
        LEFT JOIN (
            SELECT 
                product_id,
                COUNT(*) AS total_ratings,
                AVG(rating) AS average_rating
            FROM product_ratings
            GROUP BY product_id
        ) AS ratings_summary ON ratings_summary.product_id = p.id
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.deleted_at IS NULL AND p.is_active = 1
    ";

    $params = [];

    if (!empty($categorySlugs)) {
      $placeholders = implode(",", array_fill(0, count($categorySlugs), "?"));
      $sql .= " AND c.slug IN ($placeholders)";
      $params = array_merge($params, $categorySlugs);
    }

    if (!empty($genders) && !in_array("all", $genders)) {
      $genderPlaceholders = implode(",", array_fill(0, count($genders), "?"));
      $sql .= " AND p.gender IN ($genderPlaceholders)";
      $params = array_merge($params, $genders);
    }

    if (!empty($search)) {
      $sql .= " AND p.title LIKE ?";
      $params[] = "%" . $search . "%";
    }

    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";

    $params[] = (int) $limit;
    $params[] = (int) $offset;

    $this->db->query($sql);

    foreach ($params as $key => $value) {
      $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
      $this->db->bind($key + 1, $value, $paramType);
    }

    return $this->db->resultSet();
  }

  public function getTotalFilteredProducts(
    $categorySlugs = [],
    $genders = [],
    $search = ""
  ) {
    $sql = "
            SELECT COUNT(DISTINCT p.id) AS total
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.deleted_at IS NULL AND p.is_active = 1
        ";

    $params = [];

    if (!empty($categorySlugs)) {
      $placeholders = implode(",", array_fill(0, count($categorySlugs), "?"));
      $sql .= " AND c.slug IN ($placeholders)";
      $params = array_merge($params, $categorySlugs);
    }

    if (!empty($genders)) {
      $genderPlaceholders = implode(",", array_fill(0, count($genders), "?"));
      $sql .= " AND p.gender IN ($genderPlaceholders)";
      $params = array_merge($params, $genders);
    }

    if (!empty($search)) {
      $sql .= " AND p.title LIKE ?";
      $params[] = "%" . $search . "%";
    }

    $this->db->query($sql);
    $this->db->execute($params);

    $result = $this->db->single();
    return $result["total"] ?? 0;
  }

  public function getProductById($id)
  {
    // Ambil detail produk dan kategori
    $this->db->query('
        SELECT 
            p.*, 
            c.name AS category_name, 
            c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = :id
    ');
    $this->db->bind("id", $id);
    $product = $this->db->single();

    // Ambil semua gambar produk
    $this->db->query(
      "SELECT id, image_url FROM product_images WHERE product_id = :id"
    );
    $this->db->bind("id", $id);
    $images = $this->db->resultSet();

    // Ambil semua diskon yang berlaku untuk produk
    $discountModel = new Discount_model();
    $productDiscounts = $discountModel->getProductDiscounts($id);

    $product["images"] = $images;
    $product["discounts"] = $productDiscounts;

    return $product;
  }

  public function getLimitedProducts($limit)
  {
    $query = "
    SELECT 
        p.*, 
        c.name AS category_name, 
        c.slug AS category_slug,
        GROUP_CONCAT(pi.image_url) AS images,
        u.full_name AS owner_name,
        u.phone_number AS owner_phone
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_images pi ON pi.product_id = p.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE p.deleted_at IS NULL AND p.is_active = 1
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT :limit
";


    $this->db->query($query);
    $this->db->bind(":limit", (int) $limit, PDO::PARAM_INT);
    return $this->db->resultSet();
  }

  public function getAllProductRatings($productId)
  {
    $this->db->query("
        SELECT 
            pr.rating,
            pr.review_text,
            pr.created_at,
            u.name AS user_name,
            u.image AS user_image
        FROM product_ratings pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.product_id = :product_id
        ORDER BY pr.created_at DESC
    ");
    $this->db->bind(":product_id", $productId);
    return $this->db->resultSet();
  }

  public function getAllRatingsForProductIds(array $productIds)
  {
    if (empty($productIds)) {
      return [];
    }

    $placeholders = implode(",", array_fill(0, count($productIds), "?"));
    $sql = "
        SELECT 
            pr.product_id,
            pr.rating,
            pr.review_text,
            pr.created_at,
            u.full_name AS user_name, 
            u.image AS user_image
        FROM product_ratings pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.product_id IN ($placeholders)
        ORDER BY pr.product_id, pr.created_at DESC
    ";

    $this->db->query($sql);
    foreach ($productIds as $k => $id) {
      $this->db->bind($k + 1, $id, PDO::PARAM_STR);
    }
    $results = $this->db->resultSet();

    $grouped = [];
    foreach ($results as $row) {
      $grouped[$row["product_id"]][] = $row;
    }

    return $grouped;
  }

  public function addDataRating($data)
  {
    $checkQuery =
      "SELECT id FROM product_ratings WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
    $this->db->query($checkQuery);
    $this->db->bind("user_id", $data["user_id"]);
    $this->db->bind("product_id", $data["product_id"]);
    $existing = $this->db->single();

    if ($existing) {
      $updateQuery =
        "UPDATE product_ratings SET rating = :rating, review_text = :review_text WHERE id = :id";
      $this->db->query($updateQuery);
      $this->db->bind("rating", $data["rating"]);
      $this->db->bind("review_text", $data["review_text"]);
      $this->db->bind("id", $existing["id"]);

      return $this->db->execute();
    } else {
      $insertQuery = "INSERT INTO product_ratings 
                        (id, user_id, product_id, rating, review_text) 
                        VALUES 
                        (UUID(), :user_id, :product_id, :rating, :review_text)";

      $this->db->query($insertQuery);
      $this->db->bind("user_id", $data["user_id"]);
      $this->db->bind("product_id", $data["product_id"]);
      $this->db->bind("rating", $data["rating"]);
      $this->db->bind("review_text", $data["review_text"]);

      return $this->db->execute();
    }
  }

  /**
   * Menambahkan produk baru ke database dengan UUID sebagai ID
   *
   * @param array $data Data produk yang akan ditambahkan
   * @return string UUID dari produk yang baru ditambahkan
   */
  public function addProduct($data)
  {
    // Generate UUID manual
    $data["id"] = $this->generateUUID();

    // Query insert dengan ID UUID
    $query = "INSERT INTO products (
                    id, category_id, user_id, title, slug, price, stock,
                    description, gender, is_active
                  ) VALUES (
                    :id, :category_id, :user_id, :title, :slug, :price, :stock,
                    :description, :gender, :is_active
                  )";

    $this->db->query($query);
    $this->db->bind("id", $data["id"]);
    $this->db->bind("category_id", $data["category_id"]);
    $this->db->bind("user_id", $data["user_id"]);
    $this->db->bind("title", $data["title"]);
    $this->db->bind("slug", $data["slug"]);
    $this->db->bind("price", $data["price"]);
    $this->db->bind("stock", $data["stock"]);
    $this->db->bind("description", $data["description"]);
    $this->db->bind("gender", $data["gender"]);
    $this->db->bind("is_active", $data["is_active"]);

    $this->db->execute();
    return $data["id"];
  }

  /**
   * Fungsi untuk generate UUID v4 secara manual (tanpa library)
   *
   * @return string UUID versi 4
   */
  private function generateUUID()
  {
    return sprintf(
      "%04x%04x-%04x-%04x-%04x-%04x%04x%04x",
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000, // UUID versi 4
      mt_rand(0, 0x3fff) | 0x8000, // Variant
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
  }

  public function updateProduct($data)
  {
    $query =
      "UPDATE products SET title = :title, slug = :slug, price = :price, stock = :stock, description = :description, gender = :gender, updated_at = NOW() WHERE id = :id";
    $this->db->query($query);
    $this->db->bind("id", $data["id"]);
    $this->db->bind("title", $data["title"]);
    $this->db->bind("slug", $data["slug"]);
    $this->db->bind("price", $data["price"]);
    $this->db->bind("stock", $data["stock"]);
    $this->db->bind("description", $data["description"]);
    $this->db->bind("gender", $data["gender"]);
    return $this->db->execute();
  }

  public function deleteProduct($id)
  {
    $query = "UPDATE products SET deleted_at = NOW() WHERE id = :id";
    $this->db->query($query);
    $this->db->bind("id", $id);
    return $this->db->execute();
  }

  public function getProductsBySeller($userId, $limit, $offset)
  {
    $sql = "
        SELECT 
            p.*, 
            c.name AS category_name, 
            c.slug AS category_slug,
            GROUP_CONCAT(pi.image_url) AS images,
            COALESCE(ratings_summary.average_rating, 0) AS average_rating,
            COALESCE(ratings_summary.total_ratings, 0) AS total_ratings
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON pi.product_id = p.id
        LEFT JOIN (
            SELECT 
                product_id,
                COUNT(*) AS total_ratings,
                AVG(rating) AS average_rating
            FROM product_ratings
            GROUP BY product_id
        ) AS ratings_summary ON ratings_summary.product_id = p.id
        WHERE p.deleted_at IS NULL AND p.is_active = 1 AND p.user_id = :user_id
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ";

    $this->db->query($sql);
    $this->db->bind("user_id", $userId);
    $this->db->bind("limit", (int) $limit, PDO::PARAM_INT);
    $this->db->bind("offset", (int) $offset, PDO::PARAM_INT);

    return $this->db->resultSet();
  }

  public function getTotalProductsBySeller($userId)
  {
    $sql = "
        SELECT COUNT(DISTINCT p.id) AS total
        FROM products p
        WHERE p.deleted_at IS NULL AND p.is_active = 1 AND p.seller_id = :seller_id
    ";

    $this->db->query($sql);
    $this->db->bind("seller_id", $userId);

    $result = $this->db->single();
    return $result["total"] ?? 0;
  }

  public function addProductImage($productId, $imageUrl)
  {
    $query =
      "INSERT INTO product_images (id, product_id, image_url) VALUES (UUID(), :product_id, :image_url)";
    $this->db->query($query);
    $this->db->bind("product_id", $productId);
    $this->db->bind("image_url", $imageUrl);
    return $this->db->execute();
  }

  public function getProductImages($productId)
  {
    $this->db->query(
      "SELECT image_url FROM product_images WHERE product_id = :product_id"
    );
    $this->db->bind("product_id", $productId);
    return $this->db->resultSet();
  }

  public function getProductImageById($imageId)
  {
    $this->db->query("SELECT * FROM product_images WHERE id = :id");
    $this->db->bind("id", $imageId);
    return $this->db->single();
  }

  public function deleteProductImage($imageId)
  {
    $query = "DELETE FROM product_images WHERE id = :id";
    $this->db->query($query);
    $this->db->bind("id", $imageId);
    return $this->db->execute();
  }

  // Mendapatkan daftar diskon yang berlaku untuk suatu produk
  public function getProductDiscounts($productId)
  {
    $discountModel = new Discount_model();
    return $discountModel->getProductDiscounts($productId);
  }

  public function updateProductImage($data)
  {
    $query = "UPDATE product_images SET image_url = :image_url WHERE id = :id";
    $this->db->query($query);
    $this->db->bind("id", $data["id"]);
    $this->db->bind("image_url", $data["image_url"]);
    return $this->db->execute();
  }

}
