<?php
$mysqli = new mysqli("127.0.0.1:3306", "root", "", "CGPA_Calculator");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch available semesters
$semesters = $mysqli->query("SELECT semester_id, semester_number, description FROM semesters ORDER BY semester_number");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $semester_id = $_POST['semester_id'];
    $grades = $_POST['grades'];

    // Check if grades already exist for the student in the selected semester
    $checkQuery = "SELECT COUNT(*) FROM grades WHERE student_id = ? AND course_id IN (SELECT course_id FROM courses WHERE semester_id = ?)";
    $stmt = $mysqli->prepare($checkQuery);
    $stmt->bind_param("ii", $student_id, $semester_id);
    $stmt->execute();
    $stmt->bind_result($existingGrades);
    $stmt->fetch();
    $stmt->close();

    if ($existingGrades > 0) {
        echo "<p class='error'>Grades for this semester have already been entered for this student.</p>";
    } else {
        // Insert grades for all courses in the selected semester
        foreach ($grades as $course_id => $grade) {
            if (!empty($grade)) { // Only add if grade is provided
                $stmt = $mysqli->prepare("INSERT INTO grades (student_id, course_id, grade) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $student_id, $course_id, $grade);
                $stmt->execute();
            }
        }

        echo "<p>Grades for Semester $semester_id added successfully!</p>";
    }
}

// Fetch courses based on selected semester (via AJAX)
if (isset($_GET['semester_id'])) {
    $semester_id = $_GET['semester_id'];
    $courses = $mysqli->query("SELECT course_id, course_name FROM courses WHERE semester_id = $semester_id");

    $response = [];
    while ($row = $courses->fetch_assoc()) {
        $response[] = $row;
    }
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Grades by Semester</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        // Fetch courses dynamically when a semester is selected
        function fetchCourses() {
            const semesterId = document.getElementById('semester_id').value;
            const courseContainer = document.getElementById('courses');

            fetch(`add_grades.php?semester_id=${semesterId}`)
                .then(response => response.json())
                .then(data => {
                    courseContainer.innerHTML = ''; // Clear previous courses
                    data.forEach(course => {
                        courseContainer.innerHTML += `
                            <label for="course_${course.course_id}">${course.course_name}:</label>
                            <input type="text" id="course_${course.course_id}" name="grades[${course.course_id}]" maxlength="2" placeholder="Grade (e.g., O, A+, A)" required>
                        `;
                    });
                });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Add Grades by Semester</h1>
        <form method="POST">
            <div style="width: 100%; max-width: 776px; margin: left;">
                <label for="student_id">Student ID:</label>
                <input type="number" id="student_id" name="student_id" required>
            </div>
            <label for="semester_id">Select Semester:</label>
            <select id="semester_id" name="semester_id" onchange="fetchCourses()" required>
                <option value="" disabled selected>Select a Semester</option>
                <?php while ($row = $semesters->fetch_assoc()): ?>
                    <option value="<?= $row['semester_id'] ?>">
                        Semester <?= $row['semester_number'] ?> - <?= $row['description'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <div style="width: 100%; max-width: 776px; margin: left;">
                <div id="courses"></div> <!-- Courses will load dynamically -->
            </div> 
            <button type="submit">Add Grades</button>
        </form>
        <a href="index.php" class="back-btn">Back</a>
    </div>
</body>
</html>
