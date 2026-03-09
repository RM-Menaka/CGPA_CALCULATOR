<?php
$mysqli = new mysqli("127.0.0.1:3306", "root", "", "CGPA_Calculator");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$student_id = null;
$gpa = 0;
$semesters = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if student_id is provided via the form
    if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
        $student_id = $_POST['student_id'];

        // Fetch the grades and credits for the student across all semesters
        $query = "
            SELECT s.semester_number, s.description, c.course_name, c.credits, g.grade, gs.score
            FROM grades g
            JOIN courses c ON g.course_id = c.course_id
            JOIN semesters s ON c.semester_id = s.semester_id
            LEFT JOIN grade_scores gs ON g.grade = gs.grade
            WHERE g.student_id = ?
            ORDER BY s.semester_number, c.course_name
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Initialize variables for GPA calculation
        $total_credits = 0;
        $weighted_score = 0;

        while ($row = $result->fetch_assoc()) {
            $semester_number = $row['semester_number'];
            $semesters[$semester_number]['semester_number'] = $row['semester_number'];
            $semesters[$semester_number]['description'] = $row['description'];

            // Calculate weighted score
            $score = $row['score'];
            $credits = $row['credits'];
            $weighted_score += $score * $credits;
            $total_credits += $credits;

            // Store semester-wise GPA calculation
            $semesters[$semester_number]['courses'][] = [
                'course_name' => $row['course_name'],
                'credits' => $credits,
                'grade' => $row['grade'],
                'score' => $score
            ];
        }
        $stmt->close();

        // Calculate Overall GPA
        if ($total_credits > 0) {
            $gpa = $weighted_score / $total_credits;
        } else {
            $gpa = 0; // In case no grades are entered yet
        }
    } else {
        echo "<p class='error'>Please enter a valid student ID.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View GPA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #555;
            margin-bottom: 20px;
        }

        .semester {
            margin-bottom: 30px;
        }

        .gpa-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .gpa-table th, .gpa-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .gpa-table th {
            background-color: #f2f2f2;
            color: #333;
        }

        .gpa-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .gpa-table tr:hover {
            background-color: #e9e9e9;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .gpa-form {
            text-align: center;
            margin-bottom: 30px;
        }

        .gpa-form input {
            padding: 8px;
            font-size: 16px;
            width: 200px;
            margin-right: 10px;
        }

        .gpa-form button {
            padding: 8px 16px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .gpa-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View GPA</h1>

        <!-- Form to enter student ID -->
        <div class="gpa-form">
            <form method="POST" action="">
                <label for="student_id">Enter Student ID:</label>
                <input type="number" id="student_id" name="student_id" required>
                <button type="submit">View GPA</button>
            </form>
        </div>

        <?php if ($student_id !== null): ?>
            <?php if (!empty($semesters)): ?>
                <h2>Student ID: <?= htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8') ?></h2>
                <h2>Overall GPA: <?= number_format($gpa, 2) ?></h2>

                <?php foreach ($semesters as $semester): ?>
                    <div class="semester">
                        <h3>Semester <?= $semester['semester_number'] ?> - <?= $semester['description'] ?></h3>
                        <table class="gpa-table">
                            <thead>
                                <tr>
                                    <th>Course Name</th>
                                    <th>Credits</th>
                                    <th>Grade</th>
                                    <th>Grade Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($semester['courses'] as $course): ?>
                                    <tr>
                                        <td><?= $course['course_name'] ?></td>
                                        <td><?= $course['credits'] ?></td>
                                        <td><?= $course['grade'] ?></td>
                                        <td><?= $course['score'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: red;">No grades found for this student ID.</p>
            <?php endif; ?>
        <?php endif; ?>

        <a href="index.php" class="back-btn">Back</a>
    </div>
</body>
</html>
