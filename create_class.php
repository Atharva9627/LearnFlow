<?php
session_start();
// Ensure the path to your db_connect matches your folder structure
require 'backend/db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: auth.php");
    exit();
}

$error = "";

// 1. Logic to handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $className = trim($_POST['class_name']);
    $teacherId = $_SESSION['user_id'];
    
    // Generate a random 6-character alphanumeric code
    $inviteCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    if (!empty($className)) {
        try {
            // Using $conn as defined in your db_connect
            $stmt = $conn->prepare("INSERT INTO classes (class_name, teacher_id, invite_code) VALUES (?, ?, ?)");
            $stmt->execute([$className, $teacherId, $inviteCode]);
            
            // Redirect back to my_classes.php with success
            header("Location: my_classes.php?status=success&code=$inviteCode");
            exit();
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a class name.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Class | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .create-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }
        .create-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 10px; color: var(--text-dim); }
        .input-group input {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
        }
        .btn-row { display: flex; gap: 10px; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="app-container">
        <main class="main-content">
            <div class="create-container">
                <div class="create-card">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <i data-lucide="plus-circle" style="color: var(--primary); width: 48px; height: 48px;"></i>
                        <h2 style="margin-top: 10px;">Create New Class</h2>
                        <p style="color: var(--text-dim);">Students will use an invite code to join.</p>
                    </div>

                    <?php if ($error): ?>
                        <div style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="input-group">
                            <label for="class_name">Class Name</label>
                            <input type="text" id="class_name" name="class_name" placeholder="e.g. Grade 10 Mathematics" required autofocus>
                        </div>

                        <div class="btn-row">
                            <button type="submit" class="btn-primary" style="flex: 2; cursor: pointer; border: none;">Create Class</button>
                            <a href="my_classes.php" style="flex: 1; text-align: center; text-decoration: none; padding: 12px; background: rgba(255,255,255,0.1); color: white; border-radius: 8px;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>