<?php

/**
 * Discount Model
 * 
 * Handles discount operations including discount creation, validation,
 * and product-discount relationships.
 */
class Discount_model
{
    /** @var string Table name for discounts */
    private const TABLE = 'discounts';

    /** @var string Table name for product discounts relation */
    private const PRODUCT_DISCOUNTS_TABLE = 'product_discounts';

    /** @var int Default discount duration in months */
    private const DEFAULT_DURATION_MONTHS = 1;

    /** @var int Maximum allowed percentage */
    private const MAX_PERCENTAGE = 100;

    /** @var int Minimum allowed percentage */
    private const MIN_PERCENTAGE = 0;

    /** @var Database Database connection instance */
    private Database $db;

    /** @var Product_model Product model instance */
    private Product_model $productModel;

    /**
     * Constructor - initialize database connection and dependencies
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->productModel = new Product_model();
    }

    /**
     * Get all active discounts
     * 
     * @return array List of active discounts
     * @throws PDOException On database error
     */
    public function getAllDiscounts(): array
    {
        try {
            $sql = "
                SELECT * 
                FROM discounts 
                WHERE deleted_at IS NULL 
                AND is_active = 1
                ORDER BY created_at DESC
            ";

            $this->db->query($sql);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Failed to get discounts: " . $e->getMessage());
            throw new PDOException("Failed to retrieve discounts");
        }
    }

    /**
     * Get discount by ID
     * 
     * @param string $id Discount ID
     * @return array|false Discount data or false if not found
     * @throws PDOException On database error
     */
    public function getDiscountById(string $id)
    {
        try {
            $sql = "
                SELECT * 
                FROM discounts 
                WHERE id = :id 
                AND deleted_at IS NULL
            ";

            $this->db->query($sql);
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log("Failed to get discount: " . $e->getMessage());
            throw new PDOException("Failed to retrieve discount");
        }
    }

