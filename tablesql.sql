CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(255),
    student_name VARCHAR(255),
    semester INT,
    subject VARCHAR(255),
    question VARCHAR(255),
    rating INT
);
