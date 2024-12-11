<!-- Higher Or Lower City Populations over 100k: By Ethan Emerson, Jason Halabo, Reese Bos, and Ara Garabedia 12/12/2024-->
 <!--PHP Script to Handle all the logic of the game: This was done by Ara And Reese -->
 <!--HTML & CSS: This was done by Jason Halabo -->
 <!--SQL & DB: Ethan Emerson - Wrote and created all the tables and gathered all the data -->

 <!-- Initial Script -->
<?php
session_start(); //keep track of current data: like scores, across page loads

//setup database variables
$servername = "iss4014.cddnrt1zeyhj.us-east-1.rds.amazonaws.com"; 
$username = "admin";
$password = "password";
$dbname = "game";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {  //check and see if it worked, if it didn't die
    die("Connection failed: " . $conn->connect_error);
}

// Handle the form submission when a player enters their name after losing
if (isset($_POST['player_name']) && isset($_SESSION['highest_score'])) {

    //Get user variables
    $playerName = $_POST['player_name'];
    $highestScore = $_SESSION['highest_score'];

   /* // Prepare an SQL query to insert the player's name and score into the database
    $stmt = $conn->prepare("INSERT INTO PLAYER (P_NAME, P_SCORE) VALUES (?, ?)");
    $stmt->bind_param("si", $playerName, $highestScore); // Bind the variables to the SQL query
    $stmt->execute(); // Execute the query to save the player's data in the database
    $stmt->close(); // Close the prepared statement to free <resources></resources>*/

    //Query the database
    $sql = "INSERT INTO PLAYER (P_NAME, P_SCORE) VALUES ('$playerName', '$highestScore');";
    $conn->query($sql);
    $conn->close();

    //reset current variables since the user lost and then let them know their stuff was saved
    $_SESSION['current_score'] = 0;
    echo "<p>Score saved! Thanks for playing, $playerName!</p>";
}

// Select two random cities from the database for the game and then query the database
$sql = "SELECT CITY_NAME, CITY_POP, CITY_IMAGE FROM CITY ORDER BY RAND() LIMIT 2";
$result = $conn->query($sql);
$city1 = null;
$city2 = null;

// Check if exactly two cities were returned and then grab the data
if ($result && $result->num_rows === 2) {
    $cities = $result->fetch_all(MYSQLI_ASSOC);
    $city1 = $cities[0];
    $city2 = $cities[1]; 
}

// Grab the highest score from the database to compare to the current player
$overallHighestScore = 0; 
$highestScoreQuery = "SELECT MAX(P_SCORE) AS max_score FROM PLAYER"; 
$highestScoreResult = $conn->query($highestScoreQuery);
if ($highestScoreResult && $highestScoreResult->num_rows > 0) {
    $row = $highestScoreResult->fetch_assoc();
    $overallHighestScore = $row['max_score'];
}
$conn->close();

// Essentially this code creates some sesion variables if they don't already exist (so like if the player loads into the game for the first time)
if (!isset($_SESSION['current_score'])) {
    $_SESSION['current_score'] = 0;
}
if (!isset($_SESSION['highest_score'])) {
    $_SESSION['highest_score'] = 0;
}

//This block handles what the user guesses (higher or lower radio button )
if (isset($_POST['guess'])) {
    $guess = $_POST['guess']; 

    // Check if the user's guess was correct
    $correct = false;
    echo "<p>" . $city1['CITY_POP'] ." ".$city2['CITY_POP'] . "</p>";
    if($guess == 'higher')
    {
        if($city2['CITY_POP'] > $city1['CITY_POP']) //correct higher guess
        {
            $correct = true;
        }
    }
    else //player guesses lower
    { 
        if($city2['CITY_POP'] < $city1['CITY_POP']) //correct lower guess
        {
            $correct = true;
        }
    }
   /* $correct = ($guess === 'higher' && $city2['CITY_POP'] > $city1['CITY_POP']) ||
               ($guess === 'lower' && $city2['CITY_POP'] < $city1['CITY_POP']);*/

    //Correct guess, increment the score and update the highest score if needed.
    if ($correct) {
        $_SESSION['current_score']++;
        if ($_SESSION['current_score'] > $_SESSION['highest_score']) {
            $_SESSION['highest_score'] = $_SESSION['current_score'];
        }
    } else { //incorrect guess, tell the person they are wrong and set their current score back to zero
        $showForm = true; 
        $_SESSION['current_score'] = 0;
    }
    $_POST['guess'] = null;
    }
?>

<!-- Basic setup stuff, like languages and charset and stylesheet linking -->
<!DOCTYPE html> 
<html lang="en"> 
<head>
    <meta charset="UTF-8"> 
    <link rel="stylesheet" href="styles.css">
    <title>Higher or Lower California Populations</title>
</head>
<body>
    <!-- Body Section of the HTML: The first two things below are the headers that show our project name and our names-->
    <h1>Higher or Lower!: California Populations!!!</h1>
    <h2>By Ethan Emerson, Jason Halabo, Reese Bos, & Ara Garabedian</h2>

    <!-- This stuff right here grabs the player's current score from the ongoing session stuff and displays it -->
    <div class="scores">
        <p>Current Score: <?= $_SESSION['current_score'] ?></p> 
        <p>Your Highest Score: <?= $_SESSION['highest_score'] ?></p> 
        <p>Overall Highest Score: <?= $overallHighestScore ?></p> 
    </div>
    
    <!-- This script right here activates when someone loses or guesses incorrectly and the form pops up to save their progress -->
    <?php if (isset($showForm) && $showForm): ?>
        <form method="POST"> 
            <p>Oh no! You lost. Save your highest score!</p>
            <label for="player_name">Your Name:</label>
            <input type="text" id="player_name" name="player_name" required>
            <button type="submit">Save Score</button>
        </form>

        <!-- This Script and HTML elements are here if the person first starts the game or they are still allive after guess correctly -->
    <?php elseif ($city1 && $city2): ?>
        <form method="POST">
            <div class="game-container"> <!-- this div element here helps us seperate the cities or make them appear side by side -->
                
            <!-- Display the first city -->
                <div class="city">
                    <h3><?= htmlspecialchars($city1['CITY_NAME']) ?></h3>
                    <p>Population: <?= htmlspecialchars($city1['CITY_POP']) ?></p> 
                    <img src="<?= htmlspecialchars($city1['CITY_IMAGE']) ?>" alt="<?= htmlspecialchars($city1['CITY_NAME']) ?>" class="city-image"> <!-- City 1 image -->
                </div>

                <!-- Display the second city -->
                <div class="city">
                    <h3><?= htmlspecialchars($city2['CITY_NAME']) ?></h3> 
                    <p>Population: ???</p> <!-- hidden for guessing (yay)-->
                    <img src="<?= htmlspecialchars($city2['CITY_IMAGE']) ?>" alt="<?= htmlspecialchars($city2['CITY_NAME']) ?>" class="city-image"> <!-- City 2 image -->
                </div>
            </div>

            <!-- Buttons for the user to make a guess -->
            <button type="submit" name="guess" value="higher">Higher</button>
            <button type="submit" name="guess" value="lower">Lower</button>
        </form>

    <!-- these quick php scrips are used if the city is not fetched -->
    <?php else: ?>
        <p>Error fetching cities. Please try again later.</p>
    <?php endif; ?>
</body>
</html>
