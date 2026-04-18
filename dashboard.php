<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'backend/db_connect.php';

// PROTECTION: If not logged in, kick back to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnFlow | Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 300px; /* Main feed and Sidebar actions */
            gap: 20px;
            padding: 20px;
        }
        @media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } }
        
        .feed-item {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .feed-item:hover { transform: translateY(-3px); border-color: var(--primary); }
        
        .type-tag {
            font-size: 0.7rem;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 5px;
            font-weight: bold;
            margin-right: 10px;
        }
        .tag-announcement { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
        .tag-assignment { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
        .tag-quiz { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }

        .post-form input, .post-form select, .post-form textarea {
            width: 100%; padding: 12px; margin-bottom: 10px;
            background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);
            color: white; border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon"></div>
                <span>Learn<span class="flow-text">Flow</span></span>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item active"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="my_classes.php" class="nav-item"><i data-lucide="book-open"></i> My Classes</a>
                
                <?php if ($userRole === 'teacher'): ?>
                    <a href="assignments.php" class="nav-item"><i data-lucide="clipboard-list"></i> Assignments</a>
                    <a href="gradebook.php" class="nav-item"><i data-lucide="bar-chart-3"></i> Gradebook</a>
                <?php else: ?>
                    <a href="take_quiz.php" class="nav-item"><i data-lucide="pen-tool"></i> Take Quiz</a>
                    <a href="leaderboard.php" class="nav-item"><i data-lucide="trophy"></i> Leaderboard</a>
                <?php endif; ?>

                <a href="backend/logout.php" class="nav-item logout-link"><i data-lucide="log-out"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" placeholder="Search your classes...">
                </div>
                <div class="user-profile">
                    <span class="role-badge"><?php echo $userRole; ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <div class="avatar"></div>
                </div>
            </header>

            <section class="dashboard-grid">
                
                <div class="feed-section">
                    <h3><i data-lucide="rss"></i> Class Activity Feed</h3>
                    <br>
                    <?php
                    // SQL to get items from classes the student has joined OR the teacher owns
                    $feedQuery = "
                        SELECT m.*, c.class_name, u.name as author
                        FROM class_materials m
                        JOIN classes c ON m.class_id = c.id
                        JOIN users u ON c.teacher_id = u.id
                        LEFT JOIN enrollments e ON c.id = e.class_id
                        WHERE c.teacher_id = ? OR e.student_id = ?
                        ORDER BY m.created_at DESC";
                    
                    $stmt = $conn->prepare($feedQuery);
                    $stmt->execute([$userId, $userId]);
                    $feedItems = $stmt->fetchAll();

                    if (count($feedItems) > 0):
                        foreach ($feedItems as $item): 
                            $tagClass = "tag-" . $item['type'];
                    ?>
                        <div class="feed-item">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <span class="type-tag <?php echo $tagClass; ?>"><?php echo $item['type']; ?></span>
                                    <span style="color: var(--text-dim); font-size: 0.8rem;"><?php echo $item['class_name']; ?> • Posted by <?php echo $item['author']; ?></span>
                                    <h4 style="margin: 10px 0; font-size: 1.2rem;"><?php echo htmlspecialchars($item['title']); ?></h4>
                                </div>
                                <span style="font-size: 0.75rem; color: var(--text-dim);"><?php echo date('M d', strtotime($item['created_at'])); ?></span>
                            </div>
                            <p style="color: var(--text-dim); line-height: 1.5;"><?php echo nl2br(htmlspecialchars($item['content'])); ?></p>
                            
                            <?php if($item['type'] === 'quiz'): ?>
                                <a href="take_quiz.php?id=<?php echo $item['id']; ?>" class="btn-primary" style="display: inline-block; margin-top: 15px; padding: 8px 20px; font-size: 0.8rem;">Start Quiz</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; 
                    else: ?>
                        <div class="card" style="text-align: center; padding: 40px;">
                            <i data-lucide="ghost" size="48" style="color: var(--text-dim);"></i>
                            <p style="margin-top: 15px; color: var(--text-dim);">No announcements or assignments yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="actions-section">
                    <?php if ($userRole === 'teacher'): ?>
                        <div class="card post-form">
                            <h3><i data-lucide="plus-square"></i> Create Post</h3>
                            <p style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 15px;">Send an update to your students.</p>
                            
                            <form action="backend/post_material.php" method="POST">
                                <select name="class_id" required>
                                    <option value="">Choose Class</option>
                                    <?php
                                    $cStmt = $conn->prepare("SELECT id, class_name FROM classes WHERE teacher_id = ?");
                                    $cStmt->execute([$userId]);
                                    while($class = $cStmt->fetch()) {
                                        echo "<option value='{$class['id']}'>{$class['class_name']}</option>";
                                    }
                                    ?>
                                </select>
                                <select name="type">
                                    <option value="announcement">Announcement</option>
                                    <option value="assignment">Assignment</option>
                                    <option value="quiz">Quiz Notification</option>
                                </select>
                                <input type="text" name="title" placeholder="Title" required>
                                <textarea name="content" placeholder="Instructions or details..." rows="4" required></textarea>
                                <button type="submit" class="btn-primary" style="width: 100%;">Post to Feed</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <h3><i data-lucide="user-plus"></i> Join Class</h3>
                            <form action="backend/join_class.php" method="POST" class="post-form">
                                <input type="text" name="invite_code" placeholder="Enter 6-digit code" maxlength="6" required style="text-transform: uppercase; text-align: center; letter-spacing: 2px;">
                                <button type="submit" class="btn-primary" style="width: 100%;">Join Class</button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card" style="margin-top: 20px;">
                        <h3><i data-lucide="calendar"></i> Upcoming</h3>
                        <p style="font-size: 0.8rem; color: var(--text-dim); margin-top: 10px;">Check your classes for due dates.</p>
                    </div>
                </div>

            </section>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>