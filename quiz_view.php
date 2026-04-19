<?php
session_start();
require_once 'backend/db_connect.php';

// 1. Setup & Error Reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Ensure we get 'quiz_id' from the URL (Fixes "Quiz ID is missing" error)
$quizId = $_GET['quiz_id'] ?? null;
$userId = $_SESSION['user_id'];
$current_class_id = $_GET['class_id'] ?? $_SESSION['current_class_id'] ?? null;

if (!$quizId) {
    die("Error: Quiz ID is missing. Please return to the quiz selection page.");
}

// 2. DATABASE CHECK: See if this user has already submitted this quiz
// Updated to use 'quiz_scores' and 'student_id' based on your DB schema
$checkAttempt = $conn->prepare("SELECT id FROM quiz_scores WHERE student_id = ? AND quiz_id = ?");
$checkAttempt->execute([$userId, $quizId]);
$alreadyAttempted = $checkAttempt->fetch();

// 3. FETCH QUIZ DETAILS
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Error: Quiz not found in the database.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['quiz_title']); ?> | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #6366f1; --glass-bg: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.1); }
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; line-height: 1.6; }
        .quiz-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { background: #1e293b; padding: 30px; border-radius: 16px; border: 1px solid var(--glass-border); margin-bottom: 25px; }
        .option-label { 
            background: #334155; padding: 18px; border-radius: 10px; cursor: pointer; 
            display: flex; align-items: center; gap: 15px; transition: all 0.2s ease;
            border: 1px solid transparent; margin-bottom: 10px;
        }
        .option-label:hover { background: #475569; border-color: var(--primary); }
        input[type="radio"] { width: 20px; height: 20px; accent-color: var(--primary); }
        .btn-submit { 
            width: 100%; padding: 20px; background: var(--primary); color: white; 
            border: none; border-radius: 12px; font-size: 1.1rem; font-weight: bold; 
            cursor: pointer; transition: transform 0.2s; 
        }
        .btn-submit:hover { transform: translateY(-2px); filter: brightness(1.1); }
    </style>
</head>
<body>

    <div class="quiz-container">
        
        <?php if ($alreadyAttempted): ?>
            <div class="card" style="text-align: center; border-color: #ef4444; background: rgba(239, 68, 68, 0.05);">
                <i data-lucide="alert-octagon" style="width: 64px; height: 64px; color: #ef4444; margin-bottom: 20px;"></i>
                <h2 style="color: #ef4444; margin-bottom: 10px;">Already Attempted</h2>
                <p style="color: #94a3b8; margin-bottom: 30px;">
                    You have already completed <strong><?php echo htmlspecialchars($quiz['quiz_title']); ?></strong>. 
                    Students are limited to one attempt.
                </p>
                <a href="take_quiz.php?class_id=<?= $current_class_id ?>" class="btn-primary" style="text-decoration: none; padding: 12px 24px; display: inline-block;">
                    Return to Quizzes
                </a>
            </div>

        <?php else: ?>
            <header style="margin-bottom: 40px;">
                <h1 style="font-size: 2rem; margin-bottom: 10px;"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h1>
                <div style="display: flex; gap: 20px; color: #94a3b8; font-size: 0.9rem;">
                    <span><i data-lucide="help-circle" size="14" style="vertical-align: middle;"></i> Select one answer per question</span>
                    <span><i data-lucide="shield-check" size="14" style="vertical-align: middle;"></i> Single Attempt Mode</span>
                </div>
            </header>

            <?php
            // Fetch Questions for this specific Quiz ID
            $qStmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
            $qStmt->execute([$quizId]);
            $questions = $qStmt->fetchAll();
            ?>

            <form action="backend/submit_quiz.php" method="POST">
                <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
                
                <?php foreach ($questions as $index => $q): ?>
                    <div class="card">
                        <p style="font-size: 1.15rem; margin-bottom: 25px;">
                            <span style="color: var(--primary); font-weight: 800; margin-right: 10px;">Q<?php echo $index + 1; ?>.</span>
                            <?php echo htmlspecialchars($q['question_text']); ?>
                        </p>
                        
                        <div style="display: flex; flex-direction: column;">
                            <?php foreach (['a', 'b', 'c', 'd'] as $opt): ?>
                                <label class="option-label">
                                    <input type="radio" name="answer[<?php echo $q['id']; ?>]" value="<?php echo strtoupper($opt); ?>" required>
                                    <span><?php echo htmlspecialchars($q['option_'.$opt]); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn-submit" onclick="return confirm('Are you sure you want to submit? You cannot change your answers after this.')">
                    Submit Quiz Attempt
                </button>
            </form>
        <?php endif; ?>

    </div>

    <script>lucide.createIcons();</script>
</body>
</html>