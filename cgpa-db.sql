-- 1. Create Database
CREATE DATABASE CGPA_Calculator;

-- 2. Use the created database
USE CGPA_Calculator;

-- 3. Create the necessary tables

-- Table for Semesters
CREATE TABLE semesters (
    semester_id INT PRIMARY KEY AUTO_INCREMENT,
    semester_number INT NOT NULL,
    description VARCHAR(50)
);

-- Table for Courses (Subjects)
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    semester_id INT,
    course_name VARCHAR(100) NOT NULL,
    credits INT NOT NULL,
    FOREIGN KEY (semester_id) REFERENCES semesters(semester_id) ON DELETE CASCADE
);

-- Table for Grades
CREATE TABLE grades (
    grade_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT,
    grade CHAR(2),  -- Stores grades like 'O', 'A+', 'A', etc.
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

-- Grade to Numeric Score Mapping Table
CREATE TABLE grade_scores (
    grade CHAR(2) PRIMARY KEY,
    score INT NOT NULL
);

-- 4. Insert grade mappings
INSERT INTO grade_scores (grade, score) VALUES
('O', 10),
('A+', 9),
('A', 8),
('B+', 7),
('B', 6),
('C', 5),
('U', 0);  -- U stands for "fail" or "unsatisfactory"

-- 5. Inserting data into semesters table
INSERT INTO semesters (semester_number, description) VALUES
(1, 'First Semester'),
(2, 'Second Semester'),
(3, 'Third Semester'),
(4, 'Fourth Semester'),
(5, 'Fifth Semester'),
(6, 'Sixth Semester'),
(7, 'Seventh Semester - Industrial Project'),
(8, 'Eighth Semester'),
(9, 'Ninth Semester'),
(10, 'Tenth Semester - Project Work');

-- 6. Inserting courses data with credits
INSERT INTO courses (semester_id, course_name, credits) VALUES
-- Semester 1
(1, 'Communicative English', 4),
(1, 'Matrices and Calculus', 4),
(1, 'Applied Physics', 3),
(1, 'Chemistry of Materials', 3),
(1, 'Digital Systems', 4),
(1, 'C Programming', 4),
(1, 'Engineering Drawing Lab', 2),
-- Semester 2
(2, 'Communicative English II', 4),
(2, 'Basic Electrical & Electronics', 4),
(2, 'Ordinary Differential Equations', 4),
(2, 'Python Programming', 4),
(2, 'Data Structures', 3),
(2, 'Computer Architecture', 3),
(2, 'Data Structures Lab', 2),
-- Semester 3
(3, 'Partial Differential Equations', 4),
(3, 'Object-Oriented Programming', 4),
(3, 'Database Management Systems', 3),
(3, 'Operating Systems', 4),
(3, 'Microprocessor', 4),
(3, 'Analog & Digital Communication', 3),
(3, 'DBMS Lab', 2),
-- Semester 4
(4, 'Discrete Structures', 4),
(4, 'Software Engineering', 3),
(4, 'Java Programming', 4),
(4, 'Computer Networks', 3),
(4, 'Environmental Science', 4),
(4, 'Algebra and Number Theory', 2),
(4, 'Java Lab', 2),
-- Semester 5
(5, 'Probability and Statistics', 4),
(5, 'Cryptography', 4),
(5, 'Data Warehousing', 4),
(5, 'Web Technology', 3),
(5, 'Theory of Computation', 4),
(5, 'Elective', 3),
-- Semester 6
(6, 'Operations Research', 4),
(6, 'Compiler Design', 3),
(6, 'Machine Learning', 4),
(6, 'Design & Analysis of Algorithms', 4),
(6, 'Cloud Computing', 3),
(6, 'Elective', 3),
-- Semester 7 (Industrial Project)
(7, 'Industrial Project', 16),
-- Semester 8
(8, 'Advanced Statistical Methods', 4),
(8, 'Networking Technologies', 3),
(8, 'Principles of Management', 3),
(8, 'Artificial Intelligence', 3),
(8, 'Cyber Security', 3),
(8, 'Elective', 3),
-- Semester 9
(9, 'Numerical Methods', 4),
(9, 'Internet of Things', 3),
(9, 'Digital Forensics', 3),
(9, 'Multimedia Technologies', 3),
(9, 'Elective', 3),
-- Semester 10 (Final Project)
(10, 'Project Work', 16);
