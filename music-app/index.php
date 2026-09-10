<?php
/**
 * Melodify - Music Player Web App
 * Main entry point
 */

// Include configuration
require_once 'config/config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melodify - Music Player</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional inline styles for smoother loading */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--bg-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loading-screen.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loading-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        .loading-logo svg {
            width: 40px;
            height: 40px;
            stroke: white;
        }

        .loading-text {
            color: var(--text-secondary);
            font-size: 14px;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Glitch effect for title on error */
        .glitch {
            position: relative;
        }

        .glitch::before,
        .glitch::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .glitch::before {
            left: 2px;
            text-shadow: -1px 0 #ff00c1;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim-1 5s infinite linear alternate-reverse;
        }

        .glitch::after {
            left: -2px;
            text-shadow: -1px 0 #00fff9;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim-2 5s infinite linear alternate-reverse;
        }

        @keyframes glitch-anim-1 {
            0% { clip: rect(20px, 9999px, 10px, 0); }
            20% { clip: rect(50px, 9999px, 60px, 0); }
            40% { clip: rect(80px, 9999px, 30px, 0); }
            60% { clip: rect(10px, 9999px, 90px, 0); }
            80% { clip: rect(40px, 9999px, 20px, 0); }
            100% { clip: rect(70px, 9999px, 50px, 0); }
        }

        @keyframes glitch-anim-2 {
            0% { clip: rect(60px, 9999px, 80px, 0); }
            20% { clip: rect(10px, 9999px, 40px, 0); }
            40% { clip: rect(30px, 9999px, 70px, 0); }
            60% { clip: rect(90px, 9999px, 10px, 0); }
            80% { clip: rect(20px, 9999px, 50px, 0); }
            100% { clip: rect(50px, 9999px, 30px, 0); }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loading-screen">
        <div class="loading-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 18V5l12-2v13"/>
                <circle cx="6" cy="18" r="3"/>
                <circle cx="18" cy="16" r="3"/>
            </svg>
        </div>
        <div class="loading-text">Loading Melodify...</div>
    </div>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18V5l12-2v13"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                </div>
                <span>Melodify</span>
            </div>

            <nav class="nav-menu">
                <div class="nav-section">
                    <a href="#" class="nav-item active" data-page="home">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9,22 9,12 15,12 15,22"/>
                        </svg>
                        <span>Home</span>
                    </a>
                    <a href="#" class="nav-item" data-page="search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <span>Search</span>
                    </a>
                    <a href="#" class="nav-item" data-page="library">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                        <span>Your Library</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="section-title">PLAYLISTS</div>
                    <div class="playlist-list" id="playlist-list">
                        <!-- Playlists loaded via API or JS -->
                    </div>
                    <button class="create-playlist-btn" id="create-playlist-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        <span>Create Playlist</span>
                    </button>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-bar">
                <div class="nav-buttons">
                    <button class="nav-btn" id="back-btn" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"/>
                        </svg>
                    </button>
                    <button class="nav-btn" id="forward-btn" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,18 15,12 9,6"/>
                        </svg>
                    </button>
                </div>
                <div class="search-bar" id="search-bar" style="display: none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" placeholder="Search songs, artists, albums..." id="search-input">
                </div>
                <div class="user-actions">
                    <button class="theme-toggle" id="theme-toggle">
                        <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    <div class="user-profile">
                        <div class="user-avatar">U</div>
                        <span>User</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content" id="page-content">
                <!-- Home Page -->
                <section class="page active" id="home-page">
                    <div class="page-header">
                        <h1>Good evening</h1>
                    </div>

                    <div class="quick-picks" id="quick-picks">
                        <!-- Quick picks will be rendered here -->
                    </div>

                    <section class="content-section">
                        <div class="section-header">
                            <h2>Recently Played</h2>
                            <a href="#" class="see-all">See all</a>
                        </div>
                        <div class="horizontal-scroll">
                            <div class="card-grid" id="recently-played">
                                <!-- Cards will be rendered here -->
                            </div>
                        </div>
                    </section>

                    <section class="content-section">
                        <div class="section-header">
                            <h2>Made For You</h2>
                            <a href="#" class="see-all">See all</a>
                        </div>
                        <div class="horizontal-scroll">
                            <div class="card-grid" id="made-for-you">
                                <!-- Cards will be rendered here -->
                            </div>
                        </div>
                    </section>

                    <section class="content-section">
                        <div class="section-header">
                            <h2>Popular Artists</h2>
                            <a href="#" class="see-all">See all</a>
                        </div>
                        <div class="horizontal-scroll">
                            <div class="card-grid" id="popular-artists">
                                <!-- Artist cards will be rendered here -->
                            </div>
                        </div>
                    </section>
                </section>

                <!-- Search Page -->
                <section class="page" id="search-page">
                    <div class="page-header">
                        <h1>Search</h1>
                    </div>
                    <div class="search-page-content">
                        <div class="search-input-large">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            <input type="text" placeholder="What do you want to listen to?" id="search-input-large">
                        </div>
                        <div class="search-results" id="search-results" style="display: none;">
                            <!-- Search results will be rendered here -->
                        </div>
                        <div class="browse-categories" id="browse-categories">
                            <h2>Browse All</h2>
                            <div class="category-grid">
                                <!-- Categories will be rendered here -->
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Library Page -->
                <section class="page" id="library-page">
                    <div class="page-header">
                        <h1>Your Library</h1>
                    </div>
                    <div class="library-tabs">
                        <button class="tab-btn active" data-tab="playlists">Playlists</button>
                        <button class="tab-btn" data-tab="albums">Albums</button>
                        <button class="tab-btn" data-tab="artists">Artists</button>
                    </div>
                    <div class="library-content">
                        <div class="tab-content active" id="playlists-tab">
                            <div class="song-list" id="library-playlists">
                                <!-- Library playlists will be rendered here -->
                            </div>
                        </div>
                        <div class="tab-content" id="albums-tab">
                            <div class="card-grid" id="library-albums">
                                <!-- Library albums will be rendered here -->
                            </div>
                        </div>
                        <div class="tab-content" id="artists-tab">
                            <div class="card-grid" id="library-artists">
                                <!-- Library artists will be rendered here -->
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Playlist Detail Page -->
                <section class="page" id="playlist-detail-page">
                    <div class="playlist-header" id="playlist-header">
                        <!-- Playlist header will be rendered here -->
                    </div>
                    <div class="playlist-tracks">
                        <div class="track-list-header">
                            <div class="track-num">#</div>
                            <div class="track-title">Title</div>
                            <div class="track-album">Album</div>
                            <div class="track-duration">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12,6 12,12 16,14"/>
                                </svg>
                            </div>
                        </div>
                        <div class="track-list" id="playlist-tracks">
                            <!-- Tracks will be rendered here -->
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Player Bar -->
        <footer class="player-bar">
            <div class="now-playing">
                <div class="track-cover" id="player-cover">
                    <div class="track-cover-placeholder" id="player-cover-placeholder"></div>
                </div>
                <div class="track-info">
                    <div class="track-name" id="player-track-name">No track playing</div>
                    <div class="track-artist" id="player-artist-name">-</div>
                </div>
                <button class="like-btn" id="like-btn">
                    <svg class="heart-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <svg class="heart-filled" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </button>
            </div>

            <div class="player-controls">
                <div class="control-buttons">
                    <button class="control-btn" id="shuffle-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="16,3 21,3 21,8"/>
                            <line x1="4" y1="20" x2="21" y2="3"/>
                            <polyline points="21,16 21,21 16,21"/>
                            <line x1="15" y1="15" x2="21" y2="21"/>
                            <line x1="4" y1="4" x2="9" y2="9"/>
                        </svg>
                    </button>
                    <button class="control-btn" id="prev-btn">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 20L9 12l10-8v16zM7 19V5H5v14h2z"/>
                        </svg>
                    </button>
                    <button class="control-btn play-btn" id="play-btn">
                        <svg class="play-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg class="pause-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </button>
                    <button class="control-btn" id="next-btn">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M5 4l10 8-10 8V4zm12 1v14h2V5h-2z"/>
                        </svg>
                    </button>
                    <button class="control-btn" id="repeat-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="17,1 21,5 17,9"/>
                            <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                            <polyline points="7,23 3,19 7,15"/>
                            <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                        </svg>
                    </button>
                </div>
                <div class="progress-container">
                    <span class="time current-time" id="current-time">0:00</span>
                    <div class="progress-bar" id="progress-bar">
                        <div class="progress" id="progress"></div>
                        <div class="progress-handle" id="progress-handle"></div>
                    </div>
                    <span class="time total-time" id="total-time">0:00</span>
                </div>
            </div>

            <div class="volume-controls">
                <button class="control-btn" id="queue-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="8" y1="6" x2="21" y2="6"/>
                        <line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/>
                        <line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/>
                        <line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                </button>
                <button class="control-btn" id="devices-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </button>
                <div class="volume-wrapper">
                    <button class="control-btn volume-btn" id="volume-btn">
                        <svg class="volume-high" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>
                            <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                        </svg>
                        <svg class="volume-low" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/>
                            <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                        </svg>
                        <svg class="volume-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/>
                            <line x1="23" y1="9" x2="17" y2="15"/>
                            <line x1="17" y1="9" x2="23" y2="15"/>
                        </svg>
                    </button>
                    <div class="volume-slider-container">
                        <div class="volume-bar" id="volume-bar">
                            <div class="volume-level" id="volume-level"></div>
                            <div class="volume-handle" id="volume-handle"></div>
                        </div>
                    </div>
                </div>
                <button class="control-btn" id="fullscreen-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15,3 21,3 21,9"/>
                        <polyline points="9,21 3,21 3,15"/>
                        <line x1="21" y1="3" x2="14" y2="10"/>
                        <line x1="3" y1="21" x2="10" y2="14"/>
                    </svg>
                </button>
            </div>
        </footer>
    </div>

    <!-- Modals -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal" id="create-playlist-modal">
            <div class="modal-header">
                <h2>Create Playlist</h2>
                <button class="modal-close" id="modal-close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" placeholder="Playlist name" id="playlist-name-input" class="input-field">
                <textarea placeholder="Add an optional description" id="playlist-desc-input" class="input-field textarea"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-playlist-btn">Cancel</button>
                <button class="btn btn-primary" id="save-playlist-btn">Create</button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container" id="toast-container"></div>

    <script src="app.js"></script>
    <script>
        // Hide loading screen when app is ready
        document.addEventListener('DOMContentLoaded', function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.classList.add('hidden');
                    setTimeout(() => {
                        loadingScreen.style.display = 'none';
                    }, 500);
                }, 800);
            }
        });
    </script>
</body>
</html>