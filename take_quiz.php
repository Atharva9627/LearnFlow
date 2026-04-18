<?php
session_start();
// ADD THIS LINE near the top of dashboard.php
$current_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$userRole = $_SESSION['role'] ?? null;
require 'backend/db_connect.php'; // Ensure this matches your connection file

// Security: Ensure only students can access this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: auth.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Fetch Quizzes AND check for existing scores in the quiz_scores table
try {
    // Note: I'm using $conn here as per our previous backend scripts
    $stmt = $conn->prepare("
        SELECT q.id as quiz_id, q.title as quiz_title, c.class_name,
        (SELECT COUNT(*) FROM quiz_scores s WHERE s.quiz_id = q.id AND s.student_id = ?) as attempt_count
        FROM quizzes q
        JOIN classes c ON q.class_id = c.id
        JOIN enrollments e ON c.id = e.class_id
        WHERE e.student_id = ?
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $quizzes = [];
    // echo "Error: " . $e->getMessage(); // Uncomment for debugging
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .btn-completed {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .btn-view-leaderboard {
            display: block;
            text-align: center;
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
            border: 1px solid #fbbf24;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .btn-view-leaderboard:hover { background: #fbbf24; color: #000; }
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
                <h1>Available Quizzes</h1>
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <div class="avatar-circle"></div>
                </div>
            </header>

            <div class="dashboard-grid">
                <?php if (empty($quizzes)): ?>
                    <div class="card" style="grid-column: span 2; text-align: center; padding: 50px;">
                        <i data-lucide="info" style="width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                        <h3>No Quizzes Assigned</h3>
                        <p style="color: var(--text-dim);">Join a class or wait for your teacher to post a quiz.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <div class="card quiz-card">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <span style="font-size: 0.8rem; color: var(--primary); font-weight: 600;">
                                        <?php echo htmlspecialchars($quiz['class_name']); ?>
                                    </span>
                                    <h3 style="margin-top: 5px;"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h3>
                                </div>
                                <?php if ($quiz['attempt_count'] > 0): ?>
                                    <i data-lucide="check-circle" style="color: #10b981;"></i>
                                <?php else: ?>
                                    <i data-lucide="help-circle" class="text-dim"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <?php if ($quiz['attempt_count'] > 0): ?>
                                    <div class="btn-completed">
                                        <i data-lucide="lock" style="width: 16px;"></i> Completed
                                    </div>
                                    <a href="leaderboard.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn-view-leaderboard">
                                        <i data-lucide="bar-chart-2"></i> View Leaderboard
                                    </a>
                                <?php else: ?>
                                    <a href="quiz_view.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn-primary" style="display: block; text-align: center; text-decoration: none;">
                                        Start Quiz
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>