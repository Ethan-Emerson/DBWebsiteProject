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

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch two random cities from the database
$sql = "SELECT CITY_NAME, CITY_POP, CITY_IMAGE FROM CITY ORDER BY RAND() LIMIT 2";
$result = $conn->query($sql);

$city1 = null;
$city2 = null;

if ($result && $result->num_rows === 2) { // Ensure query execution is successful
    $cities = $result->fetch_all(MYSQLI_ASSOC);
    $city1 = $cities[0];
    $city2 = $cities[1];
} else {
    echo "Error: Unable to fetch cities.";
}

$conn->close();

// Initialize scores in session if not already set
if (!isset($_SESSION['current_score'])) {
    $_SESSION['current_score'] = 0;
}
if (!isset($_SESSION['highest_score'])) {
    $_SESSION['highest_score'] = 0;
}

// Check if a guess was made
if (isset($_POST['guess'])) {
    $guess = $_POST['guess'];
    $correct = ($guess === 'higher' && $city2['CITY_POP'] > $city1['CITY_POP']) ||
               ($guess === 'lower' && $city2['CITY_POP'] < $city1['CITY_POP']);

    if ($correct) {
        $_SESSION['current_score']++;
        // Update the highest score if current score is higher
        if ($_SESSION['current_score'] > $_SESSION['highest_score']) {
            $_SESSION['highest_score'] = $_SESSION['current_score'];
        }
    } else {
        // Reset the current score on an incorrect guess
        $_SESSION['current_score'] = 0;
    }
}
?>

<!-- HTML Code Now -->
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic Metadata and Title info for HTML Page -->
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Higher or Lower California Populations</title>
</head>
<!-- Begin the body and and add all the data -->
<body>
    <h1>Higher or Lower!: California Populations!!!</h1>
    <h2>By Ethan Emerson, Jason Halabo, Reese Bos, & Ara Garabedian</h2>

    <!-- Grab current scores from PHP -->
    <div class="scores">
        <p>Current Score: <?= $_SESSION['current_score'] ?></p>
        <p>Highest Score: <?= $_SESSION['highest_score'] ?></p>
    </div>
  <!-- Grab the Populations and and othe data need to display on the main game page -->
    <?php if ($city1 && $city2): ?>
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
            <!-- Add the radio buttons for answer -->
            <button type="submit" name="guess" value="higher">Higher</button>
            <button type="submit" name="guess" value="lower">Lower</button>
        </form>
    <!-- Error Handeling -->
    <?php else: ?>
        <p>Error fetching cities. Please try again later.</p>
    <?php endif; ?>
</body>
</html>
