<?php
require_once 'database-init.php';
session_start();

// Redirect if user hasn't registered
if (!isset($_SESSION['player_id'])) {
    header('Location: register.php');
    exit;
}

// Get flips count and won status from POST data or session
$flipsCount = isset($_POST['flips_count']) ? (int)$_POST['flips_count'] : ($_SESSION['flips_count'] ?? 0);
$gameWon = isset($_POST['game_won']) ? (int)$_POST['game_won'] : 0;

// Save the game result to the database
$db = initDatabase();
$playerId = $_SESSION['player_id'];

$stmt = $db->prepare('
    INSERT INTO game_results (player_id, flips_count, won)
    VALUES (:player_id, :flips_count, :won)
');

$stmt->bindValue(':player_id', $playerId, SQLITE3_INTEGER);
$stmt->bindValue(':flips_count', $flipsCount, SQLITE3_INTEGER);
$stmt->bindValue(':won', $gameWon, SQLITE3_INTEGER);
$stmt->execute();

// Get player name
$stmt = $db->prepare('SELECT name FROM players WHERE id = :id');
$stmt->bindValue(':id', $playerId, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
$playerName = $result ? $result['name'] : 'Player';

// Get max flips setting
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
    <title>Game Result - Memory Game</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Game Result</h1>
        
        <div class="result-container">
            <?php if ($gameWon): ?>
                <h2>Congratulations, <?= htmlspecialchars($playerName) ?>!</h2>
                <p>You completed the memory game in <strong><?= $flipsCount ?></strong> flips!</p>
            <?php else: ?>
                <h2>Game Over, <?= htmlspecialchars($playerName) ?>!</h2>
                <p>You used all <strong><?= $maxFlips ?></strong> flips without finding all pairs.</p>
                <p>Better luck next time!</p>
            <?php endif; ?>
            
            <div class="result-actions">
                <a href="index.php" class="btn restart-btn">Play Again</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Clear the session when player sees the results
session_destroy();
?>