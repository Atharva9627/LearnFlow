# LearnFlow
LearnFlow 🎓 | Secure Full-Stack LMS
LearnFlow is a modern, full-stack Learning Management System designed to bridge the gap between teachers and students. Built with a focus on academic integrity and user experience, it features a secure quiz engine and a beautiful "Glassmorphism" dashboard.

✨ Key Features
Secure Quiz Engine: Implemented a backend "Hard Block" logic that strictly enforces a one-attempt-only policy for students.

Dynamic Gradebook: Real-time scoring and performance tracking for teachers, including percentage calculation and submission timestamps.

Modern UI/UX: A premium interface using Glassmorphism design principles (translucent cards, background blurs) and Lucide icons.

Classroom Management: Role-based access where teachers can create classes and students can join via class codes.

🛠️ Tech Stack
Backend: PHP 8.x (using PDO for secure, SQL-injection-proof database queries)

Database: MySQL (Relational schema for Users, Classes, and Results)

Frontend: HTML5, CSS3 (Advanced Flexbox/Grid), JavaScript

Server: Apache (WAMP/LAMP Stack)

🚀 Installation & Setup
Clone the Repository:

Bash
git clone https://github.com/Atharva9627/LearnFlow.git
Database Setup:

Open phpMyAdmin.

Create a database named learnflow_db.

Import the learnflow_db.sql file located in the /database folder.

Configure Connection:

Update backend/db_connect.php with your local database credentials (DB_NAME, DB_USER, DB_PASS).

Run Application:

Move the folder to your XAMPP htdocs or WAMP www directory.

Access via localhost/LearnFlow.

🛡️ Security Features
Session Management: Secure login sessions to prevent unauthorized page access.

Attempt Restriction: Backend verification ensures that once a quiz_id and student_id pair exists in the results table, no further submissions are accepted.
