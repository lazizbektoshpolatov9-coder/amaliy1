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
        $email = htmlspecialchars(trim($_POST['email']));
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        if(empty($email) || empty($password)) {
            $error = "Email va parolni kiriting";
        } else {
            require_once 'classes/User.php';
            $user = new User($pdo);
            $result = $user->login($email, $password, $remember);
            
            if($result['success']) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
    
    generateCaptcha();
} else {
    generateCaptcha();
    
    if(isset($_COOKIE['user_email']) && isset($_COOKIE['user_remember'])) {
        $savedEmail = htmlspecialchars($_COOKIE['user_email']);
    }
}

$pageTitle = "Kirish";
?>

<h1>Kirish</h1>

<?php if(isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<form method="POST" class="form">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo $savedEmail ?? ''; ?>" required>
    </div>
    
    <div class="form-group">
        <label for="password">Parol</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <div class="form-group checkbox-group">
        <input type="checkbox" id="remember" name="remember" <?php echo isset($savedEmail) ? 'checked' : ''; ?>>
        <label for="remember">Meni eslab qol</label>
    </div>
    
    <div class="form-group captcha-group">
        <label for="captcha"><?php echo $_SESSION['captcha_question']; ?></label>
        <input type="text" id="captcha" name="captcha" required>
        <button type="button" class="btn-refresh" onclick="location.reload();">↻ Yangilash</button>
    </div>
    
    <button type="submit" class="btn btn-primary">Kirish</button>
    
    <p class="form-footer">Akkountingiz yo'qmi? <a href="register.php">Ro'yxatdan o'tish</a></p>
</form>

<?php require_once 'includes/footer.php'; ?>
