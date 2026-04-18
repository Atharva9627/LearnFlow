<?php
// 1. Enable error reporting so we can see if the database connection fails
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php'; // Ensure this file exists in the same folder!

// 2. Check if the user is logged in and if the form was actually submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    // Get the code from the form and clean it up
    $inviteCode = strtoupper(trim($_POST['invite_code'])); 
    $studentId = $_SESSION['user_id'];

    try {
        // 3. Step One: Check if this class code actually exists in our 'classes' table
        $stmt = $conn->prepare("SELECT id FROM classes WHERE invite_code = ?");
        $stmt->execute([$inviteCode]);
        $class = $stmt->fetch();

        if ($class) {
            $classId = $class['id'];

            // 4. Step Two: Add the student to the 'enrollments' table
            // 'INSERT IGNORE' prevents an error if the student tries to join the same class twice
            $enrollStmt = $conn->prepare("INSERT IGNORE INTO enrollments (student_id, class_id) VALUES (?, ?)");
            $result = $enrollStmt->execute([$studentId, $classId]);

            if ($result) {
                // Success! Send them back to the dashboard with a success message
                header("Location: ../index.php?status=joined");
                exit();
            } else {
                echo "Critical Error: Could not save the enrollment to the database.";
            }
        } else {
            // If the code doesn't exist in the database
            header("Location: ../index.php?status=invalid_code");
            exit();
        }

    } catch (PDOException $e) {
        // This will tell us if the 'enrollments' table is missing
        die("Database Error: " . $e->getMessage());
    }
} else {
    // If someone tries to access this file directly without the form
    header("Location: ../index.php");
    exit();
}
?>