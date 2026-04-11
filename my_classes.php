<?php
session_start();
require 'backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$userName = $_SESSION['user_name'];

// Fetch classes based on role
try {
    if ($userRole === 'teacher') {
        $stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT c.* FROM classes c 
                               JOIN enrollments e ON c.id = e.class_id 
                               WHERE e.student_id = ? ORDER BY e.joined_at DESC");
        $stmt->execute([$userId]);
    }
    $classes = $stmt->fetchAll();
} catch (PDOException $e) {
    $classes = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Classes | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
    <a href="index.php" class="nav-item">
        <i data-lucide="layout-dashboard"></i> Dashboard
    </a>
    
    <a href="my_classes.php" class="nav-item active">
        <i data-lucide="book-open"></i> My Classes
    </a>
    
    <?php if ($userRole === 'teacher'): ?>
        <a href="assignments.php" class="nav-item"><i data-lucide="clipboard-list"></i> Assignments</a>
        <a href="gradebook.php" class="nav-item"><i data-lucide="bar-chart-3"></i> Gradebook</a>
    <?php else: ?>
        <a href="take_quiz.php" class="nav-item"><i data-lucide="pen-tool"></i> Take Quiz</a>
        <a href="leaderboard.php" class="nav-item"><i data-lucide="trophy"></i> Leaderboard</a>
    <?php endif; ?>

    <a href="backend/logout.php" class="nav-item logout-link">
        <i data-lucide="log-out"></i> Logout
    </a>
</nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>My Classes</h1>
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <div class="avatar-circle"></div>
                </div>
            </header>

            <div class="dashboard-grid">
                <?php if (empty($classes)): ?>
                    <div class="card">
                        <p>No classes found. <?php echo ($userRole === 'teacher') ? 'Create one on the dashboard!' : 'Join one using a code!'; ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <div class="card">
                            <div class="card-header">
                                <i data-lucide="folder" style="color: var(--primary);"></i>
                                <h3><?php echo htmlspecialchars($class['class_name']); ?></h3>
                            </div>
                            <p style="color: var(--text-dim); margin-bottom: 15px;">
                                <?php if ($userRole === 'teacher'): ?>
                                    Invite Code: <strong style="color: #22c55e;"><?php echo $class['invite_code']; ?></strong>
                                <?php else: ?>
                                    Class Enrolled
                                <?php endif; ?>
                            </p>
                            <button class="btn-primary">View Materials</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>