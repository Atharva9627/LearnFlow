<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Capture Form Data
    $class_id    = $_POST['class_id'];
    $title       = $_POST['title'];
    $description = $_POST['description'];
    
    // 2. Capture the Date String (e.g., 2026-04-21T14:30)
    $due_date    = $_POST['due_date']; 

    // 3. Validation: Check if the date is actually set
    if (empty($due_date)) {
        die("Error: Please select a valid due date and time.");
    }

    try {
        // 4. Insert into the database
        $stmt = $conn->prepare("INSERT INTO assignments (class_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$class_id, $title, $description, $due_date]);

        // 5. Redirect back to assignments page with success status
        header("Location: ../assignments.php?class_id=" . $class_id . "&status=success");
        exit();
        
    } catch (PDOException $e) {
        // This helps catch if your table structure doesn't match
        die("Database Error: " . $e->getMessage());
    }
}
?>