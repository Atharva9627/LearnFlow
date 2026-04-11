<?php
session_start();
require 'db_connect.php';

// 1. Enable Error Reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $quizId = $_POST['quiz_id'];
    $answers = $_POST['answer'] ?? [];

    if (empty($answers)) {
        die("Error: No answers were submitted.");
    }

    try {
        // 2. DUPLICATE CHECK: Prevent multiple attempts
        // This stops those duplicate "Atharva" rows you see in your image
        $check = $pdo->prepare("SELECT id FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
        $check->execute([$userId, $quizId]);
        
        if ($check->fetch()) {
            // Already attempted - redirect without saving again
            header("Location: ../take_quiz.php?error=already_attempted");
            exit();
        }

        // 3. Score Calculation
        $score = 0;
        $stmt = $pdo->prepare("SELECT id, correct_option FROM questions WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
        $correctData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalQuestions = count($correctData);

        foreach ($correctData as $row) {
            $qId = $row['id'];
            $correctOpt = $row['correct_option'];
            if (isset($answers[$qId]) && $answers[$qId] === $correctOpt) {
                $score++;
            }
        }

        // 4. Save to quiz_results
        $insert = $pdo->prepare("INSERT INTO quiz_results (user_id, quiz_id, score, total_questions) VALUES (?, ?, ?, ?)");
        $result = $insert->execute([$userId, $quizId, $score, $totalQuestions]);

        if ($result) {
            header("Location: ../index.php?status=quiz_completed&score=$score&total=$totalQuestions");
            exit();
        }

    } catch (PDOException $e) {
        die("Database Exception: " . $e->getMessage());
    }
} else {
    die("Invalid request or session expired.");
}