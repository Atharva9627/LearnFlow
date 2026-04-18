<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'teacher') {
    $class_id = $_POST['class_id'];
    $content = $_POST['content'];
    $type = $_POST['type'];

    $stmt = $conn->prepare("INSERT INTO announcements (class_id, content, type) VALUES (?, ?, ?)");
    if ($stmt->execute([$class_id, $content, $type])) {
        header("Location: ../assignments.php?class_id=" . $class_id);
    } else {
        echo "Error posting announcement.";
    }
}
?>