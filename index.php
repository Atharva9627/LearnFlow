<?php
session_start();
// If the user is already logged in, skip the intro and go to the app
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to LearnFlow | Modern LMS</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Ensuring the navbar is clickable and on top */
        .intro-navbar {
            position: relative;
            z-index: 9999; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            background: rgba(10, 10, 15, 0.8); /* Slight dark backing */
            backdrop-filter: blur(10px);
        }

        .intro-hero {
            position: relative;
            z-index: 1; /* Lower than navbar */
            padding-top: 100px;
        }

        .intro-login-btn {
            cursor: pointer !important;
            pointer-events: auto !important;
            display: inline-block;
            position: relative;
            z-index: 10000;
        }
    </style>
</head>
<body class="intro-page">

    <nav class="intro-navbar">
        <div class="logo">
            <div class="logo-icon"></div>
            <span>Learn<span class="flow-text">Flow</span></span>
        </div>
        <div class="nav-right">
            <a href="auth.php" class="intro-login-btn">Login / Register</a>
        </div>
    </nav>

    <header class="intro-hero" style="text-align: center;">
        <h1>Master Your Learning Flow</h1>
        <p style="max-width: 700px; margin: 20px auto;">
            A sleek, secure platform designed for teachers to manage classrooms and students to excel in their quizzes.
        </p>
    </header>

    <section class="intro-features">
        <div class="feature-card">
            <i data-lucide="shield-check" size="48"></i>
            <h3>Secure & Private</h3>
            <p>Your data is protected with professional-grade session management.</p>
        </div>

        <div class="feature-card">
            <i data-lucide="zap" size="48"></i>
            <h3>Instant Access</h3>
            <p>Teachers generate unique 6-digit codes. Students join in a single click.</p>
        </div>

        <div class="feature-card">
            <i data-lucide="bar-chart-3" size="48"></i>
            <h3>Real-time Results</h3>
            <p>Get immediate feedback and analytics on your quiz performance.</p>
        </div>
    </section>

    <footer style="text-align: center; padding: 40px; color: #666; font-size: 0.9rem;">
        &copy; 2026 LearnFlow Project.
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>