<!-- Higher Or Lower City Populations over 100k: By Ethan Emerson, Jason Halabo, Reese Bos, and Ara Garabedia 12/12/2024-->
 <!--PHP Script to Handle all the logic of the game: This was done by Ara And Reese -->
 <!--HTML & CSS: This was done by Jason Halabo -->
 <!--SQL & DB: Ethan Emerson - Wrote and created all the tables and gathered all the data -->

 <!-- Initial Script -->
<?php
// Start a session to track the score
session_start();

// Database Connection
$servername = "iss4014.cddnrt1zeyhj.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "password";
$dbname = "game";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the player submitted their name after losing
if (isset($_POST['player_name']) && isset($_SESSION['highest_score'])) {
    $playerName = $_POST['player_name'];
    $highestScore = $_SESSION['highest_score'];

    // Save the player's highest score to the database
    $stmt = $conn->prepare("INSERT INTO PLAYER (P_NAME, P_SCORE) VALUES (?, ?)");
    $stmt->bind_param("si", $playerName, $highestScore);
    $stmt->execute();
    $stmt->close();

    // Only reset the current score, not the highest score
    $_SESSION['current_score'] = 0;

    // Notify the player that their score was saved
    echo "<p>Score saved! Thanks for playing, $playerName!</p>";
}

// Fetch two random cities from the database for gameplay
$sql = "SELECT CITY_NAME, CITY_POP, CITY_IMAGE FROM CITY ORDER BY RAND() LIMIT 2";
$result = $conn->query($sql);

$city1 = null;
$city2 = null;

if ($result && $result->num_rows === 2) {
    // Fetch and store the city data
    $cities = $result->fetch_all(MYSQLI_ASSOC);
    $city1 = $cities[0];
    $city2 = $cities[1];
}

// Fetch the overall highest score from the database
$overallHighestScore = 0;
$highestScoreQuery = "SELECT MAX(P_SCORE) AS max_score FROM PLAYER";
$highestScoreResult = $conn->query($highestScoreQuery);
if ($highestScoreResult && $highestScoreResult->num_rows > 0) {
    $row = $highestScoreResult->fetch_assoc();
    $overallHighestScore = $row['max_score'];
}

$conn->close();

// Initialize session variables for scores if not already set
if (!isset($_SESSION['current_score'])) {
    $_SESSION['current_score'] = 0;
}
if (!isset($_SESSION['highest_score'])) {
    $_SESSION['highest_score'] = 0; // Do not reset if the page reloads
}

// Handle the user's guess
if (isset($_POST['guess'])) {
    $guess = $_POST['guess'];
    $correct = ($guess === 'higher' && $city2['CITY_POP'] > $city1['CITY_POP']) ||
               ($guess === 'lower' && $city2['CITY_POP'] < $city1['CITY_POP']);

    if ($correct) {
        // Increment the current score and update the highest score if necessary
        $_SESSION['current_score']++;
        if ($_SESSION['current_score'] > $_SESSION['highest_score']) {
            $_SESSION['highest_score'] = $_SESSION['current_score'];
        }
    } else {
        // Reset only the current score, not the highest score
        $showForm = true; // Show the form to save the player's name
        $_SESSION['current_score'] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Higher or Lower California Populations</title>
</head>
<body>
    <h1>Higher or Lower!: California Populations!!!</h1>
    <h2>By Ethan Emerson, Jason Halabo, Reese Bos, & Ara Garabedian</h2>

    <!-- Display the current, user's highest, and overall highest scores -->
    <div class="scores">
        <p>Current Score: <?= $_SESSION['current_score'] ?></p>
        <p>Your Highest Score: <?= $_SESSION['highest_score'] ?></p>
        <p>Overall Highest Score: <?= $overallHighestScore ?></p>
    </div>

    <?php if (isset($showForm) && $showForm): ?>
        <!-- Form to save the user's highest score after losing -->
        <form method="POST">
            <p>Oh no! You lost. Save your highest score!</p>
            <label for="player_name">Your Name:</label>
            <input type="text" id="player_name" name="player_name" required>
            <button type="submit">Save Score</button>
        </form>
    <?php elseif ($city1 && $city2): ?>
        <!-- Gameplay interface -->
        <form method="POST">
            <div class="game-container">
                <div class="city">
                    <h3><?= htmlspecialchars($city1['CITY_NAME']) ?></h3>
                    <p>Population: <?= htmlspecialchars($city1['CITY_POP']) ?></p>
                    <img src="<?= htmlspecialchars($city1['CITY_IMAGE']) ?>" alt="<?= htmlspecialchars($city1['CITY_NAME']) ?>" class="city-image">
                </div>
                <div class="city">
                    <h3><?= htmlspecialchars($city2['CITY_NAME']) ?></h3>
                    <p>Population: ???</p>
                    <img src="<?= htmlspecialchars($city2['CITY_IMAGE']) ?>" alt="<?= htmlspecialchars($city2['CITY_NAME']) ?>" class="city-image">
                </div>
            </div>
            <button type="submit" name="guess" value="higher">Higher</button>
            <button type="submit" name="guess" value="lower">Lower</button>
        </form>
    <?php else: ?>
        <!-- Error message if cities cannot be fetched -->
        <p>Error fetching cities. Please try again later.</p>
    <?php endif; ?>
</body>
</html>
