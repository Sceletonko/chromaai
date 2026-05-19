<?php
require_once 'db.php';

try {
    $pdo = get_db_connection();
    
    // Add name column to users table if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'name'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN name VARCHAR(255) AFTER id");
        echo "Column 'name' added successfully.\n";
    } else {
        echo "Column 'name' already exists.\n";
    }

    // Ensure is_verified column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0");
        echo "Column 'is_verified' added successfully.\n";
    }

    // Ensure verification_code column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'verification_code'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN verification_code VARCHAR(10) NULL");
        echo "Column 'verification_code' added successfully.\n";
    }

    // Create chats table
    $pdo->exec("CREATE TABLE IF NOT EXISTS chats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255),
        model VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )");
    echo "Table 'chats' verified/created.\n";

    // Create messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_id INT NOT NULL,
        role ENUM('user', 'assistant') NOT NULL,
        content TEXT,
        image_url TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (chat_id)
    )");
    // Ensure image_url column exists in messages
    $stmt = $pdo->query("SHOW COLUMNS FROM messages LIKE 'image_url'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN image_url TEXT NULL AFTER content");
        echo "Column 'image_url' added to 'messages' successfully.\n";
    }

    // Ensure model column exists in chats
    $stmt = $pdo->query("SHOW COLUMNS FROM chats LIKE 'model'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE chats ADD COLUMN model VARCHAR(100) AFTER title");
        echo "Column 'model' added to 'chats' successfully.\n";
    }

    echo "Database verification complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
