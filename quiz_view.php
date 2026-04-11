<?php
session_start();
require 'backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$quizId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$quizId) {
    die("Quiz ID is missing.");
}

// 1. DATABASE CHECK: See if this user has already submitted this quiz
$checkAttempt = $pdo->prepare("SELECT id FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
$checkAttempt->execute([$userId, $quizId]);
$alreadyAttempted = $checkAttempt->fetch();

// 2. FETCH QUIZ DETAILS
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Quiz not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($quiz['quiz_title']); ?> | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body style="background: #0f172a; color: white; padding: 40px;">

    <div style="max-width: 800px; margin: 0 auto;">
        
        <?php if ($alreadyAttempted): ?>
            <div class="card" style="text-align: center; padding: 40px; border: 1px solid #ef4444; background: rgba(239, 68, 68, 0.1);">
                <i data-lucide="alert-octagon" style="width: 64px; height: 64px; color: #ef4444; margin-bottom: 20px;"></i>
                <h2 style="color: #ef4444;">Access Denied</h2>
                <p style="font-size: 1.2rem; margin: 15px 0;">You have already attempted this quiz.</p>
                <p style="color: #94a3b8; margin-bottom: 30px;">Students are allowed only 1 attempt per quiz. Please contact your teacher if you believe this is an error.</p>
                <a href="take_quiz.php" class="btn-primary" style="text-decoration: none; padding: 12px 25px;">Back to Quiz List</a>
            </div>

        <?php else: ?>
            <header style="margin-bottom: 40px; border-bottom: 1px solid #334155; padding-bottom: 20px;">
                <h1><?php echo htmlspecialchars($quiz['quiz_title']); ?></h1>
                <p style="color: #94a3b8;">Single Attempt Mode: Your score will be recorded upon submission.</p>
            </header>

            <?php
            // Fetch Questions
            $qStmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
            $qStmt->execute([$quizId]);
            $questions = $qStmt->fetchAll();
            ?>

            <form action="backend/submit_quiz.php" method="POST">
                <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
                
                <?php foreach ($questions as $index => $q): ?>
                    <div class="card" style="background: #1e293b; padding: 25px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #334155;">
                        <p style="font-size: 1.1rem; margin-bottom: 20px;">
                            <strong style="color: var(--primary);">Question <?php echo $index + 1; ?>:</strong><br>
                            <?php echo htmlspecialchars($q['question_text']); ?>
                        </p>
                        
                        <div style="display: grid; gap: 10px;">
                            <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                <label style="background: #334155; padding: 15px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: 0.2s;">
                                    <input type="radio" name="answer[<?php echo $q['id']; ?>]" value="<?php echo $opt; ?>" required>
                                    <span><?php echo htmlspecialchars($q['option_'.strtolower($opt)]); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 18px; font-size: 1.1rem; font-weight: bold; margin-top: 20px;">
                    Submit Final Attempt
                </button>
            </form>
        <?php endif; ?>

    </div>

    <script>lucide.createIcons();</script>
</body>
</html>