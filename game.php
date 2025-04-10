<?php
session_start();

// Redirect if user hasn't registered
if (!isset($_SESSION['player_id'])) {
    header('Location: register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Game</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Memory Game</h1>
        <div class="game-info">
            <p>Flips: <span id="flips">0</span></p>
        </div>
        
        <div class="game-board">
            <div class="card" data-index="0"></div>
            <div class="card" data-index="1"></div>
            <div class="card" data-index="2"></div>
            <div class="card" data-index="3"></div>
            <div class="card center fixed" data-index="4">
                <div class="card-inner">
                    <div class="card-front"></div>
                    <div class="card-back">
                        <img src="images/center.png" alt="Center Card">
                    </div>
                </div>
            </div>
            <div class="card" data-index="5"></div>
            <div class="card" data-index="6"></div>
            <div class="card" data-index="7"></div>
            <div class="card" data-index="8"></div>
        </div>
        
        <div class="next-button-container" style="display: none;">
            <form method="post" action="result.php">
                <input type="hidden" id="flips_count_input" name="flips_count" value="0">
                <button type="submit" class="btn next-btn">View Result</button>
            </form>
        </div>
    </div>
    
    <script src="js/game.js"></script>
</body>
</html>