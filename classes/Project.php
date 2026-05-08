<?php
class Project {
    private $id;
    private $user_id;
    private $category_id;
    private $title;
    private $description;
    private $image;
    private $github_link;
    private $demo_link;
    private $status;
    protected $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function save($data, $userId) {
        if(isset($data['project_id']) && !empty($data['project_id'])) {
            return $this->update($data, $userId);
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO projects (user_id, category_id, title, description, github_link, demo_link, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $userId,
            $data['category_id'] ?? null,
            $data['title'],
            $data['description'] ?? '',
            $data['github_link'] ?? '',
            $data['demo_link'] ?? '',
            $data['status'] ?? 'published'
        ]);
        
        if($result) {
            return ['success' => true, 'message' => 'Loyiha qo\'shildi', 'id' => $this->pdo->lastInsertId()];
        }
        return ['success' => false, 'message' => 'Qo\'shishda xatolik'];
    }
    
    private function update($data, $userId) {
        $stmt = $this->pdo->prepare("
            UPDATE projects 
            SET category_id = ?, title = ?, description = ?, github_link = ?, demo_link = ?, status = ?
            WHERE id = ? AND user_id = ?
        ");
        
        $result = $stmt->execute([
            $data['category_id'] ?? null,
            $data['title'],
            $data['description'] ?? '',
            $data['github_link'] ?? '',
            $data['demo_link'] ?? '',
            $data['status'] ?? 'published',
            $data['project_id'],
            $userId
        ]);
        
        if($result) {
            return ['success' => true, 'message' => 'Loyiha yangilandi'];
        }
        return ['success' => false, 'message' => 'Yangilashda xatolik'];
    }
    
    public function delete($projectId, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
        if($stmt->execute([$projectId, $userId])) {
            return ['success' => true, 'message' => 'Loyiha o\'chirildi'];
        }
        return ['success' => false, 'message' => 'O\'chirishda xatolik'];
    }
    
    public function getAll($userId = null, $search = '', $categoryId = null) {
        $sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                FROM projects p 
                LEFT JOIN categories c ON p.category_id = c.id 
                JOIN users u ON p.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if($userId) {
            $sql .= " AND p.user_id = ?";
            $params[] = $userId;
        }
        
        if(!empty($search)) {
            $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getById($projectId) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM projects p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetch();
    }
    
    public function countProjects($userId = null) {
        if($userId) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM projects WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM projects");
        }
        return $stmt->fetch()['total'];
    }
    
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }
    public function setTitle($title) { $this->title = $title; }
    public function getTitle() { return $this->title; }
    public function setDescription($desc) { $this->description = $desc; }
    public function getDescription() { return $this->description; }
    public function setStatus($status) { $this->status = $status; }
    public function getStatus() { return $this->status; }
}
?>
