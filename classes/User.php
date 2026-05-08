<?php
class User {
    private $id;
    private $full_name;
    private $email;
    private $password;
    private $role;
    protected $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function register($full_name, $email, $password, $role = 'user') {
        $checkEmail = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if($checkEmail->fetch()) {
            return ['success' => false, 'message' => 'Bu email allaqachon mavjud'];
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        
        if($stmt->execute([$full_name, $email, $hashedPassword, $role])) {
            return ['success' => true, 'message' => 'Muvaffaqiyatli ro\'yxatdan o\'tdingiz'];
        }
        
        return ['success' => false, 'message' => 'Xatolik yuz berdi'];
    }
    
    public function login($email, $password, $remember = false) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            if($remember) {
                setcookie('user_email', $email, time() + (7 * 24 * 60 * 60), '/');
                setcookie('user_remember', '1', time() + (7 * 24 * 60 * 60), '/');
            }
            
            return ['success' => true, 'message' => 'Xush kelibsiz'];
        }
        
        return ['success' => false, 'message' => 'Email yoki parol noto\'g\'ri'];
    }
    
    public function getInfo($userId) {
        $stmt = $this->pdo->prepare("SELECT id, full_name, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }
    public function setFullName($name) { $this->full_name = $name; }
    public function getFullName() { return $this->full_name; }
    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }
    public function setRole($role) { $this->role = $role; }
    public function getRole() { return $this->role; }
}
?>
