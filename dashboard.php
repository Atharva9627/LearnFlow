<?php
session_start();

// PROTECTION: If NOT logged in, kick back to login page
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
    <title>LearnFlow | Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <div class="app-container" style="display: flex; width: 100%;">
        <aside class="sidebar">
            <div class="logo">
                <span>Learn<span class="flow-text">Flow</span></span>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item active">
                    <i data-lucide="layout-dashboard"></i> Dashboard
                </a>
                
                <a href="my_classes.php" class="nav-item">
                    <i data-lucide="book-open"></i> My Classes
                </a>
                
                <?php if ($userRole === 'teacher'): ?>
                    <a href="assignments.php" class="nav-item"><i data-lucide="clipboard-list"></i> Assignments</a>
                    <a href="gradebook.php" class="nav-item"><i data-lucide="bar-chart-3"></i> Gradebook</a>
                <?php else: ?>
                    <a href="take_quiz.php" class="nav-item"><i data-lucide="pen-tool"></i> Take Quiz</a>
                    <a href="leaderboard.php" class="nav-item"><i data-lucide="trophy"></i> Leaderboard</a>
                <?php endif; ?>

                <a href="backend/logout.php" class="nav-item logout-link">
                    <i data-lucide="log-out"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" placeholder="Search for courses...">
                </div>
                <div class="user-profile">
                    <span class="role-badge"><?php echo $userRole; ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <div class="avatar" style="width:35px; height:35px; background:#333; border-radius:50%;"></div>
                </div>
            </header>

            <section class="dashboard-grid">
                <?php if ($userRole === 'teacher'): ?>
                    <div class="card">
                        <h3><i data-lucide="plus-circle"></i> Create New Class</h3>
                        <form action="backend/create_class.php" method="POST">
                            <input type="text" name="class_name" placeholder="e.g., Computer Science 101" required>
                            <button type="submit" class="btn-primary">Generate Invite Code</button>
                        </form>
                        <?php if (isset($_GET['code'])): ?>
                            <div class="success-box">
                                <p>Code: <span class="invite-code"><?php echo $_GET['code']; ?></span></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <h3><i data-lucide="user-plus"></i> Join a Class</h3>
                        <form action="backend/join_class.php" method="POST">
                            <input type="text" name="invite_code" maxlength="6" placeholder="Enter 6-Digit Code" required style="text-transform: uppercase;">
                            <button type="submit" class="btn-primary">Join Class</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <h3><i data-lucide="activity"></i> Recent Activity</h3>
                    <p style="color: var(--text-dim); margin-top:15px;">No recent activity to show yet.</p>
                </div>
            </section>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>