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
$current_class_id = $_GET['class_id'] ?? $_SESSION['current_class_id'] ?? null;

if (!$current_class_id) {
    header("Location: my_classes.php");
    exit();
}

// Logic: Fetch Stream (Announcements)
$stmt = $conn->prepare("SELECT * FROM announcements WHERE class_id = ? ORDER BY created_at DESC");
$stmt->execute([$current_class_id]);
$announcements = $stmt->fetchAll();

// Logic: Fetch Assignments
$stmtA = $conn->prepare("SELECT * FROM assignments WHERE class_id = ? ORDER BY created_at DESC");
$stmtA->execute([$current_class_id]);
$assignments = $stmtA->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignments & Stream | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .page-grid { display: grid; grid-template-columns: 1fr 350px; gap: 30px; padding: 20px; }
        @media (max-width: 900px) { .page-grid { grid-template-columns: 1fr; } }
        .announcement-card { background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); padding: 20px; border-radius: 15px; margin-bottom: 15px; }
        .announcement-badge { background: var(--primary); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; margin-bottom: 10px; display: inline-block; }
        .assignment-card { background: var(--bg-card); border: 1px solid var(--glass-border); padding: 25px; border-radius: 15px; margin-bottom: 20px; }
        .due-date { color: #ef4444; font-weight: bold; font-size: 0.85rem; }
        .post-box { background: rgba(255,255,255,0.05); padding: 25px; border-radius: 15px; border: 1px solid var(--primary); margin-bottom: 30px; }
        textarea, input { width: 100%; padding: 12px; margin-bottom: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; border-radius: 8px; }
        .btn-view { background: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid #818cf8; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; transition: 0.3s; }
        .btn-view:hover { background: #818cf8; color: white; }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="my_classes.php" class="nav-item"><i data-lucide="book-open"></i> My Classes</a>
                <div class="sidebar-divider" style="margin: 15px 0; border-top: 1px solid var(--glass-border);"></div>
                <?php if ($userRole === 'teacher'): ?>
                    <a href="create_quiz.php?class_id=<?= $current_class_id ?>" class="nav-item"><i data-lucide="plus-circle"></i> Create Quiz</a>
                <?php endif; ?>
                <a href="assignments.php?class_id=<?= $current_class_id ?>" class="nav-item active"><i data-lucide="clipboard-list"></i> Assignments</a>
                <a href="leaderboard.php?class_id=<?= $current_class_id ?>" class="nav-item"><i data-lucide="trophy"></i> Leaderboard</a>
                <a href="backend/logout.php" class="nav-item logout-link" style="margin-top: auto;"><i data-lucide="log-out"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1 style="font-size: 1.5rem;">Classroom Stream & Assignments</h1>
                <div class="user-profile">
                    <span class="role-badge"><?= $userRole ?></span>
                    <span><?= htmlspecialchars($userName) ?></span>
                </div>
            </header>

            <div class="page-grid">
                <div class="work-column">
                    <?php if ($userRole === 'teacher'): ?>
                        <div class="post-box">
                            <h3><i data-lucide="file-plus"></i> Create New Assignment</h3>
                            <form action="backend/save_assignment.php" method="POST" style="margin-top:15px;">
                                <input type="hidden" name="class_id" value="<?= $current_class_id ?>">
                                <input type="text" name="title" placeholder="Assignment Title" required>
                                <textarea name="description" rows="3" placeholder="Provide instructions for students..." required></textarea>
                                <div style="display:flex; justify-content: space-between; align-items:center;">
                                    <div style="flex:1; margin-right:15px;">
                                        <label style="font-size: 0.7rem; color: var(--text-dim);">DUE DATE</label>
                                        <input type="datetime-local" name="due_date" required>
                                    </div>
                                    <button type="submit" class="btn-primary" style="margin-top:10px;">Publish Work</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="assignment-list">
                        <h3><i data-lucide="list-checks"></i> Assignment Tasks</h3><br>
                        <?php if (empty($assignments)): ?>
                            <div class="card" style="text-align:center; padding:30px;">
                                <p style="color:var(--text-dim);">No assignments have been posted yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($assignments as $task): ?>
                                <div class="assignment-card">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                        <div>
                                            <h4 style="color:var(--primary); font-size:1.2rem;"><?= htmlspecialchars($task['title']) ?></h4>
                                            <?php if ($userRole === 'teacher'): ?>
                                                <a href="view_submissions.php?id=<?= $task['id'] ?>" class="btn-view" style="display:inline-block; margin-top:10px;">
                                                    <i data-lucide="eye" size="14" style="vertical-align:middle;"></i> View Submissions
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <span class="due-date"><i data-lucide="calendar-clock" size="14"></i> Due: <?= date('M d, H:i', strtotime($task['due_date'])) ?></span>
                                    </div>
                                    <p style="margin: 15px 0; line-height: 1.6; color: var(--text-dim);"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                                    
                                    <?php if ($userRole === 'student'): ?>
    <form action="backend/submit_work.php" method="POST" enctype="multipart/form-data" style="border-top:1px solid var(--glass-border); margin-top:15px; padding-top:15px;">
    <input type="hidden" name="assignment_id" value="<?= $task['id'] ?>">
    <input type="hidden" name="class_id" value="<?= $current_class_id ?>">
        <label style="font-size: 0.8rem; color: var(--text-dim);">Your Comments/Notes:</label>
        <textarea name="submission_text" placeholder="Type any notes for the teacher..."></textarea>
        
        <label style="font-size: 0.8rem; color: var(--text-dim);">Upload File (PDF, DOCX):</label>
        <input type="file" name="assignment_file" accept=".pdf,.doc,.docx" required style="margin-top:5px;">
        
        <button type="submit" class="btn-primary" style="margin-top:10px;">Submit My Work</button>
    </form>
<?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stream-column">
                    <h3><i data-lucide="megaphone"></i> Notice Board</h3><br>
                    <?php foreach ($announcements as $post): ?>
                        <div class="announcement-card">
                            <span class="announcement-badge" style="background: <?= $post['type'] === 'reminder' ? '#f59e0b' : '#6366f1' ?>;">
                                <?= ucfirst($post['type']) ?>
                            </span>
                            <p style="font-size: 0.95rem; margin: 5px 0;"><?= htmlspecialchars($post['content']) ?></p>
                            <span style="font-size: 0.7rem; color: var(--text-dim);"><?= date('M d', strtotime($post['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>