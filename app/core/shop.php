<?php
/**
 * Shop system functions
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Get all brands
 * 
 * @return array List of all brands
 */
function getAllBrands() {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->query("SELECT * FROM brands ORDER BY sequence ASC, name ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get brand by ID
 * 
 * @param int $brandId Brand ID
 * @return array|false Brand data or false if not found
 */
function getBrandById($brandId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->execute([$brandId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Add new brand
 * 
 * @param string $name Brand name
 * @param string $image Brand image path
 * @param int $sequence Display sequence (lower values shown first)
 * @return array Result with status and message
 */
function addBrand($name, $image, $sequence = 999) {
    /** @var \PDO $pdo */
    global $pdo;
    
    // Validate input
    if (empty($name) || empty($image)) {
        return ['success' => false, 'message' => 'Brand name and image are required'];
    }
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Check if brand already exists
        $stmt = $pdo->prepare("SELECT * FROM brands WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Brand already exists'];
        }
        
        // Insert new brand
        $stmt = $pdo->prepare("INSERT INTO brands (name, image, sequence) VALUES (?, ?, ?)");
        $stmt->execute([$name, $image, $sequence]);
        
        return ['success' => true, 'message' => 'Brand added successfully', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to add brand: ' . $e->getMessage()];
    }
}

/**
 * Update brand
 * 
 * @param int $brandId Brand ID
 * @param string $name Brand name
 * @param string $image Brand image path
 * @param int|null $sequence Display sequence (lower values shown first)
 * @return array Result with status and message
 */
function updateBrand($brandId, $name, $image = null, $sequence = null) {
    /** @var \PDO $pdo */
    global $pdo;
    
    // Validate input
    if (empty($brandId) || empty($name)) {
        return ['success' => false, 'message' => 'Brand ID and name are required'];
    }
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // If image and sequence are provided
        if ($image && $sequence !== null) {
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, image = ?, sequence = ? WHERE id = ?");
            $stmt->execute([$name, $image, $sequence, $brandId]);
        }
        // If only image is provided
        else if ($image) {
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $image, $brandId]);
        }
        // If only sequence is provided
        else if ($sequence !== null) {
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, sequence = ? WHERE id = ?");
            $stmt->execute([$name, $sequence, $brandId]);
        }
        // Otherwise, update only name
        else {
            $stmt = $pdo->prepare("UPDATE brands SET name = ? WHERE id = ?");
            $stmt->execute([$name, $brandId]);
        }
        
        return ['success' => true, 'message' => 'Brand updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update brand: ' . $e->getMessage()];
    }
}

/**
 * Delete brand
 * 
 * @param int $brandId Brand ID
 * @return array Result with status and message
 */
function deleteBrand($brandId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->execute([$brandId]);
        
        return ['success' => true, 'message' => 'Brand deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete brand: ' . $e->getMessage()];
    }
}

/**
 * Get all products
 * 
 * @param int|null $brandId Optional brand ID to filter by
 * @return array List of all products
 */
function getAllProducts($brandId = null) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        if ($brandId) {
            $stmt = $pdo->prepare("
                SELECT p.*, b.name as brand_name, 
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                       vm.name as make_name, vmo.name as model_name, vs.name as series_name
                FROM products p
                JOIN brands b ON p.brand_id = b.id
                LEFT JOIN vehicle_makes vm ON p.make_id = vm.id
                LEFT JOIN vehicle_models vmo ON p.model_id = vmo.id
                LEFT JOIN vehicle_series vs ON p.series_id = vs.id
                WHERE p.brand_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$brandId]);
        } else {
            $stmt = $pdo->query("
                SELECT p.*, b.name as brand_name,
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                       vm.name as make_name, vmo.name as model_name, vs.name as series_name
                FROM products p
                JOIN brands b ON p.brand_id = b.id
                LEFT JOIN vehicle_makes vm ON p.make_id = vm.id
                LEFT JOIN vehicle_models vmo ON p.model_id = vmo.id
                LEFT JOIN vehicle_series vs ON p.series_id = vs.id
                ORDER BY p.created_at DESC
            ");
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get product by ID
 * 
 * @param int $productId Product ID
 * @return array|false Product data or false if not found
 */
function getProductById($productId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, b.name as brand_name, b.id as brand_id,
                   vm.name as make_name, vmo.name as model_name, vs.name as series_name
            FROM products p
            JOIN brands b ON p.brand_id = b.id
            LEFT JOIN vehicle_makes vm ON p.make_id = vm.id
            LEFT JOIN vehicle_models vmo ON p.model_id = vmo.id
            LEFT JOIN vehicle_series vs ON p.series_id = vs.id
            WHERE p.id = ?
        ");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get product images by product ID
 * 
 * @param int $productId Product ID
 * @return array List of product images
 */
function getProductImages($productId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Add new product
 * 
 * @param int $brandId Brand ID
 * @param string $title Product title
 * @param float $amount Product amount/price
 * @param string $description Product description
 * @param int|null $makeId Make ID (optional)
 * @param int|null $modelId Model ID (optional)
 * @param int|null $seriesId Series ID (optional)
 * @param int $directBuying Whether direct buying is enabled (optional, defaults to 0)
 * @return array Result with status and message
 */
function addProduct($brandId, $title, $amount, $description, $makeId = null, $modelId = null, $seriesId = null, $directBuying = 0) {
    /** @var \PDO $pdo */
    global $pdo;
    
    // Validate input
    if (empty($brandId) || empty($title) || empty($amount) || empty($description)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    // Check if pdo is available
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Insert new product
        $stmt = $pdo->prepare("INSERT INTO products (brand_id, title, amount, description, make_id, model_id, series_id, direct_buying) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$brandId, $title, $amount, $description, $makeId, $modelId, $seriesId, $directBuying]);
        $productId = $pdo->lastInsertId();
        
        // Commit transaction
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'Product added successfully',
            'id' => $productId
        ];
    } catch (PDOException $e) {
        // Rollback transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Failed to add product: ' . $e->getMessage()];
    }
}

/**
 * Update product
 * 
 * @param int $productId Product ID
 * @param int $brandId Brand ID
 * @param string $title Product title
 * @param float $amount Product amount/price
 * @param string $description Product description
 * @param int|null $makeId Make ID (optional)
 * @param int|null $modelId Model ID (optional)
 * @param int|null $seriesId Series ID (optional)
 * @param int $directBuying Whether direct buying is enabled (optional)
 * @return array Result with status and message
 */
function updateProduct($productId, $brandId, $title, $amount, $description, $makeId = null, $modelId = null, $seriesId = null, $directBuying = 0) {
    /** @var \PDO $pdo */
    global $pdo;
    
    // Validate input
    if (empty($productId) || empty($brandId) || empty($title) || empty($amount) || empty($description)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    // Check if pdo is available
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET brand_id = ?, title = ?, amount = ?, description = ?, make_id = ?, model_id = ?, series_id = ?, direct_buying = ? 
            WHERE id = ?
        ");
        $stmt->execute([$brandId, $title, $amount, $description, $makeId, $modelId, $seriesId, $directBuying, $productId]);
        
        return ['success' => true, 'message' => 'Product updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()];
    }
}

/**
 * Delete product
 * 
 * @param int $productId Product ID
 * @return array Result with status and message
 */
function deleteProduct($productId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Delete product images first (foreign key constraint will handle this, but we'll do it explicitly)
        $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        // Commit transaction
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Product deleted successfully'];
    } catch (PDOException $e) {
        // Rollback transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Failed to delete product: ' . $e->getMessage()];
    }
}

/**
 * Add product image
 * 
 * @param int $productId Product ID
 * @param string $imagePath Image path
 * @param bool $isPrimary Whether this is the primary image
 * @return array Result with status and message
 */
function addProductImage($productId, $imagePath, $isPrimary = false) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // If this is the primary image, unset any existing primary image
        if ($isPrimary) {
            $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
            $stmt->execute([$productId]);
        }
        
        // Add the new image
        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
        $stmt->execute([$productId, $imagePath, $isPrimary ? 1 : 0]);
        
        // Commit transaction
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Product image added successfully'];
    } catch (PDOException $e) {
        // Rollback transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Failed to add product image: ' . $e->getMessage()];
    }
}

/**
 * Delete product image
 * 
 * @param int $imageId Image ID
 * @return array Result with status and message
 */
function deleteProductImage($imageId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        
        return ['success' => true, 'message' => 'Product image deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete product image: ' . $e->getMessage()];
    }
}

/**
 * Set image as primary
 * 
 * @param int $imageId Image ID
 * @param int $productId Product ID
 * @return array Result with status and message
 */
function setImageAsPrimary($imageId, $productId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Unset all primary images for this product
        $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // Set the specified image as primary
        $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
        $stmt->execute([$imageId]);
        
        // Commit transaction
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Primary image updated successfully'];
    } catch (PDOException $e) {
        // Rollback transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Failed to update primary image: ' . $e->getMessage()];
    }
}

/**
 * Get all products with optional filters
 * 
 * @param int|null $brandId Optional brand ID to filter by
 * @param int|null $makeId Optional make ID to filter by
 * @param int|null $modelId Optional model ID to filter by
 * @param int|null $seriesId Optional series ID to filter by
 * @return array List of filtered products
 */
function getAllProductsWithFilters($brandId = null, $makeId = null, $modelId = null, $seriesId = null) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        $params = [];
        $sql = "
            SELECT p.*, b.name as brand_name,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                   vm.name as make_name, vmo.name as model_name, vs.name as series_name
            FROM products p
            JOIN brands b ON p.brand_id = b.id
            LEFT JOIN vehicle_makes vm ON p.make_id = vm.id
            LEFT JOIN vehicle_models vmo ON p.model_id = vmo.id
            LEFT JOIN vehicle_series vs ON p.series_id = vs.id
            WHERE 1=1";
        
        // Add brand filter if provided
        if ($brandId) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $brandId;
        }
        
        // Add make filter if provided
        if ($makeId) {
            $sql .= " AND p.make_id = ?";
            $params[] = $makeId;
        }
        
        // Add model filter if provided
        if ($modelId) {
            $sql .= " AND p.model_id = ?";
            $params[] = $modelId;
        }
        
        // Add series filter if provided
        if ($seriesId) {
            $sql .= " AND p.series_id = ?";
            $params[] = $seriesId;
        }
        
        // Add ordering
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get all categories
 * 
 * @param int|null $brandId Optional brand ID to filter categories
 * @return array List of all categories
 */
