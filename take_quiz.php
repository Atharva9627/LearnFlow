<?php
// 1. Setup & Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$current_class_id = $_GET['class_id'] ?? $_SESSION['current_class_id'] ?? null;

// If we found it in the URL, save it to the session so other pages remember it
if (isset($_GET['class_id'])) {
    $_SESSION['current_class_id'] = $_GET['class_id'];
}

require_once 'backend/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// 2. Context Check
$current_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
if (!$current_class_id) {
    header("Location: my_classes.php");
    exit();
}

$userName = $_SESSION['user_name'];
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
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
        .quiz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 25px; }
        .quiz-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .quiz-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.06);
        }
        .quiz-status {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.7rem;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-new { background: rgba(99, 102, 241, 0.2); color: var(--primary); }
        .status-done { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        
        .quiz-info h3 { margin: 0 0 8px 0; color: white; font-size: 1.2rem; }
        .quiz-info p { color: var(--text-dim); font-size: 0.9rem; margin-bottom: 20px; }
        
        .btn-quiz {
            display: block;
            text-align: center;
            padding: 12px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-quiz:hover { filter: brightness(1.2); box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); }
        .btn-quiz.disabled { background: rgba(255,255,255,0.1); color: var(--text-dim); pointer-events: none; }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="my_classes.php" class="nav-item"><i data-lucide="book-open"></i> My Classes</a>
                
                <div class="sidebar-divider"></div>
                <p class="sidebar-label">Class Options</p>
                
                <a href="take_quiz.php?class_id=<?= $current_class_id ?>" class="nav-item active">
                    <i data-lucide="pen-tool"></i> Take Quiz
                </a>
                <a href="leaderboard.php?class_id=<?= $current_class_id ?>" class="nav-item">
                    <i data-lucide="trophy"></i> Leaderboard
                </a>

                <a href="backend/logout.php" class="nav-item logout-link" style="margin-top: auto;">
                    <i data-lucide="log-out"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="header-title">
                    <h1>Available Quizzes</h1>
                    <p style="color: var(--text-dim);">Class Context: #<?= htmlspecialchars($current_class_id) ?></p>
                </div>
                <div class="user-profile">
                    <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                    <div class="avatar-circle"></div>
                </div>
            </header>

            <section class="quiz-grid">
                <?php
                // Logic: Fetch all quizzes for this class AND check if the user has already submitted a score
                $stmt = $conn->prepare("
    SELECT 
        q.id, 
        q.quiz_title, 
        s.score AS has_score 
    FROM quizzes q 
    LEFT JOIN quiz_scores s ON q.id = s.quiz_id AND s.student_id = ?
    WHERE q.class_id = ? 
    GROUP BY q.id
    ORDER BY q.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $current_class_id]);
$quizzes = $stmt->fetchAll();

                if (count($quizzes) > 0):
                    foreach ($quizzes as $quiz):
                        $completed = ($quiz['has_score'] !== null);
                ?>
                    <div class="quiz-card">
                        <?php if($completed): ?>
                            <span class="quiz-status status-done">Completed</span>
                        <?php else: ?>
                            <span class="quiz-status status-new">Pending</span>
                        <?php endif; ?>

                        <div class="quiz-info">
                            <h3><?= htmlspecialchars($quiz['quiz_title']) ?></h3>
                            <p><i data-lucide="help-circle" size="14" style="vertical-align: middle;"></i> Multiple Choice Assessment</p>
                        </div>

                        <?php if($completed): ?>
                            <div class="btn-quiz disabled">Already Submitted</div>
                        <?php else: ?>
                           <a href="quiz_view.php?quiz_id=<?= $quiz['id'] ?>&class_id=<?= $current_class_id ?>" class="btn-primary">
    Start Quiz
</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; else: ?>
                    <div class="card" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                        <i data-lucide="clipboard-list" size="48" style="opacity: 0.2; margin-bottom: 15px;"></i>
                        <p style="color: var(--text-dim);">No quizzes have been assigned to this class yet.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>