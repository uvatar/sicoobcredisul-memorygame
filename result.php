<?php
require_once 'database-init.php';
session_start();


if (!isset($_SESSION['player_id'])) {
    header('Location: register.php');
    exit;
}


$flipsCount = isset($_POST['flips_count']) ? (int)$_POST['flips_count'] : ($_SESSION['flips_count'] ?? 0);
$gameWon = isset($_POST['game_won']) ? (int)$_POST['game_won'] : 0;


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


$stmt = $db->prepare('SELECT name FROM players WHERE id = :id');
$stmt->bindValue(':id', $playerId, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
$playerName = $result ? $result['name'] : 'Player';


$stmt = $db->prepare('SELECT setting_value FROM game_settings WHERE setting_name = :name');
$stmt->bindValue(':name', 'max_flips');
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
$maxFlips = $result ? (int)$result['setting_value'] : 12;

include('header.php');
?>
<body class="resultado">
    <div class="container">
        
        <div class="result-container">
            <?php if ($gameWon): ?>
                <img src="images/img-vitoria.svg" alt="" class="img-vitoria">
            <?php else: ?>
                <img src="images/img-derrota.svg" alt="" class="img-derrota">
            <?php endif; ?>
            
            <div class="result-actions">
                <a href="index.php" class="btn restart-btn">
                    <img src="images/img-voltar.svg" alt="" class="img-voltar">
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php

session_destroy();
?>