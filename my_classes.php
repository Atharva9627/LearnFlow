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

// ADDED: Detect if a class is currently selected from the URL
$current_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;

// Fetch classes based on role
try {
    // FIX: Using $conn to match your standardized connection
    if ($userRole === 'teacher') {
        $stmt = $conn->prepare("SELECT * FROM classes WHERE teacher_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
    } else {
        $stmt = $conn->prepare("SELECT c.* FROM classes c 
                                JOIN enrollments e ON c.id = e.class_id 
                                WHERE e.student_id = ? ORDER BY e.joined_at DESC");
        $stmt->execute([$userId]);
    }
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <style>
        /* Floating Plus Button Styling */
        .add-class-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
            text-decoration: none;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .add-class-btn:hover {
            transform: scale(1.1) rotate(90deg);
            background: #4f46e5;
        }

        .add-class-btn i {
            width: 32px;
            height: 32px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item">
                    <i data-lucide="layout-dashboard"></i> Dashboard
                </a>
                
                <a href="my_classes.php" class="nav-item <?= !isset($current_class_id) ? 'active' : '' ?>">
                    <i data-lucide="book-open"></i> My Classes
                </a>

                <?php if ($current_class_id): ?>
                    <div class="sidebar-divider" style="margin: 15px 0; border-top: 1px solid var(--glass-border);"></div>
                    <p style="padding-left: 20px; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase;">Class Options</p>
                    
                    <?php if ($userRole === 'teacher'): ?>
                        <a href="create_quiz.php?class_id=<?= $current_class_id ?>" class="nav-item">
                            <i data-lucide="plus-square"></i> Give Quiz
                        </a>
                        <a href="assignments.php?class_id=<?= $current_class_id ?>" class="nav-item">
                            <i data-lucide="clipboard-list"></i> Assignments
                        </a>
                        <a href="gradebook.php?class_id=<?= $current_class_id ?>" class="nav-item">
                            <i data-lucide="bar-chart-3"></i> Gradebook
                        </a>
                        <a href="leaderboard.php?class_id=<?= $current_class_id ?>" class="nav-item">
                            <i data-lucide="trophy"></i> Leaderboard
                        </a>
                    <?php else: ?>
                        <a href="take_quiz.php?class_id=<?= $current_class_id ?>" class="nav-item">
                            <i data-lucide="pen-tool"></i> Take Quiz
                        </a>
                        <a href="leaderboard.php?class_id=<?= $current_class_id ?>" class="nav-item">
                            <i data-lucide="trophy"></i> Leaderboard
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="backend/logout.php" class="nav-item logout-link" style="margin-top: auto;">
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
                    <div class="card" style="grid-column: span 2; text-align: center;">
                        <p>No classes found. <?php echo ($userRole === 'teacher') ? 'Click the + button to create your first class!' : 'Join one using a code!'; ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <div class="card">
                            <div class="card-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <i data-lucide="folder" style="color: var(--primary);"></i>
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                            </div>
                            <p style="color: var(--text-dim); margin-bottom: 15px;">
                                <?php if ($userRole === 'teacher'): ?>
                                    Invite Code: <strong style="color: #22c55e; letter-spacing: 1px;"><?php echo $class['invite_code']; ?></strong>
                                <?php else: ?>
                                    Status: <span style="color: #22c55e;">Enrolled</span>
                                <?php endif; ?>
                            </p>
                            <a href="my_classes.php?class_id=<?php echo $class['id']; ?>" class="btn-primary" style="display: block; text-align: center; text-decoration: none;">Enter Class</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <?php if ($userRole === 'teacher'): ?>
            <a href="create_class.php" class="add-class-btn" title="Create New Class">
                <i data-lucide="plus"></i>
            </a>
        <?php endif; ?>

    </div>
    <script>lucide.createIcons();</script>
</body>
</html>