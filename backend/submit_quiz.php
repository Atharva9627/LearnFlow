<?php
session_start();
require 'db_connect.php'; // Ensure this matches your $conn vs $pdo setup

// 1. Enable Error Reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Using $conn to be consistent with your other backend files
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $quizId = $_POST['quiz_id'];
    $answers = $_POST['answer'] ?? [];

    if (empty($answers)) {
        die("Error: No answers were submitted.");
    }

    try {
        // 2. DUPLICATE CHECK: Using 'quiz_scores' and 'student_id'
        $check = $conn->prepare("SELECT id FROM quiz_scores WHERE student_id = ? AND quiz_id = ?");
        $check->execute([$userId, $quizId]);
        
        if ($check->fetch()) {
            header("Location: ../take_quiz.php?error=already_attempted");
            exit();
        }

        // 3. Score Calculation
        $score = 0;
        $stmt = $conn->prepare("SELECT id, correct_option FROM questions WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
        $correctData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalMarks = count($correctData);

        foreach ($correctData as $row) {
            $qId = $row['id'];
            $correctOpt = $row['correct_option'];
            // Check if the student's answer for this question ID matches
            if (isset($answers[$qId]) && $answers[$qId] === $correctOpt) {
                $score++;
            }
        }

        // 4. Save to quiz_scores (Matching your DB image exactly)
        $insert = $conn->prepare("INSERT INTO quiz_scores (student_id, quiz_id, score, total_marks) VALUES (?, ?, ?, ?)");
        $result = $insert->execute([$userId, $quizId, $score, $totalMarks]);

        if ($result) {
            // Redirect to dashboard.php (NOT index.php)
            header("Location: ../dashboard.php?status=quiz_completed&score=$score&total=$totalMarks");
            exit();
        }

    } catch (PDOException $e) {
        die("Database Exception: " . $e->getMessage());
    }
} else {
    die("Invalid request or session expired.");
}