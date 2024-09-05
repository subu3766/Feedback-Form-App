CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id_hash VARCHAR(255) NOT NULL,
    student_name_hash VARCHAR(255) NOT NULL,
    semester INT,
    subject VARCHAR(255),
    question VARCHAR(255),
    rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
