<?php
function initDatabase() {
    $dbFile = __DIR__ . '/db/game_data.db';
    $dbDir = dirname($dbFile);
    
    if (!file_exists($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    $db = new SQLite3($dbFile);
    
    // Create tables if they don't exist
    $db->exec('
        CREATE TABLE IF NOT EXISTS players (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            ssn TEXT NOT NULL,
            phone TEXT NOT NULL,
            terms_accepted INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    $db->exec('
        CREATE TABLE IF NOT EXISTS game_results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            player_id INTEGER NOT NULL,
            flips_count INTEGER NOT NULL,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (player_id) REFERENCES players(id)
        )
    ');
    
    return $db;
}
