<?php
$host = 'localhost';
$dbname = 'feedback_system';
$username = 'your_username';
$password = 'your_password';

// Create connection
$conn = mysqli_connect('localhost', 'admin', 'admin123', 'feedback');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Subject and Feedback Question List
$subjects = [
    1 => ['Mathematics', 'Physics', 'Chemistry', 'English', 'History'],
    2 => ['Biology', 'Geography', 'Economics', 'Computer Science', 'Philosophy'],
    3 => ['Psychology', 'Sociology', 'Political Science', 'Statistics', 'Business'],
    4 => ['Engineering', 'Medicine', 'Law', 'Art', 'Music'],
    5 => ['Advanced Mathematics', 'Quantum Physics', 'Organic Chemistry', 'Creative Writing', 'World History'],
];

$feedbackQuestions = [
    'Understanding of the subject',
    'Teaching effectiveness',
    'Availability of resources',
    'Clarity of the material',
    'Overall satisfaction'
];

$studentName = '';
$studentID = '';
$selectedSemesters = [];
$feedbackSubmitted = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['studentName']) && isset($_POST['studentID'])) {
        $studentName = $_POST['studentName'];
        $studentID = $_POST['studentID'];
    }

    if (isset($_POST['semesters'])) {
        $selectedSemesters = array_map('intval', $_POST['semesters']);
    }

    if (isset($_POST['feedback'])) {
        foreach ($_POST['feedback'] as $semester => $subjectsFeedback) {
            foreach ($subjectsFeedback as $subject => $questions) {
                foreach ($questions as $question => $rating) {
                    $stmt = $conn->prepare("INSERT INTO feedback (student_id, student_name, semester, subject, question, rating) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssisii", $studentID, $studentName, $semester, $subject, $question, $rating);
                    $stmt->execute();
                }
            }
        }
        $stmt->close();
        $feedbackSubmitted = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback Form</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f8ff 0%, #ffe4e1 50%, #f0fff0 100%);
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #ffe4e1 50%, #f0fff0 100%);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .container::before, .container::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            z-index: -1;
            animation: spin 10s linear infinite;
        }

        .container::before {
            top: -75px;
            right: -75px;
            background: radial-gradient(circle at center, #ff6b6b, #ff6348);
        }

        .container::after {
            bottom: -75px;
            left: -75px;
            background: radial-gradient(circle at center, #4b7bec, #273c75);
            animation-direction: reverse;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        h1, h2, h3, h4 {
            font-family: 'Georgia', serif;
            color: #333;
        }

        h1 {
            font-size: 3rem;
            background: linear-gradient(135deg, #ff6b6b 0%, #ff6348 100%);
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 40px;
            animation: fadeIn 1s ease-in-out;
        }

        h2 {
            color: #34495e;
            font-size: 2rem;
            margin-bottom: 20px;
            animation: fadeInDown 0.6s ease;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
            animation: fadeInUp 0.8s ease;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
            background-color: #e8f0fe;
            transition: background-color 0.3s ease;
            animation: fadeIn 1s ease;
        }

        input[type="text"]:focus,
        select:focus {
            background-color: #dfe9ff;
        }

        input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.3);
            cursor: pointer;
            animation: bounce 0.6s ease;
        }

        input[type="submit"] {
            width: 100%;
            background: linear-gradient(135deg, #6ab04c 0%, #badc58 100%);
            color: #fff;
            border: none;
            padding: 15px;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            animation: pulse 1.2s infinite;
        }

        input[type="submit"]:hover {
            background-color: #78e08f;
            transform: translateY(-3px);
        }

        .feedback-question {
            margin-bottom: 15px;
            animation: fadeInUp 0.5s ease;
        }

        .feedback-question select {
            width: auto;
            display: inline-block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .feedback-submitted {
            text-align: center;
            padding: 50px;
            background: linear-gradient(135deg, #6ab04c 0%, #78e08f 100%);
            color: #fff;
            border-radius: 10px;
            animation: bounceIn 0.6s ease;
        }

        .feedback-submitted h2 {
            font-size: 2.5rem;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes bounceIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            h1 {
                font-size: 2.2rem;
            }

            input[type="submit"] {
                font-size: 1rem;
                padding: 12px;
            }

            .feedback-submitted h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Student Feedback Form</h1>

        <!-- Step 1: Display Student Name and ID Form -->
        <?php if (!$studentName && !$studentID) : ?>
            <form method="POST">
                <label for="studentName">Student Name:</label>
                <input type="text" id="studentName" name="studentName" required>

                <label for="studentID">Student ID:</label>
                <input type="text" id="studentID" name="studentID" required>

                <input type="submit" value="Next">
            </form>
        <?php endif; ?>

        <!-- Step 2: Display Semester Selection -->
        <?php if ($studentName && $studentID && empty($selectedSemesters) && !$feedbackSubmitted) : ?>
            <h2>Welcome, <?php echo htmlspecialchars($studentName); ?> (ID: <?php echo htmlspecialchars($studentID); ?>)</h2>
            <form method="POST">
                <input type="hidden" name="studentName" value="<?php echo htmlspecialchars($studentName); ?>">
                <input type="hidden" name="studentID" value="<?php echo htmlspecialchars($studentID); ?>">

                <label>Select Semester(s) for Feedback:</label>
                <?php for ($i = 1; $i <= 5; $i++) : ?>
                    <label>
                        <input type="checkbox" name="semesters[]" value="<?php echo $i; ?>"> Semester <?php echo $i; ?>
                    </label>
                <?php endfor; ?>

                <input type="submit" value="Next">
            </form>
        <?php endif; ?>

        <!-- Step 3: Display Feedback Form for Selected Semesters -->
        <?php if (!empty($selectedSemesters) && !$feedbackSubmitted) : ?>
            <h2>Provide Feedback for the Selected Semesters</h2>
            <form method="POST">
                <input type="hidden" name="studentName" value="<?php echo htmlspecialchars($studentName); ?>">
                <input type="hidden" name="studentID" value="<?php echo htmlspecialchars($studentID); ?>">

                <?php foreach ($selectedSemesters as $semester) : ?>
                    <h3>Feedback for Semester <?php echo $semester; ?></h3>
                    <?php foreach ($subjects[$semester] as $subject) : ?>
                        <h4><?php echo $subject; ?></h4>
                        <?php foreach ($feedbackQuestions as $question) : ?>
                            <div class="feedback-question">
                                <label for="question-<?php echo md5($semester . $subject . $question); ?>">
                                    <?php echo $question; ?>
                                </label>
                                <select name="feedback[<?php echo $semester; ?>][<?php echo $subject; ?>][<?php echo $question; ?>]" id="question-<?php echo md5($semester . $subject . $question); ?>" required>
                                    <?php for ($i = 0; $i <= 5; $i++) : ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <input type="submit" value="Submit Feedback">
            </form>
        <?php endif; ?>

        <!-- Feedback Submission Confirmation -->
        <?php if ($feedbackSubmitted) : ?>
            <div class="feedback-submitted">
                <h2>Thank you, <?php echo htmlspecialchars($studentName); ?>!</h2>
                <p>Your feedback has been submitted successfully.</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>

<?php
$conn->close();
?>
