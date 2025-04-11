<?php
session_start();


if (!isset($_SESSION['player_id'])) {
    http_response_code(403);
    exit('Not authorized');
}


$input = json_decode(file_get_contents('php://input'), true);
$flips = isset($input['flips']) ? (int)$input['flips'] : 0;
$won = isset($input['won']) ? (int)$input['won'] : null;


$_SESSION['flips_count'] = $flips;


if ($won !== null) {
    $_SESSION['game_won'] = $won;
}

echo json_encode(['success' => true]);