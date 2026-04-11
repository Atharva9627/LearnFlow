<?php
session_start();
// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnFlow | Login & Sign Up</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Specific styles for the Auth Page to match your design */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, var(--secondary), transparent), var(--bg-dark);
        }
        .auth-card {
            background: var(--bg-card);
            padding: 2.5rem;
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            width: 100%;
            max-width: 400px;
            box-shadow: var(--glow);
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 2rem;
            background: rgba(0,0,0,0.2);
            padding: 5px;
            border-radius: 12px;
        }
        .tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.3s;
            color: var(--text-dim);
        }
        .tab.active {
            background: var(--primary);
            color: white;
        }
        .auth-form { display: none; }
        .auth-form.active { display: block; }
        
        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: white;
            outline: none;
        }
        .msg {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            text-align: center;
        }
        .msg-error { background: rgba(255, 75, 75, 0.2); color: #ff4b4b; border: 1px solid #ff4b4b; }
        .msg-success { background: rgba(75, 255, 75, 0.2); color: #4bff4b; border: 1px solid #4bff4b; }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="logo" style="justify-content: center; margin-bottom: 1.5rem;">
            <div class="logo-icon"></div>
            <span>Learn<span class="flow-text">Flow</span></span>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="msg msg-error">Invalid email or password.</div>
        <?php endif; ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            <div class="msg msg-success">Account created! Please login.</div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="switchTab('login')">Login</div>
            <div class="tab" onclick="switchTab('signup')">Sign Up</div>
        </div>

        <form id="login-form" class="auth-form active" action="backend/auth_logic.php" method="POST">
            <input type="hidden" name="action" value="login">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn-primary">Sign In</button>
        </form>

        <form id="signup-form" class="auth-form" action="backend/auth_logic.php" method="POST">
            <input type="hidden" name="action" value="signup">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Create Password" required>
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
            <button type="submit" class="btn-primary">Create Account</button>
        </form>
    </div>

    <script>
        function switchTab(type) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            
            if(type === 'login') {
                document.querySelector('.tab:nth-child(1)').classList.add('active');
                document.getElementById('login-form').classList.add('active');
            } else {
                document.querySelector('.tab:nth-child(2)').classList.add('active');
                document.getElementById('signup-form').classList.add('active');
            }
        }

        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>