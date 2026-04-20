<?php
// 1. Enable error reporting to fix the "Blank Page" issue
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'backend/db_connect.php';

// 2. Check Permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Access Denied: Only teachers can view submissions.");
}

// 3. Get Assignment ID from the URL
$assignment_id = $_GET['id'] ?? null;

if (!$assignment_id) {
    die("Error: No assignment selected. Please go back to the assignments page.");
}

try {
    // 4. Fetch Assignment Details
    $stmtTask = $conn->prepare("SELECT title FROM assignments WHERE id = ?");
    $stmtTask->execute([$assignment_id]);
    $task = $stmtTask->fetch();

    if (!$task) {
        die("Error: Assignment not found in the database.");
    }

    // 5. Fetch Submissions and Student Names
    // We use a JOIN to get the student's name from the users table
    $stmt = $conn->prepare("
        SELECT s.*, u.name 
        FROM submissions s 
        JOIN users u ON s.student_id = u.id 
        WHERE s.assignment_id = ? 
        ORDER BY s.submitted_at DESC
    ");
    $stmt->execute([$assignment_id]);
    $submissions = $stmt->fetchAll();

} catch (PDOException $e) {
    // This catches the "Connection Refused" or table errors
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submissions: <?= htmlspecialchars($task['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .submission-grid { display: grid; gap: 20px; padding: 20px; max-width: 1000px; margin: 0 auto; }
        .submission-card { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid var(--glass-border); 
            padding: 25px; 
            border-radius: 15px;
        }
        .student-meta { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .file-link { 
            display: inline-flex; 
            align-items: center; 
            gap: 10px; 
            background: var(--primary); 
            color: white; 
            padding: 10px 20px; 
            border-radius: 8px; 
            text-decoration: none; 
            margin-top: 15px;
            font-weight: 500;
        }
        .file-link:hover { opacity: 0.9; }
        .no-file { color: #666; font-style: italic; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="app-container" style="display: block; padding-top: 50px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <a href="assignments.php" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">← Back to Assignments</a>
            <h1 style="margin-top: 10px;">Submissions for: <?= htmlspecialchars($task['title']) ?></h1>
        </div>

        <div class="submission-grid">
            <?php if (empty($submissions)): ?>
                <div class="submission-card" style="text-align: center;">
                    <p>No students have submitted this assignment yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($submissions as $sub): ?>
                    <div class="submission-card">
                        <div class="student-meta">
                            <strong><i data-lucide="user" size="16"></i> <?= htmlspecialchars($sub['name']) ?></strong>
                            <span style="font-size: 0.8rem; color: #888;">
                                Submitted on: <?= date('M d, Y h:i A', strtotime($sub['submitted_at'])) ?>
                            </span>
                        </div>

                        <div class="content">
                            <p style="color: #ccc; line-height: 1.6;"><?= nl2br(htmlspecialchars($sub['submission_text'])) ?></p>
                            
                            <?php if (!empty($sub['file_path'])): ?>
                                <a href="backend/<?= htmlspecialchars($sub['file_path']) ?>" download class="file-link">
                                    <i data-lucide="download-cloud"></i> Download Attachment
                                </a>
                            <?php else: ?>
                                <p class="no-file">No file attached.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>