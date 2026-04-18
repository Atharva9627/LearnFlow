<?php
session_start();
include 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'signup') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = $_POST['role'];

        try {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $pass, $role]);
            header("Location: ../auth.php?msg=success");
            exit();
        } catch (PDOException $e) {
            header("Location: ../auth.php?error=exists");
            exit();
        }

    } elseif ($action == 'login') {
        $email = $_POST['email'];
        $pass = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../dashboard.php");
            exit();
        } else {
            header("Location: ../auth.php?error=1");
            exit();
        }
    }
}
?>