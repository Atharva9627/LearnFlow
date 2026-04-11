<?php
session_start();

// 1. Protection: If not logged in, kick back to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role']; // 'teacher' or 'student'
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
        /* Quick styles for the new Class Section */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .card {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
        }
        .success-box {
            background: rgba(75, 255, 75, 0.1);
            border: 1px dashed #4bff4b;
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
            text-align: center;
        }
        .invite-code {
            font-size: 1.8rem;
            color: #4bff4b;
            font-weight: bold;
            letter-spacing: 3px;
            display: block;
            margin-top: 5px;
        }
        .role-badge {
            background: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-transform: capitalize;
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
    <a href="index.php" class="nav-item active"><i data-lucide="layout-dashboard"></i> Dashboard</a>
    
    <a href="my_classes.php" class="nav-item"><i data-lucide="book-open"></i> My Classes</a>
    
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
                    <div class="avatar"></div>
                </div>
                
            </header><?php if (isset($_GET['status']) && $_GET['status'] === 'quiz_completed'): ?>
    <div class="card" style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; margin-bottom: 20px;">
        <h3 style="color: #10b981;">Quiz Submitted!</h3>
        <p>You scored <strong><?php echo $_GET['score']; ?></strong> out of <strong><?php echo $_GET['total']; ?></strong>.</p>
    </div>
<?php endif; ?>

            <section class="dashboard-grid">
                
                <?php if ($userRole === 'teacher'): ?>
                    <div class="card">
                        <h3><i data-lucide="plus-circle"></i> Create New Class</h3>
                        <p style="color: var(--text-dim); margin: 10px 0;">Start a new session for your students.</p>
                        
                        <form action="backend/create_class.php" method="POST">
                            <input type="text" name="class_name" placeholder="e.g., Computer Science 101" required 
                                   style="width: 100%; padding: 12px; margin-bottom: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; border-radius: 8px;">
                            <button type="submit" class="btn-primary" style="width: 100%;">Generate Invite Code</button>
                        </form>

                        <?php if (isset($_GET['status']) && $_GET['status'] == 'class_created'): ?>
                            <div class="success-box">
                                <p>Class Created! Share this code:</p>
                                <span class="invite-code"><?php echo $_GET['code']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($userRole === 'student'): ?>
                    <div class="card">
                        <h3><i data-lucide="user-plus"></i> Join a Class</h3>
                        <p style="color: var(--text-dim); margin: 10px 0;">Enter the 6-digit code provided by your teacher.</p>
                        
                        <form action="backend/join_class.php" method="POST">
                            <input type="text" name="invite_code" maxlength="6" placeholder="Enter Code (e.g. A1B2C3)" required 
                                   style="width: 100%; padding: 12px; margin-bottom: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; border-radius: 8px; text-transform: uppercase;">
                            <button type="submit" class="btn-primary" style="width: 100%;">Join Class</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <h3><i data-lucide="activity"></i> Recent Activity</h3>
                    <p style="color: var(--text-dim); padding-top: 15px;">No recent activity to show yet. Start by joining or creating a class!</p>
                </div>

            </section>
        </main>
    </div>

    <script>
        // Initialize Icons
        lucide.createIcons();
    </script>
</body>
</html>