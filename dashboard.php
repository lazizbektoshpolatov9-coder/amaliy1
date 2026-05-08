<?php
require_once 'includes/header.php';
require_once 'classes/User.php';
require_once 'classes/Project.php';
requireLogin();

$pageTitle = "Kabinet";

require_once 'classes/User.php';
$user = new User($pdo);
$userInfo = $user->getInfo($_SESSION['user_id']);

$project = new Project($pdo);
$myProjects = $project->getAll($_SESSION['user_id']);
$totalProjects = $project->countProjects($_SESSION['user_id']);

$isAdmin = ($_SESSION['user_role'] === 'admin');

if($isAdmin) {
    require_once 'classes/AdminUser.php';
    $adminUser = new AdminUser($pdo);
    $stats = $adminUser->getStats();
}
?>

<h1>Xush kelibsiz, <?php echo htmlspecialchars(formatUserName($userInfo['full_name'])); ?>!</h1>

<div class="dashboard-info">
    <div class="info-card">
        <h3>Email</h3>
        <p><?php echo htmlspecialchars($userInfo['email']); ?></p>
    </div>
    <div class="info-card">
        <h3>Rol</h3>
        <p>
            <?php 
            switch($userInfo['role']) {
                case 'admin':
                    echo "Administrator";
                    break;
                case 'user':
                    echo "Foydalanuvchi";
                    break;
                default:
                    echo $userInfo['role'];
            }
            ?>
        </p>
    </div>
    <div class="info-card">
        <h3>Loyihalar soni</h3>
        <p><?php echo $totalProjects; ?> ta</p>
    </div>
    <div class="info-card">
        <h3>Ro'yxatdan o'tgan sana</h3>
        <p><?php echo formatDate($userInfo['created_at']); ?></p>
    </div>
</div>

<?php if($isAdmin): ?>
    <div class="admin-stats">
        <h2>Administrator statistikasi</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total_users']; ?></h3>
                <p>Foydalanuvchilar</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_projects']; ?></h3>
                <p>Loyihalar</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_categories']; ?></h3>
                <p>Kategoriyalar</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<h2 class="section-title">Mening loyihalarim</h2>

<?php if(empty($myProjects)): ?>
    <p class="empty-message">Sizda hali loyihalar yo'q.</p>
    <a href="projects.php?action=add" class="btn btn-primary">Yangi loyiha qo'shish</a>
<?php else: ?>
    <div class="projects-grid">
        <?php foreach($myProjects as $proj): ?>
            <div class="project-card">
                <div class="project-content">
                    <h3><?php echo htmlspecialchars($proj['title']); ?></h3>
                    <?php if($proj['category_name']): ?>
                        <span class="badge"><?php echo htmlspecialchars($proj['category_name']); ?></span>
                    <?php endif; ?>
                    <p class="project-desc"><?php echo shortenText($proj['description'], 100); ?></p>
                    <div class="project-actions">
                        <a href="projects.php?id=<?php echo $proj['id']; ?>" class="btn btn-small">Ko'rish</a>
                        <a href="projects.php?action=edit&id=<?php echo $proj['id']; ?>" class="btn btn-small btn-secondary">Tahrirlash</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
