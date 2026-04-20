<?php
// 1. TURN ON ERRORS FOR DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php'; // This uses the $conn variable from your fixed db_connect.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if basic data arrived - matched to your HTML form names
    if (!isset($_POST['questions']) || !isset($_POST['class_id'])) {
        die("Error: No question data received from the form. Check if your HTML inputs use name='questions[]'");
    }

    $classId = $_POST['class_id'];
    $teacherId = $_SESSION['user_id'] ?? 0;
    // Note: Ensure your 'quizzes' table column is 'title' or 'quiz_title'
    $title = $_POST['quiz_title'] ?? 'Untitled Quiz';

    // Capture the flat arrays from your HTML form
    $questionTexts = $_POST['questions'];
    $optsA = $_POST['options_a'];
    $optsB = $_POST['options_b'];
    $optsC = $_POST['options_c'];
    $optsD = $_POST['options_d'];
    $correctOpts = $_POST['correct_options'];

    try {
        // Use the $conn variable defined in db_connect.php
        $conn->beginTransaction();

        // 2. Insert the Quiz Header
        // Double-check if your column is named 'title' based on previous errors
        $stmt = $conn->prepare("INSERT INTO quizzes (class_id, teacher_id, title) VALUES (?, ?, ?)");
        $stmt->execute([$classId, $teacherId, $title]);
        $quizId = $conn->lastInsertId();

        // 3. Prepare Question Insert (Matching your exact DB columns)
        // Columns: quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option
        $qStmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // 4. Loop using index to sync all arrays
        foreach ($questionTexts as $i => $text) {
            if (empty($text)) continue; // Skip empty rows

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
        
        // Redirect back to assignments or dashboard
        header("Location: ../assignments.php?status=quiz_created&class_id=" . $classId);
        exit();

    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        // Detailed error output to help you fix any remaining schema issues
        die("Database Error: " . $e->getMessage());
    }
} else {
    die("Invalid Request Method.");
}
?>