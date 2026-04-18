<?php
// 1. Setup & Protection
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'backend/db_connect.php'; // Standardized $conn is defined here

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role'];
$quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
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
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="leaderboard.php" class="nav-item active"><i data-lucide="trophy"></i> Leaderboard</a>
                <a href="backend/logout.php" class="nav-item logout-link"><i data-lucide="log-out"></i> Logout</a>
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
                
                <?php if (!$quiz_id): ?>
                    <div class="card">
                        <h3><i data-lucide="list"></i> Select a Quiz to see Standings</h3>
                        <div style="margin-top: 20px;">
                            <?php
                            // Fetch quizzes based on your database structure
                            $stmt = $conn->prepare("SELECT id, title FROM quizzes ORDER BY id DESC");
                            $stmt->execute();
                            $quizzes = $stmt->fetchAll();

                            if (count($quizzes) > 0) {
                                foreach ($quizzes as $q) {
                                    echo "<a href='leaderboard.php?quiz_id={$q['id']}' class='quiz-card-link'>
                                            <div style='color: var(--primary); font-weight: bold;'>{$q['title']}</div>
                                            <small style='color: var(--text-dim);'>Click to view rankings</small>
                                          </a>";
                                }
                            } else {
                                echo "<p style='color: var(--text-dim);'>No quizzes available yet.</p>";
                            }
                            ?>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <?php
                                $titleStmt = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
                                $titleStmt->execute([$quiz_id]);
                                $quizTitle = $titleStmt->fetchColumn();
                            ?>
                            <h3><i data-lucide="medal"></i> Rankings: <?php echo htmlspecialchars($quizTitle); ?></h3>
                            <a href="leaderboard.php" class="btn-primary" style="padding: 8px 15px; font-size: 0.8rem;">View All Quizzes</a>
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
                                // JOIN logic to connect scores to student names
                                $stmt = $conn->prepare("
                                    SELECT u.name, s.score, s.total_marks, s.submitted_at 
                                    FROM quiz_scores s
                                    JOIN users u ON s.student_id = u.id
                                    WHERE s.quiz_id = ?
                                    ORDER BY s.score DESC, s.submitted_at ASC
                                ");
                                $stmt->execute([$quiz_id]);
                                $rank = 1;

                                while ($row = $stmt->fetch()):
                                    $rankClass = ($rank <= 3) ? "rank-$rank" : "";
                                ?>
                                    <tr>
                                        <td class="<?php echo $rankClass; ?>">#<?php echo $rank++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td>
                                            <span style="color: var(--primary);"><?php echo $row['score']; ?></span> 
                                            / <?php echo $row['total_marks']; ?>
                                        </td>
                                        <td style="color: var(--text-dim); font-size: 0.8rem;">
                                            <?php echo date('M d, H:i', strtotime($row['submitted_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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