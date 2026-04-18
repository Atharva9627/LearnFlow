<?php
session_start();
require 'backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Sidebar & Role Variables
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$userName = $_SESSION['user_name'];
$current_class_id = $_GET['class_id'] ?? null;

if (!$current_class_id) {
    header("Location: my_classes.php");
    exit();
}

// Fetch Announcements/Reminders for this specific class
$stmt = $conn->prepare("SELECT * FROM announcements WHERE class_id = ? ORDER BY created_at DESC");
$stmt->execute([$current_class_id]);
$announcements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignments & Announcements | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .assignment-grid { display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 20px; }
        .announcement-card { background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); padding: 20px; border-radius: 15px; margin-bottom: 15px; }
        .announcement-badge { background: var(--primary); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; margin-bottom: 10px; display: inline-block; }
        .post-box { background: rgba(255,255,255,0.05); padding: 25px; border-radius: 15px; border: 1px solid var(--primary); margin-bottom: 30px; }
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
                <h1>Classroom Stream</h1>
                <div class="user-profile"><span><?= htmlspecialchars($userName) ?></span></div>
            </header>

            <?php if ($userRole === 'teacher'): ?>
                <div class="post-box">
                    <h3 style="margin-bottom: 15px;">Post a Reminder or Announcement</h3>
                    <form action="backend/post_material.php" method="POST">
                        <input type="hidden" name="class_id" value="<?= $current_class_id ?>">
                        <div class="input-group" style="margin-bottom: 15px;">
                            <textarea name="content" rows="3" placeholder="What's on your mind? (e.g., Reminder: Homework due tomorrow!)" required style="width:100%; padding:15px; background:#0f172a; border:1px solid var(--glass-border); color:white; border-radius:10px;"></textarea>
                        </div>
                        <div style="display:flex; justify-content: space-between; align-items:center;">
                            <select name="type" style="padding:10px; border-radius:8px; background:#1e293b; color:white; border:none;">
                                <option value="announcement">Announcement</option>
                                <option value="reminder">Reminder</option>
                            </select>
                            <button type="submit" class="btn-primary" style="padding:10px 30px; border:none; cursor:pointer;">Post to Class</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="assignment-grid">
                <?php if (empty($announcements)): ?>
                    <div class="card" style="text-align: center; color: var(--text-dim);">
                        <p>No announcements or reminders yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $post): ?>
                        <div class="announcement-card">
                            <span class="announcement-badge" style="background: <?= $post['type'] === 'reminder' ? '#f59e0b' : '#6366f1' ?>;">
                                <?= ucfirst($post['type']) ?>
                            </span>
                            <p style="font-size: 1.1rem; margin: 10px 0;"><?= htmlspecialchars($post['content']) ?></p>
                            <span style="font-size: 0.8rem; color: var(--text-dim);">
                                Posted on <?= date('M d, Y', strtotime($post['created_at'])) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>