    /**
     * Add new discount
     * 
     * @param array $data Discount data
     * @return string New discount ID
     * @throws PDOException On database error
     * @throws InvalidArgumentException On validation failure
     */
    public function addDiscount(array $data): string
    {
        try {
            $this->db->beginTransaction();

            // Validate input data
            $this->validateDiscountData($data);

            // Check for duplicate discount code
            if ($this->getDiscountByCode($data['name'])) {
                throw new InvalidArgumentException("Discount code already exists");
            }

            // Generate new ID
            $data['id'] = $this->generateUuid();

            $sql = "
                INSERT INTO " . self::TABLE . " (
                    id, 
                    name, 
                    percentage, 
                    start_date, 
                    end_date, 
                    is_active,
                    user_id,
                    created_at,
                    updated_at
                ) VALUES (
                    :id, 
                    :name, 
                    :percentage, 
                    :start_date, 
                    :end_date, 
                    :is_active,
                    :user_id,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                )
            ";

            $this->db->query($sql);
            $this->bindDiscountParams($data);

            if (!$this->db->execute()) {
                throw new PDOException("Failed to create discount");
            }

            $this->db->commit();
            return $data['id'];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to add discount: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update existing discount
     * 
     * @param array $data Updated discount data
     * @return bool Success status
     * @throws PDOException On database error
     * @throws InvalidArgumentException On validation failure
     */
    public function updateDiscount(array $data): bool
    {
        try {
            $this->validateDiscountData($data, true);

            if (!$this->getDiscountById($data['id'])) {
                throw new InvalidArgumentException("Discount not found");
            }

            $sql = "
                UPDATE discounts 
                SET name = :name,
                    percentage = :percentage,
                    start_date = :start_date,
                    end_date = :end_date,
                    is_active = :is_active,
                    user_id = :user_id,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id 
                AND deleted_at IS NULL
            ";

            $this->db->query($sql);
            $this->bindDiscountParams($data);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Failed to update discount: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Soft delete a discount
     * 
     * @param string $id Discount ID
     * @return bool Success status
     * @throws PDOException On database error
     */
    public function deleteDiscount(string $id): bool
    {
        try {
            $sql = "
                UPDATE discounts 
                SET deleted_at = CURRENT_TIMESTAMP 
                WHERE id = :id 
                AND deleted_at IS NULL
            ";

            $this->db->query($sql);
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Failed to delete discount: " . $e->getMessage());
            throw new PDOException("Failed to delete discount");
        }
    }

    /**
     * Apply discount to product
     * 
     * @param string $productId Product ID
     * @param string $discountId Discount ID
     * @return bool Success status
     * @throws PDOException On database error
     * @throws InvalidArgumentException If product or discount not found
     */
    public function addProductDiscount(
        string $productId,
        string $discountId
    ): bool {
        try {
            $this->db->beginTransaction();

            // Validate existence and status
            $discount = $this->getDiscountById($discountId);
            $product = $this->productModel->getProductById($productId);

            if (!$discount || !$product) {
                throw new InvalidArgumentException(
                    "Product or discount not found"
                );
            }

            // Validate discount is active
            if (!$discount['is_active']) {
                throw new InvalidArgumentException("Discount is not active");
            }

            // Validate discount dates
            $now = new DateTime();
            $startDate = new DateTime($discount['start_date']);
            if ($startDate > $now) {
                throw new InvalidArgumentException("Discount period has not started");
            }

            if ($discount['end_date']) {
                $endDate = new DateTime($discount['end_date']);
                if ($endDate < $now) {
                    throw new InvalidArgumentException("Discount has expired");
                }
            }

            // Check if relationship exists
            if ($this->hasProductDiscount($productId, $discountId)) {
                throw new InvalidArgumentException(
                    "Discount already applied to product"
                );
            }

            $sql = "
                INSERT INTO " . self::PRODUCT_DISCOUNTS_TABLE . " (
                    id,
                    product_id,
                    discount_id,
                    start_date,
                    end_date,
                    is_active,
                    created_at,
                    updated_at
                ) VALUES (
                    UUID(),
                    :product_id,
                    :discount_id,
                    CURRENT_TIMESTAMP,
                    DATE_ADD(CURRENT_TIMESTAMP, INTERVAL " . self::DEFAULT_DURATION_MONTHS . " MONTH),
                    1,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                )
            ";

            $this->db->query($sql);
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':discount_id', $discountId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Failed to add product discount: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove discount from product
     * 
     * @param string $productId Product ID
     * @param string $discountId Discount ID
     * @return bool Success status
     * @throws PDOException On database error
     */
    public function deleteProductDiscount(
        string $productId,
        string $discountId
    ): bool {
        try {
            $sql = "
                UPDATE product_discounts 
                SET deleted_at = CURRENT_TIMESTAMP 
                WHERE product_id = :product_id 
                AND discount_id = :discount_id 
                AND deleted_at IS NULL
            ";

            $this->db->query($sql);
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':discount_id', $discountId);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Failed to delete product discount: " . $e->getMessage());
            throw new PDOException("Failed to remove discount from product");
        }
    }

    /**
     * Get active discounts for a product
     * 
     * @param string $productId Product ID
     * @return array List of active discounts for product
     * @throws PDOException On database error
     */
    public function getProductDiscounts(string $productId): array
    {
        try {
            $sql = "
                SELECT d.* 
                FROM product_discounts pd 
                JOIN discounts d ON pd.discount_id = d.id 
                WHERE pd.product_id = :product_id 
                AND pd.deleted_at IS NULL
                AND d.deleted_at IS NULL 
                AND d.is_active = 1
                AND d.start_date <= CURRENT_TIMESTAMP 
                AND (d.end_date IS NULL OR d.end_date >= CURRENT_TIMESTAMP)
            ";

            $this->db->query($sql);
            $this->db->bind(':product_id', $productId);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Failed to get product discounts: " . $e->getMessage());
            throw new PDOException("Failed to retrieve product discounts");
        }
    }

    /**
     * Get products with specific discount
     * 
     * @param string $discountId Discount ID
     * @return array List of products with discount
     * @throws PDOException On database error
     */
    public function getDiscountProducts(string $discountId): array
    {
        try {
            $sql = "
                SELECT p.* 
                FROM product_discounts pd 
                JOIN products p ON pd.product_id = p.id 
                WHERE pd.discount_id = :discount_id 
                AND pd.deleted_at IS NULL
                AND p.deleted_at IS NULL 
                AND p.is_active = 1
            ";

            $this->db->query($sql);
            $this->db->bind(':discount_id', $discountId);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Failed to get discount products: " . $e->getMessage());
            throw new PDOException("Failed to retrieve products with discount");
        }
    }

    /**
     * Get discount by code/name
     * 
     * @param string $name Discount code/name
     * @return array|false Discount data or false if not found
     * @throws PDOException On database error
     */
    public function getDiscountByCode(string $name)
    {
        try {
            $sql = "
                SELECT * 
                FROM discounts 
                WHERE name = :name 
                AND deleted_at IS NULL
            ";

            $this->db->query($sql);
            $this->db->bind(':name', $name);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log("Failed to get discount by code: " . $e->getMessage());
            throw new PDOException("Failed to retrieve discount");
        }
    }

    /**
     * Check if user owns discount
     * 
     * @param string $discountId Discount ID
     * @param string $userId User ID
     * @return bool True if user owns discount
     * @throws PDOException On database error
     */
    public function isDiscountOwner(string $discountId, string $userId): bool
    {
        try {
            $sql = "
                SELECT id 
                FROM discounts 
                WHERE id = :id 
                AND user_id = :user_id 
                AND deleted_at IS NULL
            ";

            $this->db->query($sql);
            $this->db->bind(':id', $discountId);
            $this->db->bind(':user_id', $userId);
            return (bool)$this->db->single();
        } catch (PDOException $e) {
            error_log("Failed to check discount ownership: " . $e->getMessage());
            throw new PDOException("Failed to verify discount ownership");
        }
    }

    /**
     * Get discounts for user's products
     * 
     * @param string $userId User ID
     * @return array List of discounts
     * @throws PDOException On database error
     */
    public function getDiscountsForUserProducts(string $userId): array
    {
        try {
            $sql = "
                SELECT DISTINCT d.* 
                FROM product_discounts pd
                JOIN discounts d ON pd.discount_id = d.id
                JOIN products p ON pd.product_id = p.id
                WHERE p.user_id = :user_id
                AND pd.deleted_at IS NULL
                AND d.deleted_at IS NULL
                AND d.is_active = 1
                AND d.start_date <= CURRENT_TIMESTAMP
                AND (d.end_date IS NULL OR d.end_date >= CURRENT_TIMESTAMP)
                ORDER BY d.created_at DESC
            ";

            $this->db->query($sql);
            $this->db->bind(':user_id', $userId);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Failed to get user product discounts: " . $e->getMessage());
            throw new PDOException("Failed to retrieve discounts");
        }
    }

    /**
     * Check if product has discount
     * 
     * @param string $productId Product ID
     * @param string $discountId Discount ID
     * @return bool True if relationship exists
     */
    private function hasProductDiscount(
        string $productId,
        string $discountId
    ): bool {
        $sql = "
            SELECT id 
            FROM product_discounts 
            WHERE product_id = :product_id 
            AND discount_id = :discount_id 
            AND deleted_at IS NULL
        ";

        $this->db->query($sql);
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':discount_id', $discountId);
        return (bool)$this->db->single();
    }

    /**
     * Validate discount data
     * 
     * @param array $data Data to validate
     * @param bool $requireId Whether ID is required
     * @throws InvalidArgumentException On validation failure
     */
    private function validateDiscountData(array $data, bool $requireId = false): void
    {
        // Required fields validation
        $required = ['name', 'percentage', 'start_date', 'is_active', 'user_id'];
        if ($requireId) {
            $required[] = 'id';
        }

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new InvalidArgumentException(
                    "Missing required field: $field"
                );
            }
        }

        // Name validation
        if (strlen($data['name']) < 3 || strlen($data['name']) > 50) {
            throw new InvalidArgumentException(
                "Discount name must be between 3 and 50 characters"
            );
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $data['name'])) {
            throw new InvalidArgumentException(
                "Discount name can only contain letters, numbers, underscores and hyphens"
            );
        }

        // Percentage validation
        if (!is_numeric($data['percentage'])) {
            throw new InvalidArgumentException("Percentage must be a number");
        }

        $percentage = (float) $data['percentage'];
        if ($percentage < self::MIN_PERCENTAGE || $percentage > self::MAX_PERCENTAGE) {
            throw new InvalidArgumentException(
                sprintf(
                    "Percentage must be between %d and %d",
                    self::MIN_PERCENTAGE,
                    self::MAX_PERCENTAGE
                )
            );
        }

        // Date validation
        try {
            $startDate = new DateTime($data['start_date']);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid start date format");
        }

        if (isset($data['end_date']) && $data['end_date']) {
            try {
                $endDate = new DateTime($data['end_date']);
                if ($startDate > $endDate) {
                    throw new InvalidArgumentException(
                        "End date must be after start date"
                    );
                }

                // Validate maximum discount duration (e.g., 1 year)
                $maxEndDate = clone $startDate;
                $maxEndDate->modify('+1 year');
                if ($endDate > $maxEndDate) {
                    throw new InvalidArgumentException(
                        "Discount duration cannot exceed 1 year"
                    );
                }
            } catch (Exception $e) {
                throw new InvalidArgumentException("Invalid end date format");
            }
        }

        // Active status validation
        if (!is_bool($data['is_active']) && !in_array($data['is_active'], [0, 1], true)) {
            throw new InvalidArgumentException(
                "Active status must be boolean or 0/1"
            );
        }

        // User ID validation
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $data['user_id'])) {
            throw new InvalidArgumentException("Invalid user ID format");
        }
    }

    /**
     * Bind discount parameters to query
     * 
     * @param array $data Data to bind
     */
    private function bindDiscountParams(array $data): void
    {
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':percentage', $data['percentage']);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date'] ?? null);
        $this->db->bind(':is_active', $data['is_active']);
        $this->db->bind(':user_id', $data['user_id']);
    }

    /**
     * Generate a UUID v4
     * 
     * @return string UUID
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
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
}
