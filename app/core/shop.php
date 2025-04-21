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
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM brands ORDER BY name");
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
    global $pdo;
    
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
 * @return array Result with status and message
 */
function addBrand($name, $image) {
    global $pdo;
    
    // Validate input
    if (empty($name) || empty($image)) {
        return ['success' => false, 'message' => 'Brand name and image are required'];
    }
    
    try {
        // Check if brand already exists
        $stmt = $pdo->prepare("SELECT * FROM brands WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Brand already exists'];
        }
        
        // Insert new brand
        $stmt = $pdo->prepare("INSERT INTO brands (name, image) VALUES (?, ?)");
        $stmt->execute([$name, $image]);
        
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
 * @return array Result with status and message
 */
function updateBrand($brandId, $name, $image = null) {
    global $pdo;
    
    // Validate input
    if (empty($brandId) || empty($name)) {
        return ['success' => false, 'message' => 'Brand ID and name are required'];
    }
    
    try {
        // If image is provided, update both name and image
        if ($image) {
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $image, $brandId]);
        } else {
            // Otherwise, update only name
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
    global $pdo;
    
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
    global $pdo;
    
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
    global $pdo;
    
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
    global $pdo;
    
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
 * @return array Result with status and message
 */
function addProduct($brandId, $title, $amount, $description, $makeId = null, $modelId = null, $seriesId = null) {
    global $pdo;
    
    // Validate input
    if (empty($brandId) || empty($title) || empty($amount) || empty($description)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Insert new product
        $stmt = $pdo->prepare("INSERT INTO products (brand_id, title, amount, description, make_id, model_id, series_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$brandId, $title, $amount, $description, $makeId, $modelId, $seriesId]);
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
        $pdo->rollBack();
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
 * @return array Result with status and message
 */
function updateProduct($productId, $brandId, $title, $amount, $description, $makeId = null, $modelId = null, $seriesId = null) {
    global $pdo;
    
    // Validate input
    if (empty($productId) || empty($brandId) || empty($title) || empty($amount) || empty($description)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET brand_id = ?, title = ?, amount = ?, description = ?, make_id = ?, model_id = ?, series_id = ? 
            WHERE id = ?
        ");
        $stmt->execute([$brandId, $title, $amount, $description, $makeId, $modelId, $seriesId, $productId]);
        
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
    global $pdo;
    
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
        $pdo->rollBack();
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
    global $pdo;
    
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
        $pdo->rollBack();
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
    global $pdo;
    
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
    global $pdo;
    
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
        $pdo->rollBack();
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
    global $pdo;
    
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
 * @return array List of all categories
 */
function getAllCategories() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
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
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
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
 * @param string $description Category description (optional)
 * @return array Result with status and message
 */
function addCategory($name, $description = '') {
    global $pdo;
    
    // Validate input
    if (empty($name)) {
        return ['success' => false, 'message' => 'Category name is required'];
    }
    
    try {
        // Check if category already exists
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Category already exists'];
        }
        
        // Insert new category
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        
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
 * @param string $description Category description (optional)
 * @return array Result with status and message
 */
function updateCategory($categoryId, $name, $description = '') {
    global $pdo;
    
    // Validate input
    if (empty($categoryId) || empty($name)) {
        return ['success' => false, 'message' => 'Category ID and name are required'];
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $categoryId]);
        
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
    global $pdo;
    
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
    global $pdo;
    
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
    global $pdo;
    
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
    global $pdo;
    
    if (empty($productId)) {
        return ['success' => false, 'message' => 'Product ID is required'];
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
        $pdo->rollBack();
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
    global $pdo;
    
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
?> 