<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'teacher') {
    $class_id = $_POST['class_id'];
    $type = $_POST['type']; // 'announcement', 'assignment', or 'quiz'
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO class_materials (class_id, type, title, content) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$class_id, $type, $title, $content])) {
        header("Location: ../dashboard.php?success=posted");
    } else {
        header("Location: ../dashboard.php?error=failed");
    }
}
?>