<?php
/**
 * API Routes for Melodify Music App
 * Handles all API requests for tracks, playlists, artists, etc.
 */

require_once '../config/config.php';

// Set headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $request_uri);

// Parse request body for POST/PUT
$input = [];
if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

// Simple routing
$routes = [
    // Tracks
    'GET' => [
        '/tracks' => 'getTracks',
        '/tracks/(\d+)' => 'getTrack',
        '/tracks/recent' => 'getRecentTracks',
        '/tracks/popular' => 'getPopularTracks',
        '/search' => 'searchTracks',
    ],
    // Artists
    'GET' => [
        '/artists' => 'getArtists',
        '/artists/(\d+)' => 'getArtist',
        '/artists/(\d+)/tracks' => 'getArtistTracks',
    ],
    // Albums
    'GET' => [
        '/albums' => 'getAlbums',
        '/albums/(\d+)' => 'getAlbum',
        '/albums/(\d+)/tracks' => 'getAlbumTracks',
    ],
    // Playlists
    'GET' => [
        '/playlists' => 'getPlaylists',
        '/playlists/(\d+)' => 'getPlaylist',
        '/playlists/(\d+)/tracks' => 'getPlaylistTracks',
    ],
    'POST' => [
        '/playlists' => 'createPlaylist',
        '/playlists/(\d+)/tracks' => 'addTrackToPlaylist',
    ],
    'PUT' => [
        '/playlists/(\d+)' => 'updatePlaylist',
    ],
    'DELETE' => [
        '/playlists/(\d+)' => 'deletePlaylist',
        '/playlists/(\d+)/tracks/(\d+)' => 'removeTrackFromPlaylist',
    ],
    // User actions
    'POST' => [
        '/like/(\d+)' => 'likeTrack',
        '/unlike/(\d+)' => 'unlikeTrack',
        '/liked-tracks' => 'getLikedTracks',
        '/play-history' => 'addToHistory',
    ],
    'GET' => [
        '/play-history' => 'getPlayHistory',
    ],
];

// Route matching and handler execution
function route($method, $path, $routes) {
    foreach ($routes[$method] ?? [] as $route => $handler) {
        $pattern = '#^' . $route . '$#';
        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches); // Remove full match
            return [$handler, $matches];
        }
    }
    return [null, []];
}

[$handler, $params] = route($method, $path, $routes);

if ($handler && method_exists('ApiHandler', $handler)) {
    $api = new ApiHandler($conn);
    $api->$handler(...$params, $input);
} else {
    ApiResponse::error('Endpoint not found', 404);
}

/**
 * API Handler Class
 */
