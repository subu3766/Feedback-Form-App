
<?php
session_start(); // Start the session

$host = 'localhost';
$dbname = 'feedback_system';
$username = 'admin';
$password = 'admin123';

// Create connection
$conn = mysqli_connect($host, $username, $password, 'feedback');
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Subject and Feedback Question List
$subjects = [
    1 => ['Mathematics I', 'Physics', 'C Programming', 'Electricals', 'Mechanical'],
    2 => ['Mathematics II', 'Electronics', 'Engineering Chemistry', 'Python Programming', 'English'],
    3 => ['Mathematics III', 'Data Structures', 'Operating Systems', 'Computer Organizations', 'Unix Shell Programming'],
    4 => ['Mathematics IV', 'OOP Using JAVA', 'Data Communications', 'Design Analysis Using C', 'Professional Ethics'],
    5 => ['Theory Of Computations', 'Database Management Systems', 'Artificial Intelligence', 'Research Methodology', 'Environmental Science'],
];

$feedbackQuestions = [
    'Understanding of the subject',
    'Teaching effectiveness',
    'Overall satisfaction',
    '.',
    '.',
    '.',
    '.',
    '.',
    '.',
    '.'
];

// Initialize session variables if not set
if (!isset($_SESSION['studentName'])) {
    $_SESSION['studentName'] = '';
    $_SESSION['studentID'] = '';
    $_SESSION['selectedSemesters'] = [];
    $_SESSION['currentSemester'] = 0;
    $_SESSION['currentSubjectIndex'] = 0;
    $_SESSION['feedbackSubmitted'] = false;
    $_SESSION['totalSubjects'] = 0;
    $_SESSION['feedbackGiven'] = 0;
    $_SESSION['subjectAverages'] = [];
    $_SESSION['overallAverage'] = 0;
}

