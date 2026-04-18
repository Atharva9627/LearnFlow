<?php
session_start();
$current_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$userRole = $_SESSION['role'] ?? null;
require 'backend/db_connect.php';

// 1. Security Check: Only Teachers should see this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
// Handle session name safely
$userName = $_SESSION['user_name'] ?? 'Teacher';

// 2. Fetch Results using 'u.name' to match your database
try {
    $stmt = $conn->prepare("
        SELECT u.name, q.quiz_title, r.score, r.total_questions, r.submitted_at 
        FROM quiz_results r
        JOIN users u ON r.user_id = u.id
        JOIN quizzes q ON r.quiz_id = q.id
        WHERE q.teacher_id = ?
        ORDER BY r.submitted_at DESC
    ");
    $stmt->execute([$userId]);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    // If table quiz_results is missing, this will catch it
    $error_msg = "Database Error: " . $e->getMessage();
    $results = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gradebook | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .grade-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #1a1a1a; border-radius: 8px; overflow: hidden; }
        .grade-table th { background: #222; color: #888; padding: 15px; text-align: left; font-size: 0.9rem; }
        .grade-table td { padding: 15px; border-bottom: 1px solid #333; color: white; }
        .score-badge { font-weight: bold; color: #6366f1; }
        .status-pass { color: #10b981; font-weight: 600; }
        .status-fail { color: #ef4444; font-weight: 600; }
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
                <h1>Student Gradebook</h1>
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <div class="avatar" style="width: 35px; height: 35px; background: #333; border-radius: 50%;"></div>
                </div>
            </header>

            <?php if (isset($error_msg)): ?>
                <div class="card" style="border: 1px solid #ef4444; color: #ef4444; padding: 15px; margin-bottom: 20px;">
                    <i data-lucide="alert-triangle"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Recent Submissions</h3>
                    <button onclick="window.print()" class="btn-secondary" style="background: #333; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
                        Print Report
                    </button>
                </div>

                <table class="grade-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Quiz Title</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 50px; color: #666;">
                                    No quiz results found. 
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $r): ?>
                                <?php 
                                    $percentage = ($r['score'] / $r['total_questions']) * 100;
                                    $isPass = $percentage >= 50;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r['quiz_title']); ?></td>
                                    <td class="score-badge"><?php echo $r['score']; ?> / <?php echo $r['total_questions']; ?></td>
                                    <td class="<?php echo $isPass ? 'status-pass' : 'status-fail'; ?>">
                                        <?php echo round($percentage); ?>% <?php echo $isPass ? '✓' : '✗'; ?>
                                    </td>
                                    <td style="color: #666; font-size: 0.85rem;">
                                        <?php echo date('M d, Y', strtotime($r['submitted_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>