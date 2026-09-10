<?php
/**
 * Database Setup Script for Melodify Music App
 * Run this file once to create the database and tables
 */

require_once 'config.php';

try {
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS melodify_db";
    $conn->exec($sql);
    echo "Database 'melodify_db' created successfully.\n\n";

    // Select the database
    $conn->exec("USE melodify_db");

    // Create users table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'users' created.\n";

    // Create artists table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS artists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            genre VARCHAR(50) DEFAULT 'Unknown',
            bio TEXT,
            image VARCHAR(255) DEFAULT NULL,
            followers INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'artists' created.\n";

    // Create albums table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS albums (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            artist_id INT NOT NULL,
            release_year INT,
            cover_image VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'albums' created.\n";

    // Create tracks table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS tracks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            artist_id INT NOT NULL,
            album_id INT DEFAULT NULL,
            duration INT NOT NULL,
            genre VARCHAR(50) DEFAULT 'Unknown',
            file_path VARCHAR(255) DEFAULT NULL,
            cover_color VARCHAR(50) DEFAULT '#1DB954',
            plays INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
            FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'tracks' created.\n";

    // Create playlists table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS playlists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            user_id INT NOT NULL,
            description TEXT,
            is_public BOOLEAN DEFAULT FALSE,
            cover_image VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'playlists' created.\n";

    // Create playlist_tracks table (junction table)
    $conn->exec("
        CREATE TABLE IF NOT EXISTS playlist_tracks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            playlist_id INT NOT NULL,
            track_id INT NOT NULL,
            position INT DEFAULT 0,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
            FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
            UNIQUE KEY unique_playlist_track (playlist_id, track_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'playlist_tracks' created.\n";

    // Create liked_tracks table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS liked_tracks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            track_id INT NOT NULL,
            liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_track (user_id, track_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'liked_tracks' created.\n";

    // Create play_history table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS play_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            track_id INT NOT NULL,
            played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'play_history' created.\n";

    // Insert sample data
    echo "\n--- Inserting sample data ---\n";

    // Insert sample artists
    $artistsData = [
        ['The Weeknd', 'Pop/R&B', 'Canadian singer and songwriter known for his falsetto vocals and dark themes.'],
        ['Taylor Swift', 'Pop', 'American singer-songwriter known for narrative songwriting and genre diversity.'],
        ['Drake', 'Hip-Hop/Rap', 'Canadian rapper, singer, and actor, one of the most successful artists of all time.'],
        ['Billie Eilish', 'Pop', 'American singer-songwriter known for her ethereal vocals and experimental sound.'],
        ['Ed Sheeran', 'Pop', 'English singer-songwriter known for his acoustic guitar-driven pop-folk songs.'],
        ['Ariana Grande', 'Pop', 'American singer and actress known for her powerful vocals and R&B style.']
    ];

    foreach ($artistsData as $artist) {
        $stmt = $conn->prepare("INSERT IGNORE INTO artists (name, genre, bio) VALUES (?, ?, ?)");
        $stmt->execute($artist);
    }
    echo "Sample artists inserted.\n";

    // Insert sample albums
    $albumsData = [
        ['After Hours', 1, 2020],
        ['Harry\'s House', 3, 2022],
        ['Midnights', 2, 2022],
        ['Gemini Rights', 6, 2022],
        ['F*CK LOVE 3', 1, 2021],
        ['Endless Summer Vacation', 8, 2023]
    ];

    foreach ($albumsData as $album) {
        $stmt = $conn->prepare("INSERT IGNORE INTO albums (title, artist_id, release_year) VALUES (?, ?, ?)");
        $stmt->execute($album);
    }
    echo "Sample albums inserted.\n";

    // Insert sample tracks
    $tracksData = [
        [1, 'Blinding Lights', 1, 1, 203, 'Pop', 'from-red-500 to-purple-500'],
        [2, 'Stay', 1, 5, 142, 'Pop', 'from-blue-400 to-pink-400'],
        [3, 'As It Was', 3, 2, 167, 'Pop', 'from-yellow-400 to-red-400'],
        [4, 'Bad Habit', 4, 4, 221, 'R&B', 'from-green-400 to-blue-400'],
        [5, 'Anti-Hero', 2, 3, 201, 'Pop', 'from-purple-400 to-indigo-400'],
        [6, 'Heat Waves', 5, NULL, 238, 'Indie', 'from-orange-400 to-yellow-400'],
        [7, 'Vampire', 6, NULL, 211, 'Pop', 'from-pink-400 to-rose-400'],
        [8, 'Flowers', 8, 6, 200, 'Pop', 'from-teal-400 to-emerald-400']
    ];

    foreach ($tracksData as $track) {
        $stmt = $conn->prepare("INSERT IGNORE INTO tracks (title, artist_id, album_id, duration, genre, cover_color) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($track);
    }
    echo "Sample tracks inserted.\n";

    // Insert sample playlists
    $playlistsData = [
        ['Liked Songs', 1, 'Your liked songs', FALSE],
        ['Chill Vibes', 1, 'Relaxing music for your day', FALSE],
        ['Workout Mix', 1, 'High energy tracks', FALSE],
        ['Study Focus', 1, 'Concentration music', FALSE],
        ['Road Trip', 1, 'Songs for the open road', FALSE]
    ];

    foreach ($playlistsData as $playlist) {
        $stmt = $conn->prepare("INSERT IGNORE INTO playlists (name, user_id, description, is_public) VALUES (?, ?, ?, ?)");
        $stmt->execute($playlist);
    }
    echo "Sample playlists inserted.\n";

    // Add some tracks to playlists
    $playlistTracks = [
        [1, 1, 0], [1, 2, 1], [1, 3, 2], [1, 4, 3], [1, 5, 4],
        [2, 6, 0], [2, 7, 1], [2, 8, 2],
        [3, 1, 0], [3, 3, 1], [3, 5, 2],
        [4, 6, 0], [4, 4, 1], [4, 7, 2],
        [5, 2, 0], [5, 8, 1], [5, 1, 2]
    ];

    foreach ($playlistTracks as $pt) {
        $stmt = $conn->prepare("INSERT IGNORE INTO playlist_tracks (playlist_id, track_id, position) VALUES (?, ?, ?)");
        $stmt->execute($pt);
    }
    echo "Sample playlist tracks added.\n";

    echo "\n===========================================\n";
    echo "Database setup completed successfully!\n";
    echo "===========================================\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$db->closeConnection();