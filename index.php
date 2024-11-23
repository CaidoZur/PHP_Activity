<?php
// For Database connection
$dsn = 'mysql:host=localhost;dbname=quiz_db;charset=utf8mb4';
$username = 'root'; 
$password = '';     
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Failed to connect: " . $e->getMessage());
}

// Define questions and answers
$questions = [
    [
        "question" => "What does PHP stand for?",
        "options" => ["Personal Home Page", "Private Home Page", "PHP: Hypertext Preprocessor", "Public Hypertext Preprocessor"],
        "answer" => 2
    ],
    [
        "question" => "Which symbol is used to access a property of an object in PHP?",
        "options" => [".", "->", "::", "#"],
        "answer" => 1
    ],
    [
        "question" => "Which function is used to include a file in PHP?",
        "options" => ["include()", "require()", "import()", "load()"],
        "answer" => 0
    ]
];

// Initialize score
$score = 0;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? 'Anonymous';
    foreach ($questions as $index => $question) {
        if (isset($_POST["question$index"]) && $_POST["question$index"] == $question['answer']) {
            $score++;
        }
    }

// Save the score to the database
    $stmt = $pdo->prepare("INSERT INTO scores (username, score) VALUES (?, ?)");
    $stmt->execute([$username, $score]);

// Display the score
    echo "<h2>Your Score: $score/" . count($questions) . "</h2>";
    echo '<a href="index.php">Try Again</a>';

// Display the leaderboard
    echo "<h3>Leaderboard (Top 10)</h3>";
    $stmt = $pdo->query("SELECT username, score, submitted_at FROM scores ORDER BY score DESC, submitted_at ASC LIMIT 10");
    $leaderboard = $stmt->fetchAll();

    echo "<ol>";
    foreach ($leaderboard as $entry) {
        echo "<li>{$entry['username']} - {$entry['score']} (Submitted at: {$entry['submitted_at']})</li>";
    }
    echo "</ol>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Quiz</title>
    <script>
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('keydown', event => {
            if (event.key === 'F12' || (event.ctrlKey && event.shiftKey && event.key === 'I')) {
                event.preventDefault();
            }
        });
    </script>
</head>
<body>
    <h1>PHP Quiz</h1>
    <form method="post" action="">
        <label for="username">Enter your name:</label><br>
        <input type="text" id="username" name="username" placeholder="Your Name" required><br><br>
        <?php foreach ($questions as $index => $question): ?>
            <fieldset>
                <legend><?php echo $question['question']; ?></legend>
                <?php foreach ($question['options'] as $optionIndex => $option): ?>
                    <label>
                        <input type="radio" name="question<?php echo $index; ?>" value="<?php echo $optionIndex; ?>">
                        <?php echo $option; ?>
                    </label><br>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <input type="submit" value="Submit">
    </form>
</body>
</html>