$studentName = $_SESSION['studentName'];
$studentID = $_SESSION['studentID'];
$selectedSemesters = $_SESSION['selectedSemesters'];
$currentSemester = $_SESSION['currentSemester'];
$currentSubjectIndex = $_SESSION['currentSubjectIndex'];
$feedbackSubmitted = $_SESSION['feedbackSubmitted'];
$totalSubjects = $_SESSION['totalSubjects'];
$feedbackGiven = $_SESSION['feedbackGiven'];
$subjectAverages = $_SESSION['subjectAverages'];
$overallAverage = $_SESSION['overallAverage'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$feedbackSubmitted) {
    if (isset($_POST['studentName']) && isset($_POST['studentID'])) {
        $studentName = $_POST['studentName'];
        $studentID = $_POST['studentID'];
        $_SESSION['studentName'] = $studentName;
        $_SESSION['studentID'] = $studentID;
    }

    if (isset($_POST['semesters'])) {
        $selectedSemesters = array_map('intval', (array)$_POST['semesters']);
        $_SESSION['selectedSemesters'] = $selectedSemesters;
        $currentSemester = reset($selectedSemesters);
        $_SESSION['currentSemester'] = $currentSemester;

        // Calculate total subjects
        foreach ($selectedSemesters as $semester) {
            $totalSubjects += count($subjects[$semester]);
        }
        $_SESSION['totalSubjects'] = $totalSubjects;
    }

    // Handle feedback submission for a specific subject
    if (isset($_POST['feedback'])) {
        $studentIDHash = hash('sha256', $studentID);
        $studentNameHash = hash('sha256', $studentName);
        $subject = $subjects[$currentSemester][$currentSubjectIndex];
        foreach ($_POST['feedback'][$subject] as $question => $rating) {
            $stmt = $conn->prepare("INSERT INTO feedback (student_id_hash, student_name_hash, semester, subject, question, rating) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisii", $studentIDHash, $studentNameHash, $currentSemester, $subject, $question, $rating);
            $stmt->execute();
        }
        $stmt->close();
        $feedbackGiven++;
        $_SESSION['feedbackGiven'] = $feedbackGiven;

        // Move to the next subject or the next semester
        $currentSubjectIndex++;
        if ($currentSubjectIndex >= count($subjects[$currentSemester])) {
            $currentSubjectIndex = 0;
            $currentSemester = next($selectedSemesters);
        }
        $_SESSION['currentSubjectIndex'] = $currentSubjectIndex;
        $_SESSION['currentSemester'] = $currentSemester;

        // If all semesters and subjects are completed
        if ($currentSemester === false) {
            $_SESSION['feedbackSubmitted'] = true;
            $feedbackSubmitted = true;

            // Calculate the average percentage for each subject and overall
            $totalFeedback = 0;
            $totalPossible = 0;

            foreach ($selectedSemesters as $semester) {
                foreach ($subjects[$semester] as $subject) {
                    $subjectFeedback = $conn->prepare("SELECT SUM(rating) AS totalRating, COUNT(rating) AS totalResponses FROM feedback WHERE semester = ? AND subject = ?");
                    $subjectFeedback->bind_param("is", $semester, $subject);
                    $subjectFeedback->execute();
                    $subjectFeedback->bind_result($totalRating, $totalResponses);
                    $subjectFeedback->fetch();
                    $subjectFeedback->close();

                    $maxPossible = count($feedbackQuestions) * 5 * $totalResponses; // 5 possible ratings per question per response
                    $subjectAverage = $totalResponses > 0 ? ($totalRating / $maxPossible) * 100 : 0;
                    $subjectAverages[$semester][$subject] = round($subjectAverage, 2);

                    $totalFeedback += $totalRating;
                    $totalPossible += $maxPossible;
                }
            }

            $overallAverage = $totalPossible > 0 ? ($totalFeedback / $totalPossible) * 100 : 0;
            $_SESSION['overallAverage'] = round($overallAverage, 2);
            $_SESSION['subjectAverages'] = $subjectAverages;
        }
    }
} elseif (isset($_GET['reset']) && $_GET['reset'] == 1) {
    // Clear session to allow new feedback submission
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Insights Webpage</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f8ff 0%, #ffe4e1 50%, #f0fff0 100%);
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
            animation: backgroundShift 8s ease infinite alternate;
        }

        @keyframes backgroundShift {
            0% {
                background-color: #f0fff0;
            }
            50% {
                background-color: #ffe4e1;
            }
            100% {
                background-color: #f0f8ff;
            }
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
            transition: transform 0.5s ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container:hover {
            transform: scale(1.02);
        }

        .content {
            flex: 1;
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

        input[type="text"], select, input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 2px solid #dcdcdc;
            border-radius: 10px;
            font-size: 1rem;
            color: #333;
            animation: fadeInUp 0.8s ease;
        }

        button {
            padding: 12px 20px;
            font-size: 1.2rem;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ff6348 100%);
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
            animation: fadeInUp 0.8s ease;
            display: block;
            margin: 0 auto;
        }

        button:hover {
            background: linear-gradient(135deg, #ff5141 0%, #ff8a65 100%);
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-40px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(40px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 40px 0;
            animation: fadeInUp 0.9s ease;
        }

        .stats-table th, .stats-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 1.1rem;
            animation: fadeIn 1s ease-in-out;
        }

        .stats-table th {
            background-color: #f4f4f4;
        }

        .reset-link {
            text-decoration: none;
            color: #ff5141;
            font-weight: bold;
            animation: fadeIn 1.2s ease;
        }

        .reset-link:hover {
            text-decoration: underline;
        }

        .progress-bar {
            height: 25px;
            background-color: #f4f4f4;
            border-radius: 10px;
            overflow: hidden;
            animation: fadeInUp 0.8s ease;
        }

        .progress-bar span {
            display: block;
            height: 100%;
            background: linear-gradient(135deg, #ff6b6b 0%, #ff5141 100%);
            animation: progress 2s ease-in-out;
        }

        @keyframes progress {
            0% {
                width: 0;
            }
            100% {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <h1>Academic Insights Webpage</h1>

        <?php if (!$feedbackSubmitted): ?>
            <?php if (empty($studentName) || empty($studentID)): ?>
                <form action="" method="POST">
                    <label for="studentName">Student Name:</label>
                    <input type="text" id="studentName" name="studentName" required>
                    <label for="studentID">Student ID:</label>
                    <input type="text" id="studentID" name="studentID" required>
                    <button type="submit">Next</button>
                
            <?php elseif (empty($selectedSemesters)): ?>
                <form action="" method="POST">
                    <label for="semesters">Select Semester(s):</label>
                    <select id="semesters" name="semesters[]" multiple required>
                        <?php foreach ($subjects as $semester => $subjectList): ?>
                            <option value="<?php echo $semester; ?>">Semester <?php echo $semester; ?></option>
                        <?php endforeach; ?>
                        </form>
                    </select>
                    <button type="submit">Next</button>
                </form>
            <?php else: ?>
                <h2>Semester <?php echo $currentSemester; ?>: <?php echo $subjects[$currentSemester][$currentSubjectIndex]; ?></h2>
                <form action="" method="POST">
                    <input type="hidden" name="currentSemester" value="<?php echo $currentSemester; ?>">
                    <input type="hidden" name="currentSubjectIndex" value="<?php echo $currentSubjectIndex; ?>">
                    <?php foreach ($feedbackQuestions as $index => $question): ?>
                        <label><?php echo htmlspecialchars($question); ?></label>
                        <select name="feedback[<?php echo htmlspecialchars($subjects[$currentSemester][$currentSubjectIndex]); ?>][<?php echo $index; ?>]" required>
                        <option value="">Select Rating</option>
                            <option value="1">Very Poor</option>
                            <option value="2">Poor</option>
                            <option value="3">Average</option>
                            <option value="4">Good</option>
                            <option value="5">Excellent</option>
                        </select>
                    <?php endforeach; ?>
                    <button type="submit">Submit Insights</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <h2>Insights Summary</h2>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th>Subject</th>
                        <th>Average Rating (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjectAverages as $semester => $subjects): ?>
                        <?php foreach ($subjects as $subject => $average): ?>
                            <tr>
                                <td><?php echo $semester; ?></td>
                                <td><?php echo $subject; ?></td>
                                <td><?php echo $average; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Overall Average: <?php echo $overallAverage; ?>%</h3>
            <a href="?reset=1" class="reset-link">Initiate New Insights</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
