<?php
// 1. TURN ON ERRORS FOR DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['questions']) || !isset($_POST['class_id'])) {
        die("Error: No question data received. Ensure your form uses name='questions[]'");
    }

    $classId = $_POST['class_id'];
    $teacherId = $_SESSION['user_id'] ?? 0;
    
    // MATCHED TO YOUR DB: Column in image_59a244.jpg is 'quiz_title'
    $quizTitle = $_POST['quiz_title'] ?? 'Untitled Quiz';

    $questionTexts = $_POST['questions'];
    $optsA = $_POST['options_a'];
    $optsB = $_POST['options_b'];
    $optsC = $_POST['options_c'];
    $optsD = $_POST['options_d'];
    $correctOpts = $_POST['correct_options'];

    try {
        $conn->beginTransaction();

        // 2. Insert the Quiz Header
        // FIX: Changed 'title' to 'quiz_title' to match image_59a244.jpg
        $stmt = $conn->prepare("INSERT INTO quizzes (class_id, teacher_id, quiz_title) VALUES (?, ?, ?)");
        $stmt->execute([$classId, $teacherId, $quizTitle]);
        $quizId = $conn->lastInsertId();

        // 3. Prepare Question Insert
        $qStmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($questionTexts as $i => $text) {
            if (empty($text)) continue; 

            $qStmt->execute([
                $quizId, 
                $text, 
                $optsA[$i], 
                $optsB[$i], 
                $optsC[$i], 
                $optsD[$i], 
                $correctOpts[$i]
            ]);
        }

        $conn->commit();
        header("Location: ../assignments.php?status=quiz_created&class_id=" . $classId);
        exit();

    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        die("Database Error: " . $e->getMessage());
    }
} else {
    die("Invalid Request Method.");
}
?>