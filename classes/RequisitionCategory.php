<?php
/**
 * GateWey Requisition Management System
 * Requisition Category Class - Enhanced with Parent-Child Support
 * 
 * File: classes/RequisitionCategory.php
 * Purpose: Manage requisition purpose categories with hierarchical structure
 * 
 * UPDATED: Added parent-child category functionality
 * - Parent categories (parent_id IS NULL)
 * - Child categories (parent_id references parent)
 * - Hierarchical queries and tree structure
 */

// Security check
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

class RequisitionCategory {
    private $db;
    private $table = 'requisition_categories';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all active categories (both parent and child)
     * 
     * @return array Array of active categories ordered by display_order
     */
    public function getAllActive() {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE is_active = 1 
                    ORDER BY display_order ASC, category_name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching active categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all categories (active and inactive)
     * 
     * @return array Array of all categories
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    ORDER BY display_order ASC, category_name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get category by ID
     * 
     * @param int $id Category ID
     * @return array|null Category data or null if not found
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching category by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get category by name
     * 
     * @param string $name Category name
     * @return array|null Category data or null if not found
     */
    public function getByName($name) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE category_name = :name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching category by name: " . $e->getMessage());
            return null;
        }
    }
    
    // ============================================
    // PARENT CATEGORY METHODS
    // ============================================
    
    /**
     * Get all parent categories (categories with no parent)
     * 
     * @param bool $activeOnly Get only active parent categories
     * @return array Array of parent categories
     */
    public function getParentCategories($activeOnly = true) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE parent_id IS NULL";
            
            if ($activeOnly) {
                $sql .= " AND is_active = 1";
            }
            
            $sql .= " ORDER BY display_order ASC, category_name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching parent categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get child categories for a specific parent
     * 
     * @param int $parentId Parent category ID
     * @param bool $activeOnly Get only active child categories
     * @return array Array of child categories
     */
    public function getChildCategories($parentId, $activeOnly = true) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE parent_id = :parent_id";
            
            if ($activeOnly) {
                $sql .= " AND is_active = 1";
            }
            
            $sql .= " ORDER BY display_order ASC, category_name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching child categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get complete category hierarchy (parent with children)
     * 
     * @param bool $activeOnly Get only active categories
     * @return array Hierarchical array of categories
     */
    public function getCategoryHierarchy($activeOnly = true) {
        try {
            // Get all parent categories
            $parents = $this->getParentCategories($activeOnly);
            
            // For each parent, get its children
            foreach ($parents as &$parent) {
                $parent['children'] = $this->getChildCategories($parent['id'], $activeOnly);
                $parent['child_count'] = count($parent['children']);
            }
            
            return $parents;
        } catch (Exception $e) {
            error_log("Error fetching category hierarchy: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if a category has children
     * 
     * @param int $categoryId Category ID
     * @return bool True if has children, false otherwise
     */
    public function hasChildren($categoryId) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE parent_id = :category_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking if category has children: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count child categories for a parent
     * 
     * @param int $parentId Parent category ID
     * @param bool $activeOnly Count only active children
     * @return int Number of child categories
     */
    public function countChildren($parentId, $activeOnly = true) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE parent_id = :parent_id";
            
            if ($activeOnly) {
                $sql .= " AND is_active = 1";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting child categories: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get parent category of a child
     * 
     * @param int $childId Child category ID
     * @return array|null Parent category data or null
     */
    public function getParentCategory($childId) {
        try {
            $sql = "SELECT p.* FROM {$this->table} p
                    INNER JOIN {$this->table} c ON c.parent_id = p.id
                    WHERE c.id = :child_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':child_id', $childId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching parent category: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if category is a parent (has no parent_id)
     * 
     * @param int $categoryId Category ID
     * @return bool True if parent, false otherwise
     */
    public function isParentCategory($categoryId) {
        try {
            $category = $this->getById($categoryId);
            return $category && $category['parent_id'] === null;
        } catch (Exception $e) {
            error_log("Error checking if category is parent: " . $e->getMessage());
            return false;
        }
    }
    
    // ============================================
    // CRUD OPERATIONS (UPDATED)
    // ============================================
    
    /**
     * Create new category
     * 
     * @param array $data Category data
     * @return int|false Category ID on success, false on failure
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (parent_id, category_name, category_code, description, is_active, display_order) 
                    VALUES (:parent_id, :name, :code, :description, :is_active, :display_order)";
            
            $stmt = $this->db->prepare($sql);
            
            // Handle parent_id (can be NULL for parent categories)
            $parentId = !empty($data['parent_id']) ? $data['parent_id'] : null;
            $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
            
            $stmt->bindParam(':name', $data['category_name'], PDO::PARAM_STR);
            $stmt->bindParam(':code', $data['category_code'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);
            $stmt->bindParam(':display_order', $data['display_order'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creating category: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update category
     * 
     * @param int $id Category ID
     * @param array $data Category data
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET parent_id = :parent_id,
                        category_name = :name,
                        category_code = :code,
                        description = :description,
                        is_active = :is_active,
                        display_order = :display_order
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            // Handle parent_id (can be NULL for parent categories)
            $parentId = !empty($data['parent_id']) ? $data['parent_id'] : null;
            $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
            
            $stmt->bindParam(':name', $data['category_name'], PDO::PARAM_STR);
            $stmt->bindParam(':code', $data['category_code'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);
            $stmt->bindParam(':display_order', $data['display_order'], PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating category: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toggle category active status
     * 
     * @param int $id Category ID
     * @return bool True on success, false on failure
     */
    public function toggleActive($id) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET is_active = NOT is_active 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error toggling category status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete category (soft delete - marks as inactive)
     * Note: Cascade deletes will remove children automatically via database constraint
     * 
     * @param int $id Category ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        try {
            // Check if has children
            if ($this->hasChildren($id)) {
                // You may want to handle this differently - either prevent deletion
                // or cascade soft delete to children
                error_log("Cannot delete category with children: ID {$id}");
                return false;
            }
            
            $sql = "UPDATE {$this->table} 
                    SET is_active = 0 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hard delete category (permanently removes from database)
     * WARNING: This will cascade delete all children due to database constraint
     * 
     * @param int $id Category ID
     * @return bool True on success, false on failure
     */
    public function hardDelete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error hard deleting category: " . $e->getMessage());
            return false;
        }
    }
    
    // ============================================
    // VALIDATION METHODS
    // ============================================
    
    /**
     * Check if category name exists
     * 
     * @param string $name Category name
     * @param int|null $excludeId Exclude this ID from check (for updates)
     * @return bool True if exists, false otherwise
     */
    public function nameExists($name, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE category_name = :name";
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            
            if ($excludeId) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking category name: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if category code exists
     * 
     * @param string $code Category code
     * @param int|null $excludeId Exclude this ID from check (for updates)
     * @return bool True if exists, false otherwise
     */
    public function codeExists($code, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE category_code = :code";
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            
            if ($excludeId) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking category code: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate parent-child relationship (prevent circular reference)
     * 
     * @param int $categoryId Current category ID
     * @param int $newParentId Proposed parent ID
     * @return bool True if valid, false if circular reference detected
     */
    public function isValidParentChildRelationship($categoryId, $newParentId) {
        // Cannot be its own parent
        if ($categoryId == $newParentId) {
            return false;
        }
        
        // Check if the new parent is actually a descendant of this category
        // This would create a circular reference
        $parent = $this->getById($newParentId);
        
        while ($parent && $parent['parent_id']) {
            if ($parent['parent_id'] == $categoryId) {
                return false; // Circular reference detected
            }
            $parent = $this->getById($parent['parent_id']);
        }
        
        return true;
    }
    
    // ============================================
    // STATISTICS & REPORTING
    // ============================================
    
    /**
     * Get category statistics
     * 
     * @return array Statistics about categories
     */
    public function getStatistics() {
        try {
            $stats = [
                'total_categories' => 0,
                'active_categories' => 0,
                'inactive_categories' => 0,
                'parent_categories' => 0,
                'child_categories' => 0
            ];
            
            // Total categories
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->db->query($sql);
            $stats['total_categories'] = (int)$stmt->fetchColumn();
            
            // Active categories
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE is_active = 1";
            $stmt = $this->db->query($sql);
            $stats['active_categories'] = (int)$stmt->fetchColumn();
            
            // Inactive categories
            $stats['inactive_categories'] = $stats['total_categories'] - $stats['active_categories'];
            
            // Parent categories
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE parent_id IS NULL";
            $stmt = $this->db->query($sql);
            $stats['parent_categories'] = (int)$stmt->fetchColumn();
            
            // Child categories
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE parent_id IS NOT NULL";
            $stmt = $this->db->query($sql);
            $stats['child_categories'] = (int)$stmt->fetchColumn();
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error fetching category statistics: " . $e->getMessage());
            return [
                'total_categories' => 0,
                'active_categories' => 0,
                'inactive_categories' => 0,
                'parent_categories' => 0,
                'child_categories' => 0
            ];
        }
    }
}