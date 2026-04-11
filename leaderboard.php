<?php
// Enable error reporting to fix the blank page issue
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="my_classes.php" class="nav-item"><i data-lucide="book-open"></i> My Classes</a>
                
                <?php if ($userRole === 'teacher'): ?>
                    <a href="assignments.php" class="nav-item"><i data-lucide="clipboard-list"></i> Assignments</a>
                    <a href="gradebook.php" class="nav-item"><i data-lucide="bar-chart-3"></i> Gradebook</a>
                <?php else: ?>
                    <a href="take_quiz.php" class="nav-item"><i data-lucide="pen-tool"></i> Take Quiz</a>
                    <a href="leaderboard.php" class="nav-item active"><i data-lucide="trophy"></i> Leaderboard</a>
                <?php endif; ?>

                <a href="backend/logout.php" class="nav-item logout-link">
                    <i data-lucide="log-out"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Class Leaderboard</h1>
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <div class="avatar-circle"></div>
                </div>
            </header>

            <div class="dashboard-grid">
                <div class="card">
                    <h3><i data-lucide="medal" style="color: #fbbf24;"></i> Rankings</h3>
                    <p style="color: var(--text-dim); margin-top: 10px;">
                        The leaderboard is calculated based on quiz scores. Finish your first quiz to see your rank!
                    </p>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>