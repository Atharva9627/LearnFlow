<?php
session_start();
require 'backend/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: auth.php");
    exit();
}

$userRole = $_SESSION['role'];
$userName = $_SESSION['user_name'];
$current_class_id = $_GET['class_id'] ?? null;

// Fetch all classes for the dropdown if needed, but prioritize the current one
$stmt = $conn->prepare("SELECT id, class_name FROM classes WHERE teacher_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacherClasses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Quiz | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .quiz-builder-container { max-width: 900px; margin: 0 auto; }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .header-section { margin-bottom: 40px; text-align: center; }
        .header-section h1 { font-size: 2.5rem; margin-bottom: 10px; background: linear-gradient(to right, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { color: var(--text-dim); font-size: 0.9rem; font-weight: 500; }
        .input-group select, .input-group input, .input-group textarea {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 16px;
            border-radius: 12px;
            color: white;
            outline: none;
            transition: border 0.3s;
        }
        .input-group input:focus { border-color: var(--primary); }

        .question-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            position: relative;
        }
        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        .btn-add { background: transparent; border: 2px dashed rgba(255,255,255,0.1); color: var(--text-dim); width: 100%; padding: 15px; border-radius: 12px; cursor: pointer; transition: 0.3s; margin-bottom: 30px; }
        .btn-add:hover { border-color: var(--primary); color: white; background: rgba(99, 102, 241, 0.05); }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="my_classes.php" class="nav-item"><i data-lucide="book-open"></i> My Classes</a>
                <?php if ($current_class_id): ?>
                    <div class="sidebar-divider"></div>
                    <a href="create_quiz.php?class_id=<?= $current_class_id ?>" class="nav-item active"><i data-lucide="plus-circle"></i> Create Quiz</a>
                    <a href="assignments.php?class_id=<?= $current_class_id ?>" class="nav-item"><i data-lucide="clipboard-list"></i> Assignments</a>
                    <a href="leaderboard.php?class_id=<?= $current_class_id ?>" class="nav-item">
                            <i data-lucide="trophy"></i> Leaderboard
                        </a>
                <?php endif; ?>
                <a href="backend/logout.php" class="nav-item logout-link"><i data-lucide="log-out"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="quiz-builder-container">
                <div class="header-section">
                    <h1>Create Multi-Question Quiz</h1>
                    <p style="color: var(--text-dim);">Design an engaging quiz for your students.</p>
                </div>

                <form action="backend/save_quiz.php" method="POST" id="quizForm">
                    <div class="glass-card">
                        <div class="form-row">
                            <div class="input-group">
                                <label>Target Class</label>
                                <select name="class_id" required>
                                    <?php foreach ($teacherClasses as $class): ?>
                                        <option value="<?= $class['id'] ?>" <?= ($current_class_id == $class['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($class['class_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Quiz Title</label>
                                <input type="text" name="quiz_title" placeholder="e.g., Final Semester Exam" required>
                            </div>
                        </div>

                        <div id="questions-wrapper">
                            <div class="question-card">
                                <h3 style="margin-bottom: 15px; font-size: 1.1rem; color: var(--primary);">Question 1</h3>
                                <div class="input-group">
                                    <textarea name="questions[]" rows="2" placeholder="Enter your question text here..." required></textarea>
                                </div>
                                <div class="options-grid">
                                    <input type="text" name="options_a[]" placeholder="Option A" required>
                                    <input type="text" name="options_b[]" placeholder="Option B" required>
                                    <input type="text" name="options_c[]" placeholder="Option C" required>
                                    <input type="text" name="options_d[]" placeholder="Option D" required>
                                </div>
                                <div class="input-group" style="margin-top: 15px;">
                                    <label>Correct Answer</label>
                                    <select name="correct_options[]">
                                        <option value="A">Option A</option>
                                        <option value="B">Option B</option>
                                        <option value="C">Option C</option>
                                        <option value="D">Option D</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn-add" id="addQuestionBtn">
                            <i data-lucide="plus-circle" style="vertical-align: middle; margin-right: 8px;"></i>
                            Add Another Question
                        </button>

                        <button type="submit" class="btn-primary" style="width: 100%; padding: 18px; border: none; font-weight: bold; font-size: 1rem; cursor: pointer;">
                            Save & Publish Quiz
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        
        let questionCount = 1;
        document.getElementById('addQuestionBtn').addEventListener('click', () => {
            questionCount++;
            const wrapper = document.getElementById('questions-wrapper');
            const newCard = document.createElement('div');
            newCard.className = 'question-card';
            newCard.innerHTML = `
                <h3 style="margin-bottom: 15px; font-size: 1.1rem; color: var(--primary);">Question ${questionCount}</h3>
                <div class="input-group">
                    <textarea name="questions[]" rows="2" placeholder="Enter your question text here..." required></textarea>
                </div>
                <div class="options-grid">
                    <input type="text" name="options_a[]" placeholder="Option A" required>
                    <input type="text" name="options_b[]" placeholder="Option B" required>
                    <input type="text" name="options_c[]" placeholder="Option C" required>
                    <input type="text" name="options_d[]" placeholder="Option D" required>
                </div>
                <div class="input-group" style="margin-top: 15px;">
                    <label>Correct Answer</label>
                    <select name="correct_options[]">
                        <option value="A">Option A</option>
                        <option value="B">Option B</option>
                        <option value="C">Option C</option>
                        <option value="D">Option D</option>
                    </select>
                </div>
            `;
            wrapper.appendChild(newCard);
            lucide.createIcons();
        });
    </script>
</body>
</html>