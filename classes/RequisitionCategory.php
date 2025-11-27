<?php
/**
 * GateWey Requisition Management System
 * Requisition Category Class
 * 
 * File: classes/RequisitionCategory.php
 * Purpose: Manage requisition purpose categories
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
     * Get all active categories
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
    
    /**
     * Create new category
     * 
     * @param array $data Category data
     * @return int|false Category ID on success, false on failure
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (category_name, category_code, description, is_active, display_order) 
                    VALUES (:name, :code, :description, :is_active, :display_order)";
            
            $stmt = $this->db->prepare($sql);
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
                    SET category_name = :name,
                        category_code = :code,
                        description = :description,
                        is_active = :is_active,
                        display_order = :display_order
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
     * Delete category
     * Note: This is a soft delete - it just marks the category as inactive
     * 
     * @param int $id Category ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        try {
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
     * Get total count of categories
     * 
     * @param bool $activeOnly Count only active categories
     * @return int Total count
     */
    public function getCount($activeOnly = false) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            
            if ($activeOnly) {
                $sql .= " WHERE is_active = 1";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting category count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get categories for dropdown select
     * Returns simplified array for form dropdowns
     * 
     * @return array Array of categories [id => name]
     */
    public function getForDropdown() {
        try {
            $sql = "SELECT id, category_name FROM {$this->table} 
                    WHERE is_active = 1 
                    ORDER BY display_order ASC, category_name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $categories = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories[$row['id']] = $row['category_name'];
            }
            
            return $categories;
        } catch (PDOException $e) {
            error_log("Error fetching categories for dropdown: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reorder categories
     * 
     * @param array $order Array of category IDs in new order
     * @return bool True on success, false on failure
     */
    public function reorder($order) {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE {$this->table} SET display_order = :order WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            foreach ($order as $index => $id) {
                $displayOrder = $index + 1;
                $stmt->bindParam(':order', $displayOrder, PDO::PARAM_INT);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error reordering categories: " . $e->getMessage());
            return false;
        }
    }
}