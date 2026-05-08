<?php
require_once __DIR__ . '/User.php';

class AdminUser extends User {
    
    public function __construct($pdo) {
        parent::__construct($pdo);
    }
    
    public function deleteAnyProject($projectId) {
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
        if($stmt->execute([$projectId])) {
            return ['success' => true, 'message' => 'Loyiha o\'chirildi'];
        }
        return ['success' => false, 'message' => 'O\'chirishda xatolik'];
    }
    
    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT id, full_name, email, role, created_at FROM users");
        return $stmt->fetchAll();
    }
    
    public function getStats() {
        $stats = [];
        
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch()['total'];
        
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM projects");
        $stats['total_projects'] = $stmt->fetch()['total'];
        
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM categories");
        $stats['total_categories'] = $stmt->fetch()['total'];
        
        return $stats;
    }
}
?>
