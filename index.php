<!-- Higher Or Lower City Populations over 100k: By Ethan Emerson, Jason Halabo, Reese Bos, and Ara Garabedia 12/12/2024-->
 <!--PHP Script to Handle all the logic of the game: This was done by Ara And Reese -->
 <!--HTML & CSS: This was done by Jason Halabo -->
 <!--SQL & DB: Ethan Emerson - Wrote and created all the tables and gathered all the data -->

 <!-- Initial Script -->
<?php
// Start a session to keep track of user-specific data, like scores, across page loads
session_start();

// Database Connection Details
// These variables store the information needed to connect to the database
$servername = "iss4014.cddnrt1zeyhj.us-east-1.rds.amazonaws.com"; // The database server's address
$username = "admin"; // Username to access the database
$password = "password"; // Password to access the database
$dbname = "game"; // Name of the database we are using

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection to the database failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Stop everything and show an error if the connection failed
}

// Handle the form submission when a player enters their name after losing
if (isset($_POST['player_name']) && isset($_SESSION['highest_score'])) {
    $playerName = $_POST['player_name']; // Get the player's name from the form
    $highestScore = $_SESSION['highest_score']; // Retrieve the highest score stored in the session

    // Prepare an SQL query to insert the player's name and score into the database
    $stmt = $conn->prepare("INSERT INTO PLAYER (P_NAME, P_SCORE) VALUES (?, ?)");
    $stmt->bind_param("si", $playerName, $highestScore); // Bind the variables to the SQL query
    $stmt->execute(); // Execute the query to save the player's data in the database
    $stmt->close(); // Close the prepared statement to free resources

    // Reset the current score to start a new game
    $_SESSION['current_score'] = 0;

    // Notify the player that their score has been saved
    echo "<p>Score saved! Thanks for playing, $playerName!</p>";
}

// Select two random cities from the database for the game
$sql = "SELECT CITY_NAME, CITY_POP, CITY_IMAGE FROM CITY ORDER BY RAND() LIMIT 2"; // Query to get random cities
$result = $conn->query($sql); // Execute the query

$city1 = null;
$city2 = null;

// Check if exactly two cities were returned
if ($result && $result->num_rows === 2) {
    $cities = $result->fetch_all(MYSQLI_ASSOC); // Fetch the city data as an associative array
    $city1 = $cities[0]; // The first city
    $city2 = $cities[1]; // The second city
}

// Fetch the overall highest score from the database
$overallHighestScore = 0; // Initialize the overall highest score
$highestScoreQuery = "SELECT MAX(P_SCORE) AS max_score FROM PLAYER"; // Query to get the highest score
$highestScoreResult = $conn->query($highestScoreQuery);
if ($highestScoreResult && $highestScoreResult->num_rows > 0) {
    $row = $highestScoreResult->fetch_assoc(); // Fetch the highest score
    $overallHighestScore = $row['max_score'];
}

$conn->close(); // Close the database connection

// Initialize session variables for scores if they don't exist
if (!isset($_SESSION['current_score'])) {
    $_SESSION['current_score'] = 0; // Start the current score at 0
}
if (!isset($_SESSION['highest_score'])) {
    $_SESSION['highest_score'] = 0; // Start the highest score at 0
}

// Handle the user's guess (Higher or Lower)
if (isset($_POST['guess'])) {
    $guess = $_POST['guess']; // Get the user's guess (higher or lower)

    // Check if the user's guess was correct
    $correct = ($guess === 'higher' && $city2['CITY_POP'] > $city1['CITY_POP']) ||
               ($guess === 'lower' && $city2['CITY_POP'] < $city1['CITY_POP']);

    if ($correct) {
        // If correct, increment the current score
        $_SESSION['current_score']++;
        // Update the highest score if the current score is greater
        if ($_SESSION['current_score'] > $_SESSION['highest_score']) {
            $_SESSION['highest_score'] = $_SESSION['current_score'];
        }
    } else {
        // If incorrect, reset only the current score
        $showForm = true; // Show the form to save the player's name
        $_SESSION['current_score'] = 0;
    }
}
?>


<!DOCTYPE html> <!-- Declare the document as an HTML5 document -->
<html lang="en"> <!-- Specify the language as English -->
<head>
    <meta charset="UTF-8"> <!-- Ensure text is displayed correctly -->
    <link rel="stylesheet" href="styles.css"> <!-- Link to an external CSS file for styling -->
    <title>Higher or Lower California Populations</title> <!-- Set the title of the web page -->
</head>
<body>
    <!-- Header Section -->
    <h1>Higher or Lower!: California Populations!!!</h1>
    <h2>By Ethan Emerson, Jason Halabo, Reese Bos, & Ara Garabedian</h2>

    <!-- Display the current, user's highest, and overall highest scores -->
    <div class="scores">
        <p>Current Score: <?= $_SESSION['current_score'] ?></p> <!-- Display the current score -->
        <p>Your Highest Score: <?= $_SESSION['highest_score'] ?></p> <!-- Display the user's highest score -->
        <p>Overall Highest Score: <?= $overallHighestScore ?></p> <!-- Display the overall highest score -->
    </div>

    <?php if (isset($showForm) && $showForm): ?>
        <!-- Form to save the user's highest score after losing -->
        <form method="POST"> <!-- Form sends data using the POST method -->
            <p>Oh no! You lost. Save your highest score!</p>
            <label for="player_name">Your Name:</label>
            <input type="text" id="player_name" name="player_name" required> <!-- Input for the player's name -->
            <button type="submit">Save Score</button> <!-- Submit button -->
        </form>
    <?php elseif ($city1 && $city2): ?>
        <!-- Gameplay interface -->
        <form method="POST">
            <div class="game-container">
                <!-- Display the first city -->
                <div class="city">
                    <h3><?= htmlspecialchars($city1['CITY_NAME']) ?></h3> <!-- City 1 name -->
                    <p>Population: <?= htmlspecialchars($city1['CITY_POP']) ?></p> <!-- City 1 population -->
                    <img src="<?= htmlspecialchars($city1['CITY_IMAGE']) ?>" alt="<?= htmlspecialchars($city1['CITY_NAME']) ?>" class="city-image"> <!-- City 1 image -->
                </div>
                <!-- Display the second city -->
                <div class="city">
                    <h3><?= htmlspecialchars($city2['CITY_NAME']) ?></h3> <!-- City 2 name -->
                    <p>Population: ???</p> <!-- Population hidden for guessing -->
                    <img src="<?= htmlspecialchars($city2['CITY_IMAGE']) ?>" alt="<?= htmlspecialchars($city2['CITY_NAME']) ?>" class="city-image"> <!-- City 2 image -->
                </div>
            </div>
            <!-- Buttons for the user to make a guess -->
            <button type="submit" name="guess" value="higher">Higher</button>
            <button type="submit" name="guess" value="lower">Lower</button>
        </form>
    <?php else: ?>
        <!-- Error message if cities cannot be fetched -->
        <p>Error fetching cities. Please try again later.</p>
    <?php endif; ?>
</body>
</html>
