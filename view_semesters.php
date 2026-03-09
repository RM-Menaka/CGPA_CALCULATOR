<?php
$mysqli = new mysqli("127.0.0.1:3306", "root","", "CGPA_Calculator");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT s.semester_id, s.semester_number, s.description, c.course_name, c.credits
          FROM semesters s
          LEFT JOIN courses c ON s.semester_id = c.semester_id
          ORDER BY s.semester_number, c.course_name";
$result = $mysqli->query($query);

// Group courses by semester
$semesters = [];
while ($row = $result->fetch_assoc()) {
    $semesters[$row['semester_id']]['semester_number'] = $row['semester_number'];
    $semesters[$row['semester_id']]['description'] = $row['description'];
    $semesters[$row['semester_id']]['courses'][] = [
        'course_name' => $row['course_name'],
        'credits' => $row['credits']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semesters & Courses</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <div class="container">
        <h1>Semesters & Courses</h1>
        
        <?php foreach ($semesters as $semester_id => $semester): ?>
            <div class="semester">
                <h2>Semester <?= $semester['semester_number'] ?> - <?= $semester['description'] ?></h2>
                <?php if (!empty($semester['courses'])): ?>
                    <ul class="course-list">
                        <?php foreach ($semester['courses'] as $course): ?>
                            <li class="course-item">
                                <span class="course-name"><?= $course['course_name'] ?></span> 
                                <span class="course-credits">(<?= $course['credits'] ?> credits)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No courses available for this semester.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <a href="index.php" class="back-btn">Back</a>
    </div>
</body>
</html>
