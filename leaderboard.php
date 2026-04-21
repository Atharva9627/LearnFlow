<?php
// 1. Setup & Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Ensure we always have the class_id from the URL
$current_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$userRole = $_SESSION['role'] ?? null;

require_once 'backend/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Redirect if class_id is missing to prevent database errors
if (!$current_class_id) {
    header("Location: my_classes.php");
    exit();
}

$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role'];

// CHANGED: We now use quiz_id for more accurate fetching
$selected_quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
$selected_quiz_title = isset($_GET['quiz_name']) ? $_GET['quiz_name'] : "Leaderboard";
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
        .rank-1 { color: #fbbf24; font-weight: bold; text-shadow: 0 0 10px rgba(251, 191, 36, 0.3); } 
        .rank-2 { color: #94a3b8; font-weight: bold; } 
        .rank-3 { color: #b45309; font-weight: bold; }
        .quiz-card-link {
            display: block; 
            padding: 18px; 
            margin-bottom: 12px; 
            background: rgba(255,255,255,0.03); 
            border: 1px solid var(--glass-border); 
            border-radius: 12px; 
            text-decoration: none; 
            transition: all 0.3s ease;
        }
        .quiz-card-link:hover { 
            border-color: var(--primary); 
            background: rgba(99, 102, 241, 0.1); 
            transform: translateX(5px);
        }
        .empty-state { color: var(--text-dim); text-align: center; padding: 40px; font-style: italic; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; background: var(--primary); color: white; margin-left: 10px; }
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
        <a href="my_classes.php" class="nav-item">
            <i data-lucide="book-open"></i> My Classes
        </a>

        <div class="sidebar-section">
            <p class="section-label">CLASS OPTIONS</p>
            
            <a href="assignments.php?class_id=<?= $current_class_id ?>" class="nav-item">
                <i data-lucide="clipboard-list"></i> My Assignments
            </a>

            <a href="take_quiz.php?class_id=<?= $current_class_id ?>" class="nav-item">
                <i data-lucide="pen-tool"></i> Take Quiz
            </a>

            <a href="leaderboard.php?class_id=<?= $current_class_id ?>" class="nav-item">
                <i data-lucide="trophy"></i> Leaderboard
            </a>
        </div>
        <a href="backend/logout.php" class="nav-item logout-link" style="margin-top: auto;">
            <i data-lucide="log-out"></i> Logout
        </a>
    </nav>
</aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="header-title">
                    <h1>Rankings & Analytics</h1>
                    <p style="color: var(--text-dim); font-size: 0.9rem;">Class ID: #<?= htmlspecialchars($current_class_id) ?></p>
                </div>
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <div class="avatar-circle"></div>
                </div>
            </header>

            <section class="dashboard-grid" style="grid-template-columns: 1fr;">
                
                <?php if (!$selected_quiz_id): ?>
                    <div class="card">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                            <i data-lucide="list" style="color: var(--primary);"></i>
                            <h3 style="margin: 0;">Select a Quiz to View Standings</h3>
                        </div>
                        
                        <div class="quiz-selection-list">
                            <?php
                            // Fetch quizzes strictly for this class
                            $stmt = $conn->prepare("SELECT id, quiz_title FROM quizzes WHERE class_id = ? ORDER BY created_at DESC");
                            $stmt->execute([$current_class_id]);
                            $quizzes = $stmt->fetchAll();

                            if (count($quizzes) > 0) {
                                foreach ($quizzes as $q) {
                                    $encoded_title = urlencode($q['quiz_title']);
                                    echo "<a href='leaderboard.php?class_id=$current_class_id&quiz_id={$q['id']}&quiz_name=$encoded_title' class='quiz-card-link'>
                                            <div style='display: flex; justify-content: space-between; align-items: center;'>
                                                <span style='color: white; font-weight: 600; font-size: 1.1rem;'>" . htmlspecialchars($q['quiz_title']) . "</span>
                                                <i data-lucide='chevron-right' size='18'></i>
                                            </div>
                                            <small style='color: var(--text-dim); margin-top: 5px; display: block;'>View performance for this class</small>
                                          </a>";
                                }
                            } else {
                                echo "<div class='empty-state'>
                                        <i data-lucide='help-circle' size='48' style='margin-bottom: 15px; opacity: 0.2;'></i>
                                        <p>No quizzes have been created for this class yet.</p>
                                      </div>";
                            }
                            ?>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--glass-border); padding-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <i data-lucide="medal" style="color: #fbbf24;"></i>
                                <h3 style="margin: 0;">Leaderboard: <span style="color: var(--primary);"><?php echo htmlspecialchars($selected_quiz_title); ?></span></h3>
                            </div>
                            <a href="leaderboard.php?class_id=<?= $current_class_id ?>" class="btn-primary" style="padding: 10px 20px; font-size: 0.85rem; text-decoration:none; display: flex; align-items: center; gap: 8px;">
                                <i data-lucide="arrow-left" size='16'></i> Back to Quiz List
                            </a>
                        </div>
                        
                        <div style="overflow-x: auto;">
                            <table class="leaderboard-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Rank</th>
                                        <th>Student Name</th>
                                        <th>Score</th>
                                        <th>Submission Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    /* CRITICAL FIX: 
                                       1. Joining quiz_scores with users to get names.
                                       2. Filtering by quiz_id from URL.
                                       3. Using corrected column names: total_marks and completed_at.
                                    */
                                    $stmt = $conn->prepare("
                                        SELECT u.name, s.score, s.total_marks, s.completed_at 
                                        FROM quiz_scores s
                                        JOIN users u ON s.student_id = u.id
                                        WHERE s.quiz_id = ?
                                        ORDER BY s.score DESC, s.completed_at ASC
                                    ");
                                    $stmt->execute([$selected_quiz_id]);
                                    $results = $stmt->fetchAll();

                                    if (count($results) > 0):
                                        $rank = 1;
                                        foreach ($results as $row):
                                            $rankClass = ($rank <= 3) ? "rank-$rank" : "";
                                    ?>
                                        <tr>
                                            <td class="<?php echo $rankClass; ?>">
                                                <?php if($rank == 1) echo "🥇"; elseif($rank == 2) echo "🥈"; elseif($rank == 3) echo "🥉"; ?>
                                                #<?php echo $rank++; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                                <?php if($row['score'] == $row['total_marks']) echo "<span class='badge'>Perfect!</span>"; ?>
                                            </td>
                                            <td>
                                                <span style="color: var(--primary); font-weight: bold; font-size: 1.1rem;"><?php echo $row['score']; ?></span> 
                                                <span style="color: var(--text-dim);">/ <?php echo $row['total_marks']; ?></span>
                                            </td>
                                            <td style="color: var(--text-dim); font-size: 0.85rem;">
                                                <i data-lucide="calendar" size="12" style="vertical-align: middle; margin-right: 4px;"></i>
                                                <?php echo date('M d, Y | H:i', strtotime($row['completed_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach; 
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="4" class="empty-state">No students have completed this quiz in this class yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            </section>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>