function getAllCategories($brandId = null) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        if ($brandId) {
            // Get categories for specific brand
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE brand_id = ? ORDER BY name");
            $stmt->execute([$brandId]);
        } else {
            // Get all categories with brand info
            $stmt = $pdo->query("
                SELECT c.*, b.name as brand_name 
                FROM categories c
                JOIN brands b ON c.brand_id = b.id
                ORDER BY b.name, c.name
            ");
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get categories by brand ID
 * 
 * @param int $brandId Brand ID
 * @return array List of categories for the brand
 */
function getCategoriesByBrandId($brandId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo || !$brandId) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE brand_id = ? ORDER BY name");
        $stmt->execute([$brandId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get category by ID
 * 
 * @param int $categoryId Category ID
 * @return array|false Category data or false if not found
 */
function getCategoryById($categoryId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, b.name as brand_name 
            FROM categories c
            JOIN brands b ON c.brand_id = b.id
            WHERE c.id = ?
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Add new category
 * 
 * @param string $name Category name
 * @param int $brandId Brand ID
 * @param string $description Category description (optional)
 * @return array Result with status and message
 */
function addCategory($name, $brandId, $description = '') {
    /** @var \PDO $pdo */
    global $pdo;
    
    // Validate input
    if (empty($name)) {
        return ['success' => false, 'message' => 'Category name is required'];
    }
    
    if (empty($brandId)) {
        return ['success' => false, 'message' => 'Brand is required'];
    }
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Check if category already exists for this brand
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE name = ? AND brand_id = ?");
        $stmt->execute([$name, $brandId]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Category already exists for this brand'];
        }
        
        // Insert new category
        $stmt = $pdo->prepare("INSERT INTO categories (name, brand_id, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $brandId, $description]);
        
        return ['success' => true, 'message' => 'Category added successfully', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to add category: ' . $e->getMessage()];
    }
}

/**
 * Update category
 * 
 * @param int $categoryId Category ID
 * @param string $name Category name
 * @param int $brandId Brand ID
 * @param string $description Category description (optional)
 * @return array Result with status and message
 */
function updateCategory($categoryId, $name, $brandId, $description = '') {
    /** @var \PDO $pdo */
    global $pdo;
    
    // Validate input
    if (empty($categoryId) || empty($name)) {
        return ['success' => false, 'message' => 'Category ID and name are required'];
    }
    
    if (empty($brandId)) {
        return ['success' => false, 'message' => 'Brand is required'];
    }
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Check if category with same name already exists for this brand (excluding current category)
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE name = ? AND brand_id = ? AND id != ?");
        $stmt->execute([$name, $brandId, $categoryId]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Another category with this name already exists for this brand'];
        }
        
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, brand_id = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $brandId, $description, $categoryId]);
        
        return ['success' => true, 'message' => 'Category updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update category: ' . $e->getMessage()];
    }
}

/**
 * Delete category
 * 
 * @param int $categoryId Category ID
 * @return array Result with status and message
 */
function deleteCategory($categoryId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        
        return ['success' => true, 'message' => 'Category deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete category: ' . $e->getMessage()];
    }
}

/**
 * Get categories for a product
 * 
 * @param int $productId Product ID
 * @return array List of categories associated with the product
 */
function getProductCategories($productId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.* 
            FROM categories c
            JOIN product_categories pc ON c.id = pc.category_id
            WHERE pc.product_id = ?
            ORDER BY c.name
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get category IDs for a product
 * 
 * @param int $productId Product ID
 * @return array List of category IDs associated with the product
 */
function getProductCategoryIds($productId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT category_id 
            FROM product_categories
            WHERE product_id = ?
        ");
        $stmt->execute([$productId]);
        return array_column($stmt->fetchAll(), 'category_id');
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Associate products with categories
 * 
 * @param int $productId Product ID
 * @param array $categoryIds Array of category IDs
 * @return array Result with status and message
 */
function updateProductCategories($productId, $categoryIds = []) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (empty($productId)) {
        return ['success' => false, 'message' => 'Product ID is required'];
    }
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Delete existing category associations
        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // Add new category associations
        if (!empty($categoryIds)) {
            $values = [];
            $placeholders = [];
            
            foreach ($categoryIds as $categoryId) {
                $values[] = $productId;
                $values[] = $categoryId;
                $placeholders[] = "(?, ?)";
            }
            
            $sql = "INSERT INTO product_categories (product_id, category_id) VALUES " . implode(", ", $placeholders);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
        }
        
        // Commit transaction
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Product categories updated successfully'];
    } catch (PDOException $e) {
        // Rollback transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Failed to update product categories: ' . $e->getMessage()];
    }
}

/**
 * Get products by category
 * 
 * @param int $categoryId Category ID
 * @return array List of products in the category
 */
function getProductsByCategory($categoryId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, b.name as brand_name,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                   vm.name as make_name, vmo.name as model_name, vs.name as series_name
            FROM products p
            JOIN brands b ON p.brand_id = b.id
            JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN vehicle_makes vm ON p.make_id = vm.id
            LEFT JOIN vehicle_models vmo ON p.model_id = vmo.id
            LEFT JOIN vehicle_series vs ON p.series_id = vs.id
            WHERE pc.category_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get the current user's cart items
 * 
 * @return array Cart items with product details
 */
function getCartItems() {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    // Get session ID
    $sessionId = session_id();
    
    // Get user ID if logged in
    $userId = null;
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    try {
        // Debug info
        error_log("Getting cart items for session: $sessionId, user: " . ($userId ? $userId : 'guest'));
        
        // Prepare query conditions based on session or user
        if ($userId) {
            // For logged in users, get items from both their user_id and their current session
            $stmt = $pdo->prepare("
                SELECT ci.*, p.title, p.amount, 
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image_path 
                FROM shopping_cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ? OR ci.session_id = ?
                ORDER BY ci.created_at DESC
            ");
            $stmt->execute([$userId, $sessionId]);
        } else {
            // For guests, only get items from their session
            $stmt = $pdo->prepare("
                SELECT ci.*, p.title, p.amount, 
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image_path 
                FROM shopping_cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.session_id = ?
                ORDER BY ci.created_at DESC
            ");
            $stmt->execute([$sessionId]);
        }
        
        $results = $stmt->fetchAll();
        error_log("Found " . count($results) . " cart items");
        return $results;
    } catch (PDOException $e) {
        error_log("Database error in getCartItems: " . $e->getMessage());
        return [];
    }
}

/**
 * Add an item to the cart
 * 
 * @param int $productId The product ID
 * @param int $quantity The quantity to add
 * @return array Result with status and message
 */
function addToCart($productId, $quantity = 1) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log("Database connection error in addToCart");
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    // Validate product ID
    if (!$productId) {
        error_log("Invalid product ID in addToCart");
        return ['success' => false, 'message' => 'Invalid product ID'];
    }
    
    // Validate quantity
    $quantity = max(1, intval($quantity));
    
    // Get session ID
    $sessionId = session_id();
    if (empty($sessionId)) {
        // Make sure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            $sessionId = session_id();
        }
        
        if (empty($sessionId)) {
            error_log("Failed to get session ID in addToCart");
            return ['success' => false, 'message' => 'Session error'];
        }
    }
    
    // Get user ID if logged in
    $userId = null;
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    error_log("Adding to cart: Product ID: $productId, Quantity: $quantity, Session: $sessionId, User: " . ($userId ?: 'guest'));
    
    try {
        // Check if the product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            error_log("Product not found: $productId");
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Check if this item is already in the cart
        if ($userId) {
            $stmt = $pdo->prepare("SELECT id, quantity FROM shopping_cart_items WHERE (user_id = ? OR session_id = ?) AND product_id = ?");
            $stmt->execute([$userId, $sessionId, $productId]);
        } else {
            $stmt = $pdo->prepare("SELECT id, quantity FROM shopping_cart_items WHERE session_id = ? AND product_id = ?");
            $stmt->execute([$sessionId, $productId]);
        }
        
        $existingItem = $stmt->fetch();
        
        if ($existingItem) {
            // Update quantity for existing item
            $newQuantity = $existingItem['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE shopping_cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newQuantity, $existingItem['id']]);
            
            error_log("Updated cart item {$existingItem['id']} quantity to $newQuantity");
            return ['success' => true, 'message' => 'Item quantity updated in cart', 'quantity' => $newQuantity];
        } else {
            // Add new item to cart
            $stmt = $pdo->prepare("INSERT INTO shopping_cart_items (session_id, user_id, product_id, quantity) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sessionId, $userId, $productId, $quantity]);
            $cartItemId = $pdo->lastInsertId();
            
            error_log("Added new cart item ID: $cartItemId, Product: $productId, Quantity: $quantity");
            
            // Double-check the item was added
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM shopping_cart_items WHERE id = ?");
            $stmt->execute([$cartItemId]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                error_log("Verified item was added to cart");
            } else {
                error_log("WARNING: Item appears not to have been added to cart");
            }
            
            return ['success' => true, 'message' => 'Item added to cart', 'quantity' => $quantity, 'cart_item_id' => $cartItemId];
        }
    } catch (PDOException $e) {
        error_log("Database error in addToCart: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error adding item to cart: ' . $e->getMessage()];
    }
}

/**
 * Update cart item quantity
 * 
 * @param int $cartItemId The cart item ID
 * @param int $quantity The new quantity
 * @return array Result with status and message
 */
function updateCartItemQuantity($cartItemId, $quantity) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    // Validate input
    if (!$cartItemId) {
        return ['success' => false, 'message' => 'Invalid cart item ID'];
    }
    
    // Ensure quantity is at least 1
    $quantity = max(1, intval($quantity));
    
    // Get session ID and user ID for security check
    $sessionId = session_id();
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    try {
        // Verify that the cart item belongs to this user/session
        if ($userId) {
            $stmt = $pdo->prepare("SELECT id FROM shopping_cart_items WHERE id = ? AND (user_id = ? OR session_id = ?)");
            $stmt->execute([$cartItemId, $userId, $sessionId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM shopping_cart_items WHERE id = ? AND session_id = ?");
            $stmt->execute([$cartItemId, $sessionId]);
        }
        
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Cart item not found or access denied'];
        }
        
        // Update the item quantity
        $stmt = $pdo->prepare("UPDATE shopping_cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$quantity, $cartItemId]);
        
        return ['success' => true, 'message' => 'Cart item quantity updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error updating cart item: ' . $e->getMessage()];
    }
}

/**
 * Remove an item from the cart
 * 
 * @param int $cartItemId The cart item ID
 * @return array Result with status and message
 */
function removeFromCart($cartItemId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    // Validate input
    if (!$cartItemId) {
        return ['success' => false, 'message' => 'Invalid cart item ID'];
    }
    
    // Get session ID and user ID for security check
    $sessionId = session_id();
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    try {
        // Verify that the cart item belongs to this user/session
        if ($userId) {
            $stmt = $pdo->prepare("DELETE FROM shopping_cart_items WHERE id = ? AND (user_id = ? OR session_id = ?)");
            $stmt->execute([$cartItemId, $userId, $sessionId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM shopping_cart_items WHERE id = ? AND session_id = ?");
            $stmt->execute([$cartItemId, $sessionId]);
        }
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Item removed from cart'];
        } else {
            return ['success' => false, 'message' => 'Cart item not found or access denied'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error removing item from cart: ' . $e->getMessage()];
    }
}

/**
 * Get the total number of items in the cart
 * 
 * @return int The number of items in the cart
 */
function getCartItemCount() {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    // Get session ID
    $sessionId = session_id();
    
    // Get user ID if logged in
    $userId = null;
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    try {
        // Prepare query conditions based on session or user
        if ($userId) {
            // For logged in users, get items from both their user_id and their current session
            $stmt = $pdo->prepare("
                SELECT SUM(quantity) as total
                FROM shopping_cart_items
                WHERE user_id = ? OR session_id = ?
            ");
            $stmt->execute([$userId, $sessionId]);
        } else {
            // For guests, only get items from their session
            $stmt = $pdo->prepare("
                SELECT SUM(quantity) as total
                FROM shopping_cart_items
                WHERE session_id = ?
            ");
            $stmt->execute([$sessionId]);
        }
        
        $result = $stmt->fetch();
        return $result['total'] ? intval($result['total']) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Create a new order
 * 
 * @param int|null $userId User ID if logged in
 * @param string $sessionId Session ID
 * @param string $paymentMethod Payment method
 * @param float $subtotal Order subtotal
 * @param float $shippingCost Shipping cost
 * @param float $total Order total
 * @param string $notes Order notes
 * @param float $gstAmount GST amount
 * @return array Array with success status and order ID or error message
 */
function createOrder($userId, $sessionId, $paymentMethod, $subtotal, $shippingCost, $total, $notes = '', $gstAmount = 0) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Database connection error'
        ];
    }
    
    try {
        // Generate unique order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_number, user_id, session_id, payment_method, 
                subtotal, shipping_cost, gst_amount, total, notes
            ) VALUES (
                :order_number, :user_id, :session_id, :payment_method, 
                :subtotal, :shipping_cost, :gst_amount, :total, :notes
            )
        ");
        
        $stmt->execute([
            ':order_number' => $orderNumber,
            ':user_id' => $userId,
            ':session_id' => $sessionId,
            ':payment_method' => $paymentMethod,
            ':subtotal' => $subtotal,
            ':shipping_cost' => $shippingCost,
            ':gst_amount' => $gstAmount,
            ':total' => $total,
            ':notes' => $notes
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Get cart items
        $cartItems = getCartItems();
        
        // Insert order items
        foreach ($cartItems as $item) {
            $itemSubtotal = $item['amount'] * $item['quantity'];
            
            $stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, product_id, quantity, price, subtotal
                ) VALUES (
                    :order_id, :product_id, :quantity, :price, :subtotal
                )
            ");
            
            $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['product_id'],
                ':quantity' => $item['quantity'],
                ':price' => $item['amount'],
                ':subtotal' => $itemSubtotal
            ]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        return [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        
        return [
            'success' => false,
            'message' => 'Failed to create order: ' . $e->getMessage()
        ];
    }
}

/**
 * Add billing details for an order
 * 
 * @param int $orderId Order ID
 * @param array $billingData Billing details
 * @return array Array with success status and billing ID or error message
 */
function addBillingDetails($orderId, $billingData) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Database connection error'
        ];
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO billing_details (
                order_id, first_name, last_name, company_name, country, 
                street_address_1, street_address_2, city, state, postcode, 
                phone, email
            ) VALUES (
                :order_id, :first_name, :last_name, :company_name, :country, 
                :street_address_1, :street_address_2, :city, :state, :postcode, 
                :phone, :email
            )
        ");
        
        $stmt->execute([
            ':order_id' => $orderId,
            ':first_name' => $billingData['first_name'],
            ':last_name' => $billingData['last_name'],
            ':company_name' => $billingData['company_name'] ?? null,
            ':country' => $billingData['country'],
            ':street_address_1' => $billingData['street_address_1'],
            ':street_address_2' => $billingData['street_address_2'] ?? null,
            ':city' => $billingData['city'],
            ':state' => $billingData['state'],
            ':postcode' => $billingData['postcode'],
            ':phone' => $billingData['phone'],
            ':email' => $billingData['email']
        ]);
        
        $billingId = $pdo->lastInsertId();
        
        return [
            'success' => true,
            'billing_id' => $billingId
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Failed to add billing details: ' . $e->getMessage()
        ];
    }
}

/**
 * Record a payment transaction for an order
 * 
 * @param int $orderId Order ID
 * @param string|null $transactionId Transaction ID from payment gateway
 * @param string $paymentMethod Payment method
 * @param float $amount Payment amount
 * @param string $status Payment status
 * @param array|null $gatewayResponse Response from payment gateway
 * @return array Array with success status and transaction ID or error message
 */
function recordPaymentTransaction($orderId, $transactionId, $paymentMethod, $amount, $status, $gatewayResponse = null) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Database connection error'
        ];
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO payment_transactions (
                order_id, transaction_id, payment_method, 
                amount, status, gateway_response
            ) VALUES (
                :order_id, :transaction_id, :payment_method, 
                :amount, :status, :gateway_response
            )
        ");
        
        $stmt->execute([
            ':order_id' => $orderId,
            ':transaction_id' => $transactionId,
            ':payment_method' => $paymentMethod,
            ':amount' => $amount,
            ':status' => $status,
            ':gateway_response' => $gatewayResponse ? json_encode($gatewayResponse) : null
        ]);
        
        $transactionId = $pdo->lastInsertId();
        
        // Update order payment status
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = :payment_status 
            WHERE id = :order_id
        ");
        
        $stmt->execute([
            ':payment_status' => $status,
            ':order_id' => $orderId
        ]);
        
        return [
            'success' => true,
            'transaction_id' => $transactionId
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Failed to record payment transaction: ' . $e->getMessage()
        ];
    }
}

/**
 * Get order details by ID
 * 
 * @param int $orderId Order ID
 * @return array|false Order details or false on error
 */
function getOrderById($orderId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log('Database connection error in getOrderById');
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM orders WHERE id = :order_id
        ");
        
        $stmt->execute([':order_id' => $orderId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Error getting order: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get order items by order ID
 * 
 * @param int $orderId Order ID
 * @return array Order items
 */
function getOrderItems($orderId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log('Database connection error in getOrderItems');
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT oi.*, p.title, pi.image_path 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN (
                SELECT product_id, MAX(image_path) as image_path 
                FROM product_images 
                WHERE is_primary = 1
                GROUP BY product_id
            ) pi ON p.id = pi.product_id
            WHERE oi.order_id = :order_id
        ");
        
        $stmt->execute([':order_id' => $orderId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Error getting order items: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get billing details by order ID
 * 
 * @param int $orderId Order ID
 * @return array|false Billing details or false on error
 */
function getBillingDetailsByOrderId($orderId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log('Database connection error in getBillingDetailsByOrderId');
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM billing_details WHERE order_id = :order_id
        ");
        
        $stmt->execute([':order_id' => $orderId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Error getting billing details: ' . $e->getMessage());
        return false;
    }
}

/**
 * Clear cart after successful order
 */
function clearCart() {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log('Database connection error in clearCart');
        return false;
    }
    
    $sessionId = session_id();
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    try {
        if ($userId) {
            $stmt = $pdo->prepare("
                DELETE FROM shopping_cart_items 
                WHERE user_id = :user_id OR session_id = :session_id
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':session_id' => $sessionId
            ]);
        } else {
            $stmt = $pdo->prepare("
                DELETE FROM shopping_cart_items 
                WHERE session_id = :session_id
            ");
            $stmt->execute([':session_id' => $sessionId]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Error clearing cart: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get all orders
 * 
 * @param array $filters Optional filters for orders
 * @return array List of all orders
 */
function getAllOrders($filters = []) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log('Database connection error in getAllOrders');
        return [];
    }
    
    try {
        $sql = "SELECT o.*, 
                u.username as user_username,
                u.email as user_email,
                bd.first_name, bd.last_name, bd.phone, bd.email as billing_email,
                pt.transaction_id, pt.status as payment_status
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN billing_details bd ON o.id = bd.order_id
        LEFT JOIN (
            SELECT order_id, transaction_id, status
            FROM payment_transactions 
            ORDER BY created_at DESC
        ) pt ON o.id = pt.order_id
        WHERE 1=1";
        
        $params = [];
        
        // Filter by order number
        if (!empty($filters['order_number'])) {
            $sql .= " AND o.order_number LIKE :order_number";
            $params[':order_number'] = '%' . $filters['order_number'] . '%';
        }
        
        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Filter by payment status
        if (!empty($filters['payment_status'])) {
            $sql .= " AND pt.status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }
        
        // Filter by date range
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(o.created_at) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(o.created_at) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log the error
        error_log("Error getting orders: " . $e->getMessage());
        return [];
    }
}

/**
 * Get order billing details by order ID
 * 
 * @param int $orderId Order ID
 * @return array|false Billing details or false on error
 */
function getOrderBillingDetails($orderId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log('Database connection error in getOrderBillingDetails');
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM billing_details WHERE order_id = :order_id
        ");
        
        $stmt->execute([':order_id' => $orderId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Error getting billing details: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get order payment details by order ID
 * 
 * @param int $orderId Order ID
 * @return array Payment transaction details
 */
function getOrderPaymentDetails($orderId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log('Database connection error in getOrderPaymentDetails');
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM payment_transactions WHERE order_id = :order_id
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([':order_id' => $orderId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Error getting payment details: ' . $e->getMessage());
        return [];
    }
}

/**
 * Update order status
 * 
 * @param int $orderId Order ID
 * @param string $status New status
 * @return array Result with success status and message
 */
function updateOrderStatus($orderId, $status) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Database connection error'
        ];
    }
    
    // Validate status
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    if (!in_array($status, $validStatuses)) {
        return [
            'success' => false,
            'message' => 'Invalid status value'
        ];
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update order status
        $stmt = $pdo->prepare("
            UPDATE orders SET status = :status, updated_at = NOW()
            WHERE id = :order_id
        ");
        
        $stmt->execute([
            ':status' => $status,
            ':order_id' => $orderId
        ]);
        
        // For COD orders: If order status is set to "delivered", update payment status to "completed"
        if ($status == 'delivered') {
            // Get order payment method to check if it's COD
            $stmt = $pdo->prepare("
                SELECT payment_method FROM orders WHERE id = :order_id
            ");
            $stmt->execute([':order_id' => $orderId]);
            $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If payment method is COD (case-insensitive check)
            if ($orderData && (
                strtolower($orderData['payment_method']) == 'cod' || 
                strtolower($orderData['payment_method']) == 'cash on delivery'
            )) {
                // Update payment status to completed
                $stmt = $pdo->prepare("
                    UPDATE orders SET payment_status = 'completed', updated_at = NOW()
                    WHERE id = :order_id
                ");
                $stmt->execute([':order_id' => $orderId]);
                
                // Also add a payment transaction record if none exists
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM payment_transactions WHERE order_id = :order_id
                ");
                $stmt->execute([':order_id' => $orderId]);
                $hasTransaction = $stmt->fetchColumn() > 0;
                
                if (!$hasTransaction) {
                    // Get order amount
                    $stmt = $pdo->prepare("
                        SELECT total, currency FROM orders WHERE id = :order_id
                    ");
                    $stmt->execute([':order_id' => $orderId]);
                    $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($orderInfo) {
                        $stmt = $pdo->prepare("
                            INSERT INTO payment_transactions (
                                order_id, transaction_id, payment_method, 
                                amount, currency, status, gateway_response
                            ) VALUES (
                                :order_id, :transaction_id, :payment_method, 
                                :amount, :currency, :status, :gateway_response
                            )
                        ");
                        
                        $transactionId = 'COD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                        
                        $stmt->execute([
                            ':order_id' => $orderId,
                            ':transaction_id' => $transactionId,
                            ':payment_method' => 'Cash on Delivery',
                            ':amount' => $orderInfo['total'],
                            ':currency' => $orderInfo['currency'] ?? 'INR',
                            ':status' => 'completed',
                            ':gateway_response' => json_encode(['message' => 'Payment received on delivery'])
                        ]);
                    }
                } else {
                    // Update existing payment transaction to completed
                    $stmt = $pdo->prepare("
                        UPDATE payment_transactions 
                        SET status = 'completed', gateway_response = :gateway_response
                        WHERE order_id = :order_id
                    ");
                    
                    $stmt->execute([
                        ':order_id' => $orderId,
                        ':gateway_response' => json_encode(['message' => 'Payment received on delivery', 'updated_at' => date('Y-m-d H:i:s')])
                    ]);
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Order status updated successfully'
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update order status: ' . $e->getMessage()
        ];
    }
}

/**
 * Get order count by status
 * 
 * @param string $status Status to count
 * @return int Number of orders with the given status
 */
function getOrderCountByStatus($status) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM orders 
            WHERE status = :status
        ");
        
        $stmt->execute([':status' => $status]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return intval($result['count']);
        
    } catch (PDOException $e) {
        error_log('Error getting order count by status: ' . $e->getMessage());
        return 0;
    }
}
?> 