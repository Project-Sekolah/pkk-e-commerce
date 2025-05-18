<?php

class Product_model
{
  private $table = "products";
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  // Get paginated & filtered products
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
      $sql .= " AND p.name LIKE ?";
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

  // Get total filtered product count (for pagination)
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

    // Filter by categories
    if (!empty($categorySlugs)) {
      $placeholders = implode(",", array_fill(0, count($categorySlugs), "?"));
      $sql .= " AND c.slug IN ($placeholders)";
      $params = array_merge($params, $categorySlugs);
    }

    // Filter by genders
    if (!empty($genders)) {
      $genderPlaceholders = implode(",", array_fill(0, count($genders), "?"));
      $sql .= " AND p.gender IN ($genderPlaceholders)";
      $params = array_merge($params, $genders);
    }

    // Filter by search term
    if (!empty($search)) {
      $sql .= " AND p.name LIKE ?";
      $params[] = "%" . $search . "%";
    }

    // Execute the query
    $this->db->query($sql);
    $this->db->execute($params);

    $result = $this->db->single();
    return $result["total"] ?? 0;
  }

  //belum di pake
  public function getProductById($id)
  {
    $this->db->query('
            SELECT 
                p.*, 
                c.name AS category_name, 
                c.slug AS category_slug,
                GROUP_CONCAT(pi.image_url) AS images
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id
            WHERE p.id = :id
            GROUP BY p.id
        ');
    $this->db->bind("id", $id);
    return $this->db->single();
  }

  //belum di pake
  public function getLimitedProducts($limit)
  {
    $query = "
            SELECT 
                p.*, 
                c.name AS category_name, 
                c.slug AS category_slug,
                GROUP_CONCAT(pi.image_url) AS images
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id
            WHERE p.deleted_at IS NULL AND p.is_active = 1
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ";

    $this->db->query($query);
    $this->db->bind(":limit", (int) $limit, PDO::PARAM_INT);
    return $this->db->resultSet();
  }

  // =======================
  // DATA Rating
  // =======================

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
      $this->db->bind($k + 1, $id, PDO::PARAM_STR); // id user adalah char(36), bind sebagai string
    }
    $results = $this->db->resultSet();

    // Kelompokkan rating berdasarkan product_id supaya mudah akses di view
    $grouped = [];
    foreach ($results as $row) {
      $grouped[$row["product_id"]][] = $row;
    }

    return $grouped;
  }

  public function addDataRating($data)
{
    // Cek apakah feedback user untuk produk ini sudah ada
    $checkQuery = "SELECT id FROM product_ratings WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
    $this->db->query($checkQuery);
    $this->db->bind("user_id", $data["user_id"]);
    $this->db->bind("product_id", $data["product_id"]);
    $existing = $this->db->single();

    if ($existing) {
        // Kalau sudah ada, update feedback lama
        $updateQuery = "UPDATE product_ratings SET rating = :rating, review_text = :review_text WHERE id = :id";
        $this->db->query($updateQuery);
        $this->db->bind("rating", $data["rating"]);
        $this->db->bind("review_text", $data["review_text"]);
        $this->db->bind("id", $existing["id"]);

        return $this->db->execute();
    } else {
        // Kalau belum ada, insert feedback baru
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
}
