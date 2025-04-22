<?php
function initDatabase() {
    $dbFile = __DIR__ . '/db/game_data.db';
    $dbDir = dirname($dbFile);
    
    if (!file_exists($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    $db = new SQLite3($dbFile);
    
    
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
            won INTEGER NOT NULL DEFAULT 0,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (player_id) REFERENCES players(id)
        )
    ');
    
    $db->exec('
        CREATE TABLE IF NOT EXISTS game_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_name TEXT NOT NULL UNIQUE,
            setting_value TEXT NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    
    $stmt = $db->prepare('SELECT setting_value FROM game_settings WHERE setting_name = :name');
    $stmt->bindValue(':name', 'max_flips');
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if (!$result) {
        $db->exec('INSERT INTO game_settings (setting_name, setting_value) VALUES ("max_flips", "12")');
    }
    
    return $db;
}