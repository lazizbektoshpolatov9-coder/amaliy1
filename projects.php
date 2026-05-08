<?php
require_once 'includes/header.php';

$pageTitle = "Loyihalar";
$action = $_GET['action'] ?? 'list';
$projectId = $_GET['id'] ?? null;

require_once 'classes/Project.php';
$projectObj = new Project($pdo);

if($action === 'add' || $action === 'edit') {
    requireLogin();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    if(!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik xatosi";
    } else {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $github_link = trim($_POST['github_link']);
        $demo_link = trim($_POST['demo_link']);
        $status = $_POST['status'] ?? 'published';
        
        if(empty($title)) {
            $error = "Loyiha nomini kiriting";
        } elseif(strlen($title) < 3) {
            $error = "Loyiha nomi kamida 3 ta belgidan iborat bo'lishi kerak";
        } else {
            $data = [
                'title' => $title,
                'description' => $description,
                'category_id' => $category_id,
                'github_link' => $github_link,
                'demo_link' => $demo_link,
                'status' => $status
            ];
            
            if($action === 'edit' && $projectId) {
                $data['project_id'] = $projectId;
            }
            
            $result = $projectObj->save($data, $_SESSION['user_id']);
            
            if($result['success']) {
                $_SESSION['success'] = $result['message'];
                header('Location: projects.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    requireLogin();
    if(!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik xatosi";
    } else {
        $projectId = intval($_POST['project_id']);
        
        $isAdmin = ($_SESSION['user_role'] === 'admin');
        
        if($isAdmin) {
            require_once 'classes/AdminUser.php';
            $adminUser = new AdminUser($pdo);
            $result = $adminUser->deleteAnyProject($projectId);
        } else {
            $result = $projectObj->delete($projectId, $_SESSION['user_id']);
        }
        
        if($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $error = $result['message'];
        }
        header('Location: projects.php');
        exit;
    }
}

$search = $_GET['search'] ?? '';
$categoryId = !empty($_GET['category']) ? intval($_GET['category']) : null;

$projects = $projectObj->getAll(null, $search, $categoryId);

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$editProject = null;
if($action === 'edit' && $projectId) {
    $editProject = $projectObj->getById($projectId);
    if(!$editProject || ($editProject['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin')) {
        header('Location: projects.php');
        exit;
    }
}

$singleProject = null;
if($action === 'list' && $projectId) {
    $singleProject = $projectObj->getById($projectId);
}
?>

<?php if($action === 'list' && !$projectId): ?>
    <h1>Loyihalar</h1>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Qidirish..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="">Barcha kategoriyalar</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Qidirish</button>
        <?php if(isLoggedIn()): ?>
            <a href="projects.php?action=add" class="btn btn-secondary">Yangi loyiha</a>
        <?php endif; ?>
    </form>
    
    <?php if(empty($projects)): ?>
        <p class="empty-message">Loyihalar topilmadi.</p>
    <?php else: ?>
        <p class="results-count"><?php echo count($projects); ?> ta loyiha topildi</p>
        <div class="projects-grid">
            <?php foreach($projects as $proj): ?>
                <div class="project-card">
                    <div class="project-content">
                        <h3><?php echo htmlspecialchars($proj['title']); ?></h3>
                        <p class="project-author"><?php echo htmlspecialchars(formatUserName($proj['author_name'])); ?></p>
                        <?php if($proj['category_name']): ?>
                            <span class="badge"><?php echo htmlspecialchars($proj['category_name']); ?></span>
                        <?php endif; ?>
                        <p class="project-desc"><?php echo shortenText($proj['description'], 120); ?></p>
                        <p class="project-date"><?php echo formatDate($proj['created_at']); ?></p>
                        <div class="project-actions">
                            <a href="projects.php?id=<?php echo $proj['id']; ?>" class="btn btn-small">Ko'rish</a>
                            <?php if(isLoggedIn() && ($proj['user_id'] == $_SESSION['user_id'] || $_SESSION['user_role'] === 'admin')): ?>
                                <a href="projects.php?action=edit&id=<?php echo $proj['id']; ?>" class="btn btn-small btn-secondary">Tahrirlash</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Rostdan ham o\'chirmoqchimisiz?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="project_id" value="<?php echo $proj['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-small btn-danger">O'chirish</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php elseif($singleProject): ?>
    <div class="project-detail">
        <a href="projects.php" class="btn btn-secondary">← Orqaga</a>
        <h1><?php echo htmlspecialchars($singleProject['title']); ?></h1>
        <?php if($singleProject['category_name']): ?>
            <span class="badge"><?php echo htmlspecialchars($singleProject['category_name']); ?></span>
        <?php endif; ?>
        <p class="project-author">Muallif: <?php echo htmlspecialchars(formatUserName($singleProject['author_name'])); ?></p>
        <p class="project-date"><?php echo formatDate($singleProject['created_at']); ?></p>
        <div class="project-full-desc">
            <p><?php echo nl2br(htmlspecialchars($singleProject['description'])); ?></p>
        </div>
        <div class="project-links">
            <?php if($singleProject['github_link']): ?>
                <a href="<?php echo htmlspecialchars($singleProject['github_link']); ?>" target="_blank" class="btn btn-primary">GitHub</a>
            <?php endif; ?>
            <?php if($singleProject['demo_link']): ?>
                <a href="<?php echo htmlspecialchars($singleProject['demo_link']); ?>" target="_blank" class="btn btn-secondary">Demo ko'rish</a>
            <?php endif; ?>
        </div>
    </div>

<?php elseif($action === 'add' || $action === 'edit'): ?>
    <h1><?php echo $action === 'add' ? 'Yangi loyiha qo\'shish' : 'Loyihani tahrirlash'; ?></h1>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-group">
            <label for="title">Loyiha nomi</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($editProject['title'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="category_id">Kategoriya</label>
            <select id="category_id" name="category_id">
                <option value="">Tanlang</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($editProject['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="description">Tavsif</label>
            <textarea id="description" name="description" rows="6"><?php echo htmlspecialchars($editProject['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="github_link">GitHub havolasi</label>
            <input type="url" id="github_link" name="github_link" value="<?php echo htmlspecialchars($editProject['github_link'] ?? ''); ?>" placeholder="https://github.com/...">
        </div>
        
        <div class="form-group">
            <label for="demo_link">Demo havolasi</label>
            <input type="url" id="demo_link" name="demo_link" value="<?php echo htmlspecialchars($editProject['demo_link'] ?? ''); ?>" placeholder="https://...">
        </div>
        
        <div class="form-group">
            <label for="status">Holat</label>
            <select id="status" name="status">
                <option value="published" <?php echo ($editProject['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>E'lon qilingan</option>
                <option value="draft" <?php echo ($editProject['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Qoralama</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo $action === 'add' ? 'Qo\'shish' : 'Saqlash'; ?></button>
        <a href="projects.php" class="btn btn-secondary">Bekor qilish</a>
    </form>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
