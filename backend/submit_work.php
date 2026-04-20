<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Basic Security Check
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
        die("Unauthorized access.");
    }

    $assignment_id = $_POST['assignment_id'];
    $student_id = $_SESSION['user_id'];
    $text = $_POST['submission_text'] ?? '';
    $file_path = null;

    // 2. Handle File Upload
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) { 
            mkdir($upload_dir, 0777, true); 
        }

        $file_name = $_FILES['assignment_file']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types for safety
        $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg'];

        if (in_array($file_ext, $allowed_extensions)) {
            // Create a unique name to prevent overwriting
            $new_file_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_file)) {
                $file_path = $target_file;
            }
        } else {
            die("Error: Invalid file type. Please upload PDF, Word, or Image files.");
        }
    }

    try {
        // 3. Save to Database
        $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, submission_text, file_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$assignment_id, $student_id, $text, $file_path]);

        // 4. Smart Redirect
        // If class_id was passed in the form, use that; otherwise, try session
        $redirect_class = $_POST['class_id'] ?? $_SESSION['current_class_id'] ?? '';
        
        header("Location: ../assignments.php?class_id=" . $redirect_class . "&status=submitted");
        exit();
        
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}