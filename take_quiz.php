<?php
session_start();
require 'backend/db_connect.php';

// Security: Ensure only students can access this, and they must be logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: auth.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Fetch Quizzes AND check if student has already submitted them
try {
    $stmt = $pdo->prepare("
        SELECT q.id as quiz_id, q.quiz_title, c.class_name,
        (SELECT COUNT(*) FROM quiz_results r WHERE r.quiz_id = q.id AND r.user_id = ?) as attempt_count
        FROM quizzes q
        JOIN classes c ON q.class_id = c.id
        JOIN enrollments e ON c.id = e.class_id
        WHERE e.student_id = ?
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    $quizzes = $stmt->fetchAll();
} catch (PDOException $e) {
    $quizzes = [];
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
            cursor: not-allowed;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="my_classes.php" class="nav-item"><i data-lucide="book-open"></i> My Classes</a>
                <a href="take_quiz.php" class="nav-item active"><i data-lucide="pen-tool"></i> Take Quiz</a>
                <a href="leaderboard.php" class="nav-item"><i data-lucide="trophy"></i> Leaderboard</a>
                <a href="backend/logout.php" class="nav-item logout-link"><i data-lucide="log-out"></i> Logout</a>
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
                    <div class="card" style="grid-column: span 2; text-align: center;">
                        <i data-lucide="info" style="width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                        <h3>No Quizzes Available</h3>
                        <p style="color: var(--text-dim);">Join a class or wait for your teacher to assign a quiz.</p>
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
                                        <i data-lucide="lock" style="width: 16px;"></i> Attempt Finished
                                    </div>
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

    <script>
        lucide.createIcons();
    </script>
</body>
</html>