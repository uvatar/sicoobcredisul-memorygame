<?php
session_start();
require_once 'database-init.php';

// Redirect if user hasn't registered
if (!isset($_SESSION['player_id'])) {
    header('Location: register.php');
    exit;
}

// Get maximum flips setting
$db = initDatabase();
$stmt = $db->prepare('SELECT setting_value FROM game_settings WHERE setting_name = :name');
$stmt->bindValue(':name', 'max_flips');
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
$maxFlips = $result ? (int)$result['setting_value'] : 12;
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
            <p>Flips: <span id="flips">0</span>/<span id="max-flips"><?= $maxFlips ?></span></p>
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
        
        <div id="game-message" class="game-message" style="display: none;"></div>
        
        <div class="next-button-container" style="display: none;">
            <form method="post" action="result.php">
                <input type="hidden" id="flips_count_input" name="flips_count" value="0">
                <input type="hidden" id="game_won_input" name="game_won" value="1">
                <button type="submit" class="btn next-btn">View Result</button>
            </form>
        </div>
    </div>
    
    <script>
        // Pass PHP variable to JavaScript
        const MAX_FLIPS = <?= $maxFlips ?>;
    </script>
    <script src="js/game.js"></script>
</body>
</html>