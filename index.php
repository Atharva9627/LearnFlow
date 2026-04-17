<?php
session_start();
// If the user is ALREADY logged in, send them straight to the dashboard

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to LearnFlow | Modern LMS</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="intro-page">

    <nav class="intro-navbar">
        <div class="logo">
            <span>Learn<span class="flow-text">Flow</span></span>
        </div>
        <a href="auth.php" class="intro-login-btn">Login / Register</a>
    </nav>

    <header class="intro-hero">
        <h1>Master Your Learning Flow</h1>
        <p>A sleek, secure platform designed for teachers to manage classrooms and students to excel in their quizzes.</p>
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
            <p>Teachers generate 6-digit codes. Students join in a single click.</p>
        </div>
        <div class="feature-card">
            <i data-lucide="bar-chart-3" size="48"></i>
            <h3>Real-time Results</h3>
            <p>Get immediate feedback and analytics on your quiz performance.</p>
        </div>
    </section>

    <script>lucide.createIcons();</script>
</body>
</html>