<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'teacher') {
    $className = $_POST['class_name'];
    $teacherId = $_SESSION['user_id'];
    
    // Generate a random 6-character alphanumeric code
    $inviteCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    try {
        $stmt = $pdo->prepare("INSERT INTO classes (class_name, teacher_id, invite_code) VALUES (?, ?, ?)");
        $stmt->execute([$className, $teacherId, $inviteCode]);
        
        // Redirect back to dashboard with a success message
        header("Location: ../index.php?status=class_created&code=$inviteCode");
        exit();
    } catch (PDOException $e) {
        header("Location: ../index.php?status=error");
        exit();
    }
}
?>