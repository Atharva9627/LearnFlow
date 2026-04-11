<?php
session_start();
require 'backend/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id, class_name FROM classes WHERE teacher_id = ?");
$stmt->execute([$userId]);
$myClasses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Quiz | LearnFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">Learn<span class="flow-text">Flow</span></div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="assignments.php" class="nav-item active"><i data-lucide="clipboard-list"></i> Assignments</a>
                <a href="backend/logout.php" class="nav-item logout-link"><i data-lucide="log-out"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar"><h1>Create Multi-Question Quiz</h1></header>

            <div class="card" style="max-width: 800px; margin: 20px auto;">
                <form action="backend/save_quiz.php" method="POST" id="quizForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                        <div>
                            <label>Target Class</label>
                            <select name="class_id" required style="width:100%; padding:12px; margin-top:8px; border-radius:8px; background:#1e1e2e; color:white; border:1px solid #333;">
                                <?php foreach ($myClasses as $class): ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Quiz Title</label>
                            <input type="text" name="quiz_title" placeholder="e.g., Final Exam" required>
                        </div>
                    </div>

                    <div id="questions-container">
                        <div class="question-item card" style="background: rgba(255,255,255,0.02); margin-bottom: 20px; border: 1px solid #333;">
                            <h3>Question 1</h3>
                            <input type="text" name="questions[0][text]" placeholder="Enter Question" required style="margin: 10px 0;">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <input type="text" name="questions[0][a]" placeholder="Option A" required>
                                <input type="text" name="questions[0][b]" placeholder="Option B" required>
                                <input type="text" name="questions[0][c]" placeholder="Option C" required>
                                <input type="text" name="questions[0][d]" placeholder="Option D" required>
                            </div>

                            <label style="display:block; margin-top:10px;">Correct Answer:</label>
                            <select name="questions[0][correct]" required style="width:100%; padding:10px; border-radius:8px; background:#1e1e2e; color:white;">
                                <option value="A">A</option><option value="B">B</option>
                                <option value="C">C</option><option value="D">D</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="button" onclick="addQuestion()" class="btn-secondary" style="background: #333; flex: 1;">
                            <i data-lucide="plus-circle"></i> Add Another Question
                        </button>
                        <button type="submit" class="btn-primary" style="flex: 1;">Save Quiz</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        let questionCount = 1;
        function addQuestion() {
            const container = document.getElementById('questions-container');
            const div = document.createElement('div');
            div.className = 'question-item card';
            div.style = "background: rgba(255,255,255,0.02); margin-bottom: 20px; border: 1px solid #333;";
            
            div.innerHTML = `
                <h3>Question ${questionCount + 1}</h3>
                <input type="text" name="questions[${questionCount}][text]" placeholder="Enter Question" required style="margin: 10px 0;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <input type="text" name="questions[${questionCount}][a]" placeholder="Option A" required>
                    <input type="text" name="questions[${questionCount}][b]" placeholder="Option B" required>
                    <input type="text" name="questions[${questionCount}][c]" placeholder="Option C" required>
                    <input type="text" name="questions[${questionCount}][d]" placeholder="Option D" required>
                </div>
                <label style="display:block; margin-top:10px;">Correct Answer:</label>
                <select name="questions[${questionCount}][correct]" required style="width:100%; padding:10px; border-radius:8px; background:#1e1e2e; color:white;">
                    <option value="A">A</option><option value="B">B</option>
                    <option value="C">C</option><option value="D">D</option>
                </select>
            `;
            container.appendChild(div);
            questionCount++;
            lucide.createIcons(); // Re-render icons
        }
        lucide.createIcons();
    </script>
</body>
</html>