class ApiHandler {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Tracks
    public function getTracks($input = []) {
        $limit = $input['limit'] ?? 50;
        $offset = $input['offset'] ?? 0;
        $genre = $input['genre'] ?? null;

        $sql = "SELECT t.*, a.name as artist_name, al.title as album_title
                FROM tracks t
                LEFT JOIN artists a ON t.artist_id = a.id
                LEFT JOIN albums al ON t.album_id = al.id";

        $params = [];
        if ($genre) {
            $sql .= " WHERE t.genre = ?";
            $params[] = $genre;
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $tracks = $stmt->fetchAll();

        ApiResponse::success($tracks);
    }

    public function getTrack($id, $input = []) {
        $stmt = $this->conn->prepare("
            SELECT t.*, a.name as artist_name, al.title as album_title
            FROM tracks t
            LEFT JOIN artists a ON t.artist_id = a.id
            LEFT JOIN albums al ON t.album_id = al.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $track = $stmt->fetch();

        if (!$track) {
            ApiResponse::error('Track not found', 404);
        }

        // Increment plays
        $this->conn->prepare("UPDATE tracks SET plays = plays + 1 WHERE id = ?")->execute([$id]);

        ApiResponse::success($track);
    }

    public function getRecentTracks($input = []) {
        $limit = $input['limit'] ?? 10;

        $stmt = $this->conn->prepare("
            SELECT t.*, a.name as artist_name
            FROM tracks t
            LEFT JOIN artists a ON t.artist_id = a.id
            ORDER BY t.created_at DESC LIMIT ?
        ");
        $stmt->execute([$limit]);
        $tracks = $stmt->fetchAll();

        ApiResponse::success($tracks);
    }

    public function getPopularTracks($input = []) {
        $limit = $input['limit'] ?? 10;

        $stmt = $this->conn->prepare("
            SELECT t.*, a.name as artist_name
            FROM tracks t
            LEFT JOIN artists a ON t.artist_id = a.id
            ORDER BY t.plays DESC LIMIT ?
        ");
        $stmt->execute([$limit]);
        $tracks = $stmt->fetchAll();

        ApiResponse::success($tracks);
    }

    public function searchTracks($query = '', $input = []) {
        if (empty($query)) {
            $query = $input['q'] ?? '';
        }

        if (empty($query)) {
            ApiResponse::error('Search query required', 400);
        }

        $searchTerm = "%{$query}%";
        $stmt = $this->conn->prepare("
            SELECT t.*, a.name as artist_name, al.title as album_title
            FROM tracks t
            LEFT JOIN artists a ON t.artist_id = a.id
            LEFT JOIN albums al ON t.album_id = al.id
            WHERE t.title LIKE ? OR a.name LIKE ? OR al.title LIKE ?
            ORDER BY t.plays DESC
            LIMIT 50
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $tracks = $stmt->fetchAll();

        ApiResponse::success([
            'query' => $query,
            'results' => $tracks,
            'count' => count($tracks)
        ]);
    }

    // Artists
    public function getArtists($input = []) {
        $limit = $input['limit'] ?? 50;
        $offset = $input['offset'] ?? 0;

        $stmt = $this->conn->prepare("SELECT * FROM artists ORDER BY followers DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $artists = $stmt->fetchAll();

        ApiResponse::success($artists);
    }

    public function getArtist($id, $input = []) {
        $stmt = $this->conn->prepare("SELECT * FROM artists WHERE id = ?");
        $stmt->execute([$id]);
        $artist = $stmt->fetch();

        if (!$artist) {
            ApiResponse::error('Artist not found', 404);
        }

        ApiResponse::success($artist);
    }

    public function getArtistTracks($id, $input = []) {
        $stmt = $this->conn->prepare("SELECT * FROM tracks WHERE artist_id = ? ORDER BY plays DESC");
        $stmt->execute([$id]);
        $tracks = $stmt->fetchAll();

        ApiResponse::success($tracks);
    }

    // Albums
    public function getAlbums($input = []) {
        $limit = $input['limit'] ?? 50;
        $offset = $input['offset'] ?? 0;

        $stmt = $this->conn->prepare("
            SELECT al.*, a.name as artist_name
            FROM albums al
            LEFT JOIN artists a ON al.artist_id = a.id
            ORDER BY al.release_year DESC LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $albums = $stmt->fetchAll();

        ApiResponse::success($albums);
    }

    public function getAlbum($id, $input = []) {
        $stmt = $this->conn->prepare("
            SELECT al.*, a.name as artist_name
            FROM albums al
            LEFT JOIN artists a ON al.artist_id = a.id
            WHERE al.id = ?
        ");
        $stmt->execute([$id]);
        $album = $stmt->fetch();

        if (!$album) {
            ApiResponse::error('Album not found', 404);
        }

        ApiResponse::success($album);
    }

    public function getAlbumTracks($id, $input = []) {
        $stmt = $this->conn->prepare("SELECT * FROM tracks WHERE album_id = ? ORDER BY id");
        $stmt->execute([$id]);
        $tracks = $stmt->fetchAll();

        ApiResponse::success($tracks);
    }

    // Playlists
    public function getPlaylists($input = []) {
        $userId = $input['user_id'] ?? 1; // Default to user 1 for now

        $stmt = $this->conn->prepare("
            SELECT p.*, COUNT(pt.id) as track_count
            FROM playlists p
            LEFT JOIN playlist_tracks pt ON p.id = pt.playlist_id
            WHERE p.user_id = ?
            GROUP BY p.id
            ORDER BY p.updated_at DESC
        ");
        $stmt->execute([$userId]);
        $playlists = $stmt->fetchAll();

        ApiResponse::success($playlists);
    }

    public function getPlaylist($id, $input = []) {
        $stmt = $this->conn->prepare("SELECT * FROM playlists WHERE id = ?");
        $stmt->execute([$id]);
        $playlist = $stmt->fetch();

        if (!$playlist) {
            ApiResponse::error('Playlist not found', 404);
        }

        ApiResponse::success($playlist);
    }

    public function getPlaylistTracks($id, $input = []) {
        $stmt = $this->conn->prepare("
            SELECT t.*, a.name as artist_name, pt.position, pt.added_at
            FROM tracks t
            LEFT JOIN artists a ON t.artist_id = a.id
            LEFT JOIN playlist_tracks pt ON t.id = pt.track_id
            WHERE pt.playlist_id = ?
            ORDER BY pt.position
        ");
        $stmt->execute([$id]);
        $tracks = $stmt->fetchAll();

        ApiResponse::success($tracks);
    }

    public function createPlaylist($input = []) {
        $name = $input['name'] ?? '';
        $description = $input['description'] ?? '';
        $userId = $input['user_id'] ?? 1;

        if (empty($name)) {
            ApiResponse::error('Playlist name is required', 400);
        }

        $stmt = $this->conn->prepare("
            INSERT INTO playlists (name, user_id, description)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$name, $userId, $description]);
        $playlistId = $this->conn->lastInsertId();

        ApiResponse::success(['id' => $playlistId, 'name' => $name], 'Playlist created');
    }

    public function updatePlaylist($id, $input = []) {
        $name = $input['name'] ?? null;
        $description = $input['description'] ?? null;

        $updates = [];
        $params = [];

        if ($name !== null) {
            $updates[] = "name = ?";
            $params[] = $name;
        }
        if ($description !== null) {
            $updates[] = "description = ?";
            $params[] = $description;
        }

        if (empty($updates)) {
            ApiResponse::error('No updates provided', 400);
        }

        $params[] = $id;
        $sql = "UPDATE playlists SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        ApiResponse::success(null, 'Playlist updated');
    }

    public function deletePlaylist($id, $input = []) {
        $stmt = $this->conn->prepare("DELETE FROM playlists WHERE id = ?");
        $stmt->execute([$id]);

        ApiResponse::success(null, 'Playlist deleted');
    }

    public function addTrackToPlaylist($playlistId, $input = []) {
        $trackId = $input['track_id'] ?? 0;
        $position = $input['position'] ?? 0;

        if (empty($trackId)) {
            ApiResponse::error('Track ID is required', 400);
        }

        // Get next position if not provided
        if ($position === 0) {
            $stmt = $this->conn->prepare("SELECT MAX(position) + 1 as next_pos FROM playlist_tracks WHERE playlist_id = ?");
            $stmt->execute([$playlistId]);
            $result = $stmt->fetch();
            $position = $result['next_pos'] ?? 0;
        }

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO playlist_tracks (playlist_id, track_id, position)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$playlistId, $trackId, $position]);

            ApiResponse::success(null, 'Track added to playlist');
        } catch (PDOException $e) {
            ApiResponse::error('Track already in playlist', 400);
        }
    }

    public function removeTrackFromPlaylist($playlistId, $trackId, $input = []) {
        $stmt = $this->conn->prepare("
            DELETE FROM playlist_tracks
            WHERE playlist_id = ? AND track_id = ?
        ");
        $stmt->execute([$playlistId, $trackId]);

        ApiResponse::success(null, 'Track removed from playlist');
    }

    // User actions
    public function likeTrack($trackId, $input = []) {
        $userId = $input['user_id'] ?? 1;

        try {
            $stmt = $this->conn->prepare("INSERT INTO liked_tracks (user_id, track_id) VALUES (?, ?)");
            $stmt->execute([$userId, $trackId]);

            ApiResponse::success(null, 'Track liked');
        } catch (PDOException $e) {
            ApiResponse::error('Track already liked', 400);
        }
    }

    public function unlikeTrack($trackId, $input = []) {
        $userId = $input['user_id'] ?? 1;

        $stmt = $this->conn->prepare("DELETE FROM liked_tracks WHERE user_id = ? AND track_id = ?");
        $stmt->execute([$userId, $trackId]);

        ApiResponse::success(null, 'Track unliked');
    }

    public function getLikedTracks($input = []) {
        $userId = $input['user_id'] ?? 1;

        $stmt = $this->conn->prepare("
            SELECT t.*, a.name as artist_name
            FROM tracks t
            LEFT JOIN artists a ON t.artist_id = a.id
            INNER JOIN liked_tracks lt ON t.id = lt.track_id
            WHERE lt.user_id = ?
            ORDER BY lt.liked_at DESC
        ");
        $stmt->execute([$userId]);
        $tracks = $stmt->fetchAll();

        ApiResponse::success($tracks);
    }

    public function addToHistory($input = []) {
        $trackId = $input['track_id'] ?? 0;
        $userId = $input['user_id'] ?? 1;

        if (empty($trackId)) {
            ApiResponse::error('Track ID is required', 400);
        }

        $stmt = $this->conn->prepare("INSERT INTO play_history (user_id, track_id) VALUES (?, ?)");
        $stmt->execute([$userId, $trackId]);

        ApiResponse::success(null, 'Added to history');
    }

    public function getPlayHistory($input = []) {
        $userId = $input['user_id'] ?? 1;
        $limit = $input['limit'] ?? 50;

        $stmt = $this->conn->prepare("
            SELECT t.*, a.name as artist_name, ph.played_at
            FROM tracks t
            LEFT JOIN artists a ON t.artist_id = a.id
            INNER JOIN play_history ph ON t.id = ph.track_id
            WHERE ph.user_id = ?
            ORDER BY ph.played_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        $history = $stmt->fetchAll();

        ApiResponse::success($history);
    }
}