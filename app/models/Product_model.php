<?php

class Product_model {
    private $table = "products";
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    
    // Get paginated & filtered products
    public function getFilteredProductsPaginated($categorySlugs = [], $genders = [], $search = '', $limit = 8, $offset = 0) {
        $sql = "
            SELECT 
                p.*, 
                c.name AS category_name, 
                c.slug AS category_slug,
                GROUP_CONCAT(pi.image_url) AS images
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id
            WHERE p.deleted_at IS NULL AND p.is_active = 1
        ";

        $params = [];

        // Filter by categories
        if (!empty($categorySlugs)) {
            $placeholders = implode(',', array_fill(0, count($categorySlugs), '?'));
            $sql .= " AND c.slug IN ($placeholders)";
            $params = array_merge($params, $categorySlugs);
        }

        // Filter by genders, skip if 'all' is present
        if (!empty($genders) && !in_array('all', $genders)) {
            $genderPlaceholders = implode(',', array_fill(0, count($genders), '?'));
            $sql .= " AND p.gender IN ($genderPlaceholders)";
            $params = array_merge($params, $genders);
        }

        // Filter by search term
        if (!empty($search)) {
            $sql .= " AND p.name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";

        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $this->db->query($sql);

        // Bind parameters safely
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->db->bind($key+1, $value, $paramType);
        }

        return $this->db->resultSet();
    }


    // Get total filtered product count (for pagination)
    public function getTotalFilteredProducts($categorySlugs = [], $genders = [], $search = '') {
        $sql = "
            SELECT COUNT(DISTINCT p.id) AS total
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.deleted_at IS NULL AND p.is_active = 1
        ";

        $params = [];

        // Filter by categories
        if (!empty($categorySlugs)) {
            $placeholders = implode(',', array_fill(0, count($categorySlugs), '?'));
            $sql .= " AND c.slug IN ($placeholders)";
            $params = array_merge($params, $categorySlugs);
        }

        // Filter by genders
        if (!empty($genders)) {
            $genderPlaceholders = implode(',', array_fill(0, count($genders), '?'));
            $sql .= " AND p.gender IN ($genderPlaceholders)";
            $params = array_merge($params, $genders);
        }

        // Filter by search term
        if (!empty($search)) {
            $sql .= " AND p.name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        // Execute the query
        $this->db->query($sql);
        $this->db->execute($params);

        $result = $this->db->single();
        return $result['total'] ?? 0;
    }


    public function getProductById($id){
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
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getLimitedProducts($limit) {
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
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }


    

}
?>
