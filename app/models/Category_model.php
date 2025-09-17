<?php

/**
 * Category Model
 * 
 * Handles all category-related operations including:
 * - Category CRUD operations
 * - Category retrieval by various parameters
 * - Category validation
 */
class Category_model {
    /** @var string Table name for categories */
    private const TABLE = 'categories';
    
    /** @var Database Database connection instance */
    private Database $db;

    /**
     * Constructor
     * Initializes database connection
     */
    public function __construct()
    {
        $this->db = new Database;
    }

    // ==========================
    // GETTER FUNCTIONS
    // ==========================

    /**
     * Gets all categories
     *
     * @return array List of all categories
     * @throws RuntimeException If query fails
     */
    public function getAllCategories(): array
    {
        try {
            $this->db->query("SELECT * FROM " . self::TABLE . " ORDER BY name ASC");
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Failed to get categories: " . $e->getMessage());
            throw new RuntimeException("Failed to retrieve categories");
        }
    }

    /**
     * Gets category by ID
     *
     * @param int $id Category ID
     * @return array|null Category data or null if not found
     * @throws RuntimeException If query fails
     */
    public function getCategoryById(int $id): ?array
    {
        try {
            $this->db->query("SELECT * FROM " . self::TABLE . " WHERE id = :id");
            $this->db->bind(':id', $id);
            $result = $this->db->single();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Failed to get category by ID: " . $e->getMessage());
            throw new RuntimeException("Failed to retrieve category");
        }
    }

    /**
     * Gets category by slug
     *
     * @param string $slug Category slug
     * @return array|null Category data or null if not found
     * @throws RuntimeException If query fails
     */
    public function getCategoryBySlug(string $slug): ?array
    {
        try {
            $this->db->query("SELECT * FROM " . self::TABLE . " WHERE slug = :slug");
            $this->db->bind(':slug', $slug);
            $result = $this->db->single();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Failed to get category by slug: " . $e->getMessage());
            throw new RuntimeException("Failed to retrieve category");
        }
    }

    // ==========================
    // CRUD FUNCTIONS
    // ==========================

    /**
     * Adds a new category
     *
     * @param string $name Category name
     * @param string $slug Category slug
     * @return bool True if successful
     * @throws InvalidArgumentException If validation fails
     * @throws RuntimeException If query fails
     */
    public function addCategory(string $name, string $slug): bool
    {
        $this->validateCategory($name, $slug);

        try {
            // ...existing code...

            // Check for duplicate slug
            if ($this->getCategoryBySlug($slug)) {
                throw new InvalidArgumentException("Category with this slug already exists");
            }

            $this->db->query("
                INSERT INTO " . self::TABLE . " (
                    name, 
                    slug,
                    created_at,
                    updated_at
                ) VALUES (
                    :name,
                    :slug,
                    NOW(),
                    NOW()
                )
            ");

            $this->db->bind(':name', trim($name));
            $this->db->bind(':slug', $this->sanitizeSlug($slug));

            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Failed to add category: " . $e->getMessage());
            throw new RuntimeException("Failed to create category");
        }
    }

    /**
     * Updates an existing category
     *
     * @param int $id Category ID
     * @param string $name New category name
     * @param string $slug New category slug
     * @return bool True if successful
     * @throws InvalidArgumentException If validation fails
     * @throws RuntimeException If query fails
     */
    public function updateCategory(int $id, string $name, string $slug): bool
    {
        $this->validateCategory($name, $slug);

        try {
            // ...existing code...

            // Check if category exists
            $existingCategory = $this->getCategoryById($id);
            if (!$existingCategory) {
                throw new InvalidArgumentException("Category not found");
            }

            // Check for duplicate slug (excluding current category)
            $this->db->query("
                SELECT id FROM " . self::TABLE . "
                WHERE slug = :slug AND id != :id
            ");
            $this->db->bind(':slug', $slug);
            $this->db->bind(':id', $id);
            if ($this->db->single()) {
                throw new InvalidArgumentException("Category with this slug already exists");
            }

            $this->db->query("
                UPDATE " . self::TABLE . "
                SET 
                    name = :name,
                    slug = :slug,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $this->db->bind(':id', $id);
            $this->db->bind(':name', trim($name));
            $this->db->bind(':slug', $this->sanitizeSlug($slug));

            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Failed to update category: " . $e->getMessage());
            throw new RuntimeException("Failed to update category");
        }
    }

    /**
     * Deletes a category
     *
     * @param int $id Category ID
     * @return bool True if successful
     * @throws InvalidArgumentException If category not found
     * @throws RuntimeException If query fails or category has associated products
     */
    public function deleteCategory(int $id): bool
    {
        try {
            // ...existing code...

            // Check if category exists
            if (!$this->getCategoryById($id)) {
                throw new InvalidArgumentException("Category not found");
            }

            // Check for associated products
            $this->db->query("
                SELECT COUNT(*) as count 
                FROM products 
                WHERE category_id = :id
            ");
            $this->db->bind(':id', $id);
            $productCount = $this->db->single()['count'];

            if ($productCount > 0) {
                throw new RuntimeException("Cannot delete category with associated products");
            }

            $this->db->query("DELETE FROM " . self::TABLE . " WHERE id = :id");
            $this->db->bind(':id', $id);

            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Failed to delete category: " . $e->getMessage());
            throw new RuntimeException("Failed to delete category");
        }
    }

    // ==========================
    // HELPER FUNCTIONS
    // ==========================

    /**
     * Validates category data
     *
     * @param string $name Category name
     * @param string $slug Category slug
     * @throws InvalidArgumentException If validation fails
     */
    private function validateCategory(string $name, string $slug): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException("Category name is required");
        }

        if (strlen($name) > 100) {
            throw new InvalidArgumentException("Category name must not exceed 100 characters");
        }

        if (empty(trim($slug))) {
            throw new InvalidArgumentException("Category slug is required");
        }

        if (strlen($slug) > 100) {
            throw new InvalidArgumentException("Category slug must not exceed 100 characters");
        }

        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            throw new InvalidArgumentException("Category slug must contain only lowercase letters, numbers, and hyphens");
        }
    }

    /**
     * Sanitizes a slug string
     *
     * @param string $slug Raw slug
     * @return string Sanitized slug
     */
    private function sanitizeSlug(string $slug): string
    {
        // Convert to lowercase
        $slug = strtolower($slug);
        
        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $slug);
        
        // Remove special characters
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        
        // Replace multiple hyphens with single hyphen
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Remove leading/trailing hyphens
        return trim($slug, '-');
    }
}
