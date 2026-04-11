<?php
// 1. TURN ON ERRORS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if data actually arrived
    if (!isset($_POST['questions']) || !isset($_POST['class_id'])) {
        die("Error: No question data received from the form.");
    }

    $classId = $_POST['class_id'];
    $teacherId = $_SESSION['user_id'];
    $title = $_POST['quiz_title'];
    $questions = $_POST['questions']; 

    try {
        $pdo->beginTransaction();

        // 2. Insert the Quiz Header
        $stmt = $pdo->prepare("INSERT INTO quizzes (class_id, teacher_id, quiz_title) VALUES (?, ?, ?)");
        $stmt->execute([$classId, $teacherId, $title]);
        $quizId = $pdo->lastInsertId();

        // 3. Prepare the Question Insert
        $qStmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // 4. Loop through the array of questions
        foreach ($questions as $q) {
            $qStmt->execute([
                $quizId, 
                $q['text'], 
                $q['a'], 
                $q['b'], 
                $q['c'], 
                $q['d'], 
                $q['correct']
            ]);
        }

        $pdo->commit();
        
        // 5. Redirect back to dashboard on success
        header("Location: ../index.php?status=quiz_created");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        // This will print the error instead of a blank screen
        die("Database Error: " . $e->getMessage());
    }
} else {
    die("Invalid Request Method.");
}
?>