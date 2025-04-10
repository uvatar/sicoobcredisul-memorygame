<?php
session_start();

// Make sure the player is registered
if (!isset($_SESSION['player_id'])) {
    http_response_code(403);
    exit('Not authorized');
}

// Get the flips count from the request
$input = json_decode(file_get_contents('php://input'), true);
$flips = isset($input['flips']) ? (int)$input['flips'] : 0;
$won = isset($input['won']) ? (int)$input['won'] : null;

// Update the session with the new flips count
$_SESSION['flips_count'] = $flips;

// Update the won status if provided
if ($won !== null) {
    $_SESSION['game_won'] = $won;
}

echo json_encode(['success' => true]);