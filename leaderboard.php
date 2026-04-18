<?php
// 1. Setup & Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$current_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$userRole = $_SESSION['role'] ?? null;
// Standardized $conn is defined in db_connect.php
require_once 'backend/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role'];

// We use quiz_name to match the quiz_scores table
$selected_quiz = isset($_GET['quiz_name']) ? $_GET['quiz_name'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .leaderboard-table { width: 100%; border-collapse: collapse; margin-top: 20px; color: white; }
        .leaderboard-table th, .leaderboard-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--glass-border); }
        .rank-1 { color: #fbbf24; font-weight: bold; } 
        .rank-2 { color: #94a3b8; font-weight: bold; } 
        .rank-3 { color: #b45309; font-weight: bold; }
        .quiz-card-link {
            display: block; 
            padding: 15px; 
            margin-bottom: 10px; 
            background: rgba(255,255,255,0.05); 
            border: 1px solid var(--glass-border); 
            border-radius: 10px; 
            text-decoration: none; 
            transition: 0.3s;
        }
        .quiz-card-link:hover { border-color: var(--primary); background: rgba(255,255,255,0.1); }
        .empty-state { color: var(--text-dim); text-align: center; padding: 20px; }
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
                <h1>Rankings & Analytics</h1>
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                </div>
            </header>

            <section class="dashboard-grid" style="grid-template-columns: 1fr;">
                
                <?php if (!$selected_quiz): ?>
                    <div class="card">
                        <h3><i data-lucide="list"></i> Select a Quiz to see Standings</h3>
                        <div style="margin-top: 20px;">
                            <?php
                            // Fetching quiz_title from quizzes table
                            $stmt = $conn->prepare("SELECT DISTINCT quiz_title FROM quizzes ORDER BY quiz_title ASC");
                            $stmt->execute();
                            $quizzes = $stmt->fetchAll();

                            if (count($quizzes) > 0) {
                                foreach ($quizzes as $q) {
                                    // Pass quiz_name in the URL
                                    echo "<a href='leaderboard.php?quiz_name=" . urlencode($q['quiz_title']) . "' class='quiz-card-link'>
                                            <div style='color: var(--primary); font-weight: bold;'>" . htmlspecialchars($q['quiz_title']) . "</div>
                                            <small style='color: var(--text-dim);'>Click to view student rankings</small>
                                          </a>";
                                }
                            } else {
                                echo "<p class='empty-state'>No quizzes have been created yet.</p>";
                            }
                            ?>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3><i data-lucide="medal"></i> Rankings: <?php echo htmlspecialchars($selected_quiz); ?></h3>
                            <a href="leaderboard.php" class="btn-primary" style="padding: 8px 15px; font-size: 0.8rem; text-decoration:none; background:var(--primary); color:white; border-radius:5px;">View All Quizzes</a>
                        </div>
                        
                        <table class="leaderboard-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student Name</th>
                                    <th>Score</th>
                                    <th>Submission Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // JOIN quiz_scores with users using correct column names
                                $stmt = $conn->prepare("
                                    SELECT u.name, s.score, s.total_questions, s.completed_at 
                                    FROM quiz_scores s
                                    JOIN users u ON s.student_id = u.id
                                    WHERE s.quiz_name = ?
                                    ORDER BY s.score DESC, s.completed_at ASC
                                ");
                                $stmt->execute([$selected_quiz]);
                                $rank = 1;
                                $results = $stmt->fetchAll();

                                if (count($results) > 0):
                                    foreach ($results as $row):
                                        $rankClass = ($rank <= 3) ? "rank-$rank" : "";
                                ?>
                                    <tr>
                                        <td class="<?php echo $rankClass; ?>">#<?php echo $rank++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td>
                                            <span style="color: var(--primary);"><?php echo $row['score']; ?></span> 
                                            / <?php echo $row['total_questions']; ?>
                                        </td>
                                        <td style="color: var(--text-dim); font-size: 0.8rem;">
                                            <?php echo date('M d, H:i', strtotime($row['completed_at'])); ?>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach; 
                                else:
                                ?>
                                    <tr>
                                        <td colspan="4" class="empty-state">No students have completed this quiz yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </section>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>