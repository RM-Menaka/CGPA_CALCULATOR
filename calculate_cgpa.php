<?php
$mysqli = new mysqli("127.0.0.1:3306", "root", "", "CGPA_Calculator");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$student_id = null;
$cgpa = 0;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
        $student_id = $_POST['student_id'];

        // Check if the student ID exists in the database
        $checkStudentQuery = "SELECT COUNT(*) FROM grades WHERE student_id = ?";
        $stmt = $mysqli->prepare($checkStudentQuery);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            // Student exists, calculate CGPA
            $query = "
                SELECT s.semester_number, c.credits, gs.score
                FROM grades g
                JOIN courses c ON g.course_id = c.course_id
                JOIN semesters s ON c.semester_id = s.semester_id
                LEFT JOIN grade_scores gs ON g.grade = gs.grade
                WHERE g.student_id = ?
                ORDER BY s.semester_number
            ";

            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Variables to calculate CGPA
            $total_credits = 0;
            $weighted_score = 0;

            while ($row = $result->fetch_assoc()) {
                $score = $row['score'];
                $credits = $row['credits'];
                $weighted_score += $score * $credits;
                $total_credits += $credits;
            }

            $stmt->close();

            if ($total_credits > 0) {
                $cgpa = $weighted_score / $total_credits;
            }
        } else {
            // Student ID does not exist
            $error_message = "Student ID not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculate CGPA</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Calculate CGPA</h1>
        <form method="POST">
            <div style="width: 100%; max-width: 776px; margin: left;">
                <label for="student_id">Student ID:</label>
                <input type="number" id="student_id" name="student_id" required>
            </div>
            <button type="submit">Calculate</button>
        </form>

        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif (isset($student_id)): ?>
            <p>Student ID: <?= htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8') ?></p>
            <p>Your CGPA: <strong><?= number_format($cgpa, 2) ?></strong></p>
        <?php endif; ?>

        <a href="index.php" class="back-btn">Back</a>
    </div>
</body>
</html>
