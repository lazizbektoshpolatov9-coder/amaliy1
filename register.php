<?php
require_once 'includes/header.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $captcha_attempts = isset($_SESSION['captcha_attempts']) ? $_SESSION['captcha_attempts'] : 0;
    
    if(isset($_SESSION['block_until']) && time() < $_SESSION['block_until']) {
        $error = "Juda ko'p xato! 1 daqiqadan so'ng qayta urinib ko'ring.";
    } elseif(!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik xatosi";
    } elseif(!checkCaptcha($_POST['captcha'] ?? '')) {
        $captcha_attempts++;
        $_SESSION['captcha_attempts'] = $captcha_attempts;
        
        if($captcha_attempts >= 3) {
            $_SESSION['block_until'] = time() + 60;
            $error = "Juda ko'p xato! 1 daqiqadan so'ng qayta urinib ko'ring.";
        } else {
            $error = "Captcha noto'g'ri!";
        }
    } else {
        $_SESSION['captcha_attempts'] = 0;
        $fullName = htmlspecialchars(trim($_POST['full_name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if(empty($fullName) || empty($email) || empty($password)) {
            $error = "Barcha maydonlarni to'ldiring";
        } elseif(strlen($fullName) < 3) {
            $error = "Ism kamida 3 ta belgidan iborat bo'lishi kerak";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email formati noto'g'ri";
        } elseif(strlen($password) < 6) {
            $error = "Parol kamida 6 ta belgidan iborat bo'lishi kerak";
        } elseif($password !== $confirmPassword) {
            $error = "Parollar mos kelmadi";
        } else {
            require_once 'classes/User.php';
            $user = new User($pdo);
            $result = $user->register($fullName, $email, $password);
            
            if($result['success']) {
                $_SESSION['success'] = $result['message'];
                header('Location: login.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
    
    generateCaptcha();
} else {
    generateCaptcha();
}

$pageTitle = "Ro'yxatdan o'tish";
?>

<h1>Ro'yxatdan o'tish</h1>

<?php if(isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" class="form">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="form-group">
        <label for="full_name">To'liq ism</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="password">Parol</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <div class="form-group">
        <label for="confirm_password">Parolni tasdiqlang</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    
    <div class="form-group captcha-group">
        <label for="captcha"><?php echo $_SESSION['captcha_question']; ?></label>
        <input type="text" id="captcha" name="captcha" required>
        <button type="button" class="btn-refresh" onclick="location.reload();">↻ Yangilash</button>
    </div>
    
    <button type="submit" class="btn btn-primary">Ro'yxatdan o'tish</button>
    
    <p class="form-footer">Akkountingiz bormi? <a href="login.php">Kirish</a></p>
</form>

<?php require_once 'includes/footer.php'; ?>
