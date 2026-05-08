<?php
require_once 'includes/header.php';
require_once 'classes/Project.php';

$pageTitle = "Bosh sahifa";

$project = new Project($pdo);
$recentProjects = $project->getAll(null, '', null);
$recentProjects = array_slice($recentProjects, 0, 6);
?>

<div class="hero">
    <h1>Student Portfolio</h1>
    <p>Talabalarning loyihalari va ishlarini ko'rib chiqing</p>
    <?php if(!isLoggedIn()): ?>
        <div class="hero-buttons">
            <a href="register.php" class="btn btn-primary">Ro'yxatdan o'tish</a>
            <a href="login.php" class="btn btn-secondary">Kirish</a>
        </div>
    <?php else: ?>
        <div class="hero-buttons">
            <a href="dashboard.php" class="btn btn-primary">Kabinetga o'tish</a>
            <a href="projects.php" class="btn btn-secondary">Loyihalarni ko'rish</a>
        </div>
    <?php endif; ?>
</div>

<h2 class="section-title">Oxirgi qo'shilgan loyihalar</h2>

<?php if(empty($recentProjects)): ?>
    <p class="empty-message">Hali loyihalar yo'q. Birinchi bo'lib loyiha qo'shing!</p>
<?php else: ?>
    <div class="projects-grid">
        <?php foreach($recentProjects as $proj): ?>
            <div class="project-card">
                <div class="project-content">
                    <h3><?php echo htmlspecialchars($proj['title']); ?></h3>
                    <p class="project-author"><?php echo htmlspecialchars(formatUserName($proj['author_name'])); ?></p>
                    <?php if($proj['category_name']): ?>
                        <span class="badge"><?php echo htmlspecialchars($proj['category_name']); ?></span>
                    <?php endif; ?>
                    <p class="project-desc"><?php echo shortenText($proj['description'], 100); ?></p>
                    <p class="project-date"><?php echo formatDate($proj['created_at']); ?></p>
                    <a href="projects.php?id=<?php echo $proj['id']; ?>" class="btn btn-small">Batafsil</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center">
        <a href="projects.php" class="btn btn-secondary">Barcha loyihalarni ko'rish</a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
