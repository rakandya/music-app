class MusicApp {
    constructor() {
        this.currentTrack = null;
        this.currentPlaylist = null;
        this.isPlaying = false;
        this.currentTime = 0;
        this.duration = 0;
        this.volume = 0.8;
        this.isMuted = false;
        this.isShuffle = false;
        this.isRepeat = false;
        this.likedTracks = new Set();
        this.playHistory = [];
        this.searchResults = [];
        this.playlists = [];
        this.currentTheme = localStorage.getItem('theme') || 'dark';

        this.init();
    }

    async init() {
        this.applyTheme();
        await this.loadDataFromAPI();
        this.setupEventListeners();
        this.renderContent();
        this.setupAudio();
    }

    async loadDataFromAPI() {
        try {
            // Fetch tracks
            const tracksRes = await fetch('api/index.php?path=/tracks&limit=50');
            const tracksData = await tracksRes.json();
            if (tracksData.status === 'success') {
                this.tracks = tracksData.data.map(track => ({
                    id: track.id,
                    title: track.title,
                    artist: track.artist_name || track.artist_id,
                    album: track.album_title || '',
                    duration: track.duration,
                    genre: track.genre,
                    cover: track.cover_color || 'bg-gradient-to-br from-gray-400 to-gray-700',
                    file_path: track.file_path,
                    liked: false
                }));
                console.log(`Loaded ${this.tracks.length} tracks from API`);
            } else {
                console.warn('Failed to load tracks from API, using mock data');
                this.loadMockData();
                return;
            }

            // Fetch artists
            const artistsRes = await fetch('api/index.php?path=/artists&limit=50');
            const artistsData = await artistsRes.json();
            if (artistsData.status === 'success') {
                this.artists = artistsData.data.map(artist => ({
                    id: artist.id,
                    name: artist.name,
                    genre: artist.genre,
                    followers: `${Math.floor(artist.followers / 1000000)}M`
                }));
                console.log(`Loaded ${this.artists.length} artists from API`);
            }

            // Fetch playlists (for default user ID 1)
            const playlistsRes = await fetch('api/index.php?path=/playlists&user_id=1');
            const playlistsData = await playlistsRes.json();
            if (playlistsData.status === 'success') {
                this.playlists = playlistsData.data.map(playlist => ({
                    id: playlist.id,
                    name: playlist.name,
                    description: playlist.description,
                    trackCount: playlist.track_count || 0,
                    color: this.getRandomGradientColor(playlist.id)
                }));
                console.log(`Loaded ${this.playlists.length} playlists from API`);
            }

            // Fetch liked tracks for user 1
            const likedRes = await fetch('api/index.php?path=/liked-tracks&user_id=1');
            const likedData = await likedRes.json();
            if (likedData.status === 'success') {
                likedData.data.forEach(track => {
                    this.likedTracks.add(track.id);
                    const foundTrack = this.tracks.find(t => t.id === track.id);
                    if (foundTrack) foundTrack.liked = true;
                });
                console.log(`Loaded ${this.likedTracks.size} liked tracks from API`);
            }
        } catch (error) {
            console.error('Error loading data from API:', error);
            // Fallback to mock data if API fails
            this.loadMockData();
        }
    }

    getRandomGradientColor(seed) {
        const colors = [
            'from-blue-400 to-cyan-400',
            'from-green-500 to-emerald-500',
            'from-red-500 to-orange-500',
            'from-purple-400 to-pink-400',
            'from-yellow-400 to-amber-500',
            'from-indigo-400 to-purple-400',
            'from-teal-400 to-emerald-400',
            'from-pink-400 to-rose-400'
        ];
        return colors[seed % colors.length];
    }

    // Fallback mock data if API is not available
    loadMockData() {
        console.log('Loading mock data as fallback');
        this.tracks = [
            {
                id: 1,
                title: "Blinding Lights",
                artist: "The Weeknd",
                album: "After Hours",
                duration: 203,
                genre: "Pop",
                cover: "bg-gradient-to-br from-red-500 to-purple-500",
                file_path: "assets/audio/track_1.wav",
                liked: false
            },
            {
                id: 2,
                title: "Stay",
                artist: "The Kid LAROI, Justin Bieber",
                album: "F*CK LOVE 3",
                duration: 142,
                genre: "Pop",
                cover: "bg-gradient-to-br from-blue-400 to-pink-400",
                file_path: "assets/audio/track_2.wav",
                liked: true
            },
            {
                id: 3,
                title: "As It Was",
                artist: "Harry Styles",
                album: "Harry's House",
                duration: 167,
                genre: "Pop",
                cover: "bg-gradient-to-br from-yellow-400 to-red-400",
                file_path: "assets/audio/track_3.wav",
                liked: false
            },
            {
                id: 4,
                title: "Bad Habit",
                artist: "Steve Lacy",
                album: "Gemini Rights",
                duration: 221,
                genre: "R&B",
                cover: "bg-gradient-to-br from-green-400 to-blue-400",
                file_path: "assets/audio/track_4.wav",
                liked: true
            },
            {
                id: 5,
                title: "Anti-Hero",
                artist: "Taylor Swift",
                album: "Midnights",
                duration: 201,
                genre: "Pop",
                cover: "bg-gradient-to-br from-purple-400 to-indigo-400",
                file_path: "assets/audio/track_5.wav",
                liked: false
            },
            {
                id: 6,
                title: "Heat Waves",
                artist: "Glass Animals",
                album: "Dreamland",
                duration: 238,
                genre: "Indie",
                cover: "bg-gradient-to-br from-orange-400 to-yellow-400",
                file_path: "assets/audio/track_6.wav",
                liked: true
            },
            {
                id: 7,
                title: "Vampire",
                artist: "Olivia Rodrigo",
                album: "GUTS",
                duration: 211,
                genre: "Pop",
                cover: "bg-gradient-to-br from-pink-400 to-rose-400",
                file_path: "assets/audio/track_7.wav",
                liked: false
            },
            {
                id: 8,
                title: "Flowers",
                artist: "Miley Cyrus",
                album: "Endless Summer Vacation",
                duration: 200,
                genre: "Pop",
                cover: "bg-gradient-to-br from-teal-400 to-emerald-400",
                file_path: "assets/audio/track_8.wav",
                liked: true
            }
        ];

        this.artists = [
            { id: 1, name: "The Weeknd", genre: "Pop/R&B", followers: "58M" },
            { id: 2, name: "Taylor Swift", genre: "Pop", followers: "82M" },
            { id: 3, name: "Drake", genre: "Hip-Hop/Rap", followers: "66M" },
            { id: 4, name: "Billie Eilish", genre: "Pop", followers: "45M" },
            { id: 5, name: "Ed Sheeran", genre: "Pop", followers: "52M" },
            { id: 6, name: "Ariana Grande", genre: "Pop", followers: "49M" }
        ];

        this.playlists = [
            { id: 1, name: "Liked Songs", description: "Your liked songs", trackCount: 8, color: "bg-gradient-to-r from-green-500 to-emerald-500" },
            { id: 2, name: "Chill Vibes", description: "Relaxing music for your day", trackCount: 12, color: "bg-gradient-to-r from-blue-400 to-cyan-400" },
            { id: 3, name: "Workout Mix", description: "High energy tracks", trackCount: 15, color: "bg-gradient-to-r from-red-500 to-orange-500" },
            { id: 4, name: "Study Focus", description: "Concentration music", trackCount: 10, color: "bg-gradient-to-r from-purple-400 to-pink-400" },
            { id: 5, name: "Road Trip", description: "Songs for the open road", trackCount: 20, color: "bg-gradient-to-r from-yellow-400 to-amber-500" }
        ];

        this.tracks.filter(track => track.liked).forEach(track => {
            this.likedTracks.add(track.id);
        });
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const page = item.getAttribute('data-page');
                this.navigateToPage(page);

                // Update active state
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Search
        const searchInput = document.getElementById('search-input');
        const searchInputLarge = document.getElementById('search-input-large');

        const handleSearch = (query) => {
            if (query.trim().length > 0) {
                this.searchTracks(query);
                document.getElementById('browse-categories').style.display = 'none';
                document.getElementById('search-results').style.display = 'block';
            } else {
                document.getElementById('browse-categories').style.display = 'block';
                document.getElementById('search-results').style.display = 'none';
            }
        };

        searchInput?.addEventListener('input', (e) => handleSearch(e.target.value));
        searchInputLarge?.addEventListener('input', (e) => handleSearch(e.target.value));

        // Playlist creation
        document.getElementById('create-playlist-btn')?.addEventListener('click', () => {
            this.showCreatePlaylistModal();
        });

        document.getElementById('modal-close')?.addEventListener('click', () => {
            this.hideModal();
        });

        document.getElementById('cancel-playlist-btn')?.addEventListener('click', () => {
            this.hideModal();
        });

        document.getElementById('save-playlist-btn')?.addEventListener('click', () => {
            this.createPlaylist();
        });

        document.getElementById('modal-overlay')?.addEventListener('click', (e) => {
            if (e.target.id === 'modal-overlay') {
                this.hideModal();
            }
        });

        // Theme toggle
        document.getElementById('theme-toggle')?.addEventListener('click', () => {
            this.toggleTheme();
        });

        // Player controls
        document.getElementById('play-btn')?.addEventListener('click', () => {
            this.togglePlay();
        });

        document.getElementById('prev-btn')?.addEventListener('click', () => {
            this.playPrevious();
        });

        document.getElementById('next-btn')?.addEventListener('click', () => {
            this.playNext();
        });

        document.getElementById('shuffle-btn')?.addEventListener('click', () => {
            this.toggleShuffle();
        });

        document.getElementById('repeat-btn')?.addEventListener('click', () => {
            this.toggleRepeat();
        });

        document.getElementById('like-btn')?.addEventListener('click', () => {
            this.toggleLike();
        });

        // Volume controls
        document.getElementById('volume-btn')?.addEventListener('click', () => {
            this.toggleMute();
        });

        // Progress bar
        const progressBar = document.getElementById('progress-bar');
        progressBar?.addEventListener('click', (e) => {
            const rect = progressBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            this.seekTo(percent);
        });

        // Volume bar
        const volumeBar = document.getElementById('volume-bar');
        volumeBar?.addEventListener('click', (e) => {
            const rect = volumeBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            this.setVolume(percent);
        });

        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.getAttribute('data-tab');
                this.switchTab(tab);

                document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    }

    setupAudio() {
        // Create real HTML5 audio element
        this.audio = new Audio();
        this.audio.volume = this.volume;
        this.audio.preload = 'metadata';

        // Set up audio event listeners
        this.audio.addEventListener('loadedmetadata', () => {
            this.duration = this.audio.duration;
            this.updateProgress();
        });

        this.audio.addEventListener('timeupdate', () => {
            this.currentTime = this.audio.currentTime;
            this.updateProgress();
        });

        this.audio.addEventListener('ended', () => {
            if (this.isRepeat) {
                this.audio.currentTime = 0;
                this.audio.play().catch(() => {});
            } else {
                this.playNext();
            }
        });

        this.audio.addEventListener('play', () => {
            this.isPlaying = true;
            const playBtn = document.getElementById('play-btn');
            if (playBtn) playBtn.classList.add('playing');
        });

        this.audio.addEventListener('pause', () => {
            this.isPlaying = false;
            const playBtn = document.getElementById('play-btn');
            if (playBtn) playBtn.classList.remove('playing');
        });

        this.audio.addEventListener('volumechange', () => {
            this.volume = this.audio.volume;
            this.isMuted = this.audio.muted;
            this.updateVolumeUI();
        });

        // Start progress simulation for visual updates only
        this.startProgressSimulation();
    }

    startProgressSimulation() {
        setInterval(() => {
            if (this.isPlaying && this.currentTrack) {
                this.currentTime += 1;
                if (this.currentTime >= this.duration) {
                    if (this.isRepeat) {
                        this.currentTime = 0;
                    } else {
                        this.playNext();
                    }
                }
                this.updateProgress();
            }
        }, 1000);
    }

    renderContent() {
        this.renderQuickPicks();
        this.renderRecentlyPlayed();
        this.renderMadeForYou();
        this.renderPopularArtists();
        this.renderPlaylists();
        this.renderCategories();
        this.updatePlayerInfo();
    }

    renderQuickPicks() {
        const container = document.getElementById('quick-picks');
        if (!container) return;

        const picks = [
            { title: "Chill Vibes", color: "from-blue-400 to-cyan-400" },
            { title: "Party Mix", color: "from-purple-400 to-pink-400" },
            { title: "Workout", color: "from-red-500 to-orange-500" },
            { title: "Study", color: "from-green-400 to-teal-400" },
            { title: "Road Trip", color: "from-yellow-400 to-amber-500" },
            { title: "Sleep", color: "from-indigo-400 to-purple-400" }
        ];

        container.innerHTML = picks.map(pick => `
            <div class="quick-pick-card" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
                <p>${pick.title}</p>
            </div>
        `).join('');
    }

    renderRecentlyPlayed() {
        const container = document.getElementById('recently-played');
        if (!container) return;

        const recentTracks = this.tracks.slice(0, 6);
        container.innerHTML = recentTracks.map(track => `
            <div class="card" data-track-id="${track.id}">
                <div class="card-image ${track.cover}">
                    <div class="play-overlay">
                        <svg viewBox="0 0 24 24" fill="white">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="card-info">
                    <div class="card-title">${track.title}</div>
                    <div class="card-subtitle">${track.artist}</div>
                </div>
            </div>
        `).join('');

        // Add click handlers
        container.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', () => {
                const trackId = parseInt(card.getAttribute('data-track-id'));
                const track = this.tracks.find(t => t.id === trackId);
                if (track) {
                    this.playTrack(track);
                }
            });
        });
    }

    renderMadeForYou() {
        const container = document.getElementById('made-for-you');
        if (!container) return;

        const madeForYou = this.playlists.slice(0, 6);
        container.innerHTML = madeForYou.map(playlist => `
            <div class="card" data-playlist-id="${playlist.id}">
                <div class="card-image ${playlist.color}">
                    <div class="play-overlay">
                        <svg viewBox="0 0 24 24" fill="white">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="card-info">
                    <div class="card-title">${playlist.name}</div>
                    <div class="card-subtitle">Playlist · ${playlist.trackCount} songs</div>
                </div>
            </div>
        `).join('');

        // Add click handlers
        container.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', () => {
                const playlistId = parseInt(card.getAttribute('data-playlist-id'));
                const playlist = this.playlists.find(p => p.id === playlistId);
                if (playlist) {
                    this.showPlaylist(playlist);
                }
            });
        });
    }

    renderPopularArtists() {
        const container = document.getElementById('popular-artists');
        if (!container) return;

        container.innerHTML = this.artists.map(artist => `
            <div class="card">
                <div class="card-image bg-gradient-to-br from-gray-700 to-gray-900">
                    <div class="play-overlay">
                        <svg viewBox="0 0 24 24" fill="white">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="card-info">
                    <div class="card-title">${artist.name}</div>
                    <div class="card-subtitle">Artist · ${artist.followers} followers</div>
                </div>
            </div>
        `).join('');
    }

    renderPlaylists() {
        const container = document.getElementById('playlist-list');
        if (!container) return;

        container.innerHTML = this.playlists.map(playlist => `
            <div class="playlist-item" data-playlist-id="${playlist.id}">
                ${playlist.name}
            </div>
        `).join('');

        // Add click handlers
        container.querySelectorAll('.playlist-item').forEach(item => {
            item.addEventListener('click', () => {
                const playlistId = parseInt(item.getAttribute('data-playlist-id'));
                const playlist = this.playlists.find(p => p.id === playlistId);
                if (playlist) {
                    this.showPlaylist(playlist);
                }
            });
        });
    }

    renderCategories() {
        const container = document.querySelector('.category-grid');
        if (!container) return;

        const categories = [
            "Pop", "Hip-Hop", "Rock", "Jazz", "Classical", "Electronic",
            "R&B", "Country", "Latin", "K-Pop", "Indie", "Metal"
        ];

        container.innerHTML = categories.map(category => `
            <div class="category-card">
                <p>${category}</p>
            </div>
        `).join('');
    }

    navigateToPage(page) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));

        // Show target page
        document.getElementById(`${page}-page`)?.classList.add('active');

        // Handle search bar visibility
        const searchBar = document.getElementById('search-bar');
        if (page === 'search') {
            searchBar.style.display = 'flex';
            document.getElementById('search-input-large').focus();
        } else {
            searchBar.style.display = 'none';
        }
    }

    searchTracks(query) {
        query = query.toLowerCase();
        this.searchResults = this.tracks.filter(track =>
            track.title.toLowerCase().includes(query) ||
            track.artist.toLowerCase().includes(query) ||
            track.album.toLowerCase().includes(query)
        );

        const container = document.getElementById('search-results');
        if (!container) return;

        if (this.searchResults.length === 0) {
            container.innerHTML = '<div class="no-results">No results found</div>';
        } else {
            container.innerHTML = `
                <div class="search-results-section">
                    <h3>Tracks</h3>
                    <div class="song-list">
                        ${this.searchResults.map(track => `
                            <div class="song-item" data-track-id="${track.id}">
                                <div class="song-item-image ${track.cover}"></div>
                                <div class="song-item-info">
                                    <div class="song-item-title">${track.title}</div>
                                    <div class="song-item-artist">${track.artist}</div>
                                </div>
                                <div class="song-item-album">${track.album}</div>
                                <div class="song-item-duration">${this.formatTime(track.duration)}</div>
                                <div class="song-item-actions">
                                    <button class="like-btn" data-track-id="${track.id}">
                                        <svg viewBox="0 0 24 24" fill="${track.liked ? 'var(--primary)' : 'none'}" stroke="currentColor" stroke-width="2">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;

            // Add click handlers
            container.querySelectorAll('.song-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    if (!e.target.closest('.song-item-actions')) {
                        const trackId = parseInt(item.getAttribute('data-track-id'));
                        const track = this.tracks.find(t => t.id === trackId);
                        if (track) {
                            this.playTrack(track);
                        }
                    }
                });
            });

            // Add like button handlers
            container.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const trackId = parseInt(btn.getAttribute('data-track-id'));
                    this.toggleTrackLike(trackId);
                });
            });
        }
    }

    showCreatePlaylistModal() {
        document.getElementById('modal-overlay').classList.add('active');
        document.getElementById('playlist-name-input').value = '';
        document.getElementById('playlist-desc-input').value = '';
        document.getElementById('playlist-name-input').focus();
    }

    hideModal() {
        document.getElementById('modal-overlay').classList.remove('active');
    }

    async createPlaylist() {
        const name = document.getElementById('playlist-name-input').value.trim();
        const description = document.getElementById('playlist-desc-input').value.trim();

        if (!name) {
            this.showToast('Please enter a playlist name', 'error');
            return;
        }

        try {
            const response = await fetch('api/index.php?path=/playlists', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: name,
                    description: description,
                    user_id: 1
                })
            });

            const result = await response.json();
            if (result.status === 'success') {
                const newPlaylist = {
                    id: result.data.id,
                    name: name,
                    description: description,
                    trackCount: 0,
                    color: this.getRandomGradientColor(result.data.id)
                };

                this.playlists.push(newPlaylist);
                this.renderPlaylists();
                this.hideModal();
                this.showToast('Playlist created successfully!', 'success');
            } else {
                this.showToast('Failed to create playlist: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Error creating playlist:', error);
            this.showToast('Failed to create playlist', 'error');
        }
    }

    showPlaylist(playlist) {
        this.currentPlaylist = playlist;
        this.navigateToPage('playlist-detail');

        const header = document.getElementById('playlist-header');
        const tracksContainer = document.getElementById('playlist-tracks');

        if (header) {
            header.innerHTML = `
                <div class="playlist-cover ${playlist.color}">
                    <svg viewBox="0 0 24 24" fill="white" width="80" height="80">
                        <path d="M15 6H3v2h12V6zm0 4H3v2h12v-2zM3 14h8v2H3v-2z"/>
                    </svg>
                </div>
                <div class="playlist-details">
                    <h1>${playlist.name}</h1>
                    <p>${playlist.description || 'Playlist'}</p>
                    <div class="playlist-details-meta">
                        <span>Created by User</span>
                        <span>${playlist.trackCount} songs</span>
                    </div>
                </div>
            `;
        }

        if (tracksContainer) {
            tracksContainer.innerHTML = `
                ${this.tracks.slice(0, 8).map((track, index) => `
                    <div class="track-item" data-track-id="${track.id}">
                        <div class="track-num">${index + 1}</div>
                        <div class="track-info">
                            <div class="track-title">${track.title}</div>
                            <div class="track-artist">${track.artist}</div>
                        </div>
                        <div class="track-album">${track.album}</div>
                        <div class="track-duration">${this.formatTime(track.duration)}</div>
                    </div>
                `).join('')}
            `;

            // Add click handlers
            tracksContainer.querySelectorAll('.track-item').forEach(item => {
                item.addEventListener('click', () => {
                    const trackId = parseInt(item.getAttribute('data-track-id'));
                    const track = this.tracks.find(t => t.id === trackId);
                    if (track) {
                        this.playTrack(track);
                    }
                });
            });
        }
    }

    async playTrack(track) {
        if (!track || !track.file_path) {
            this.showToast('No audio file available for this track', 'error');
            return;
        }

        try {
            this.currentTrack = track;
            this.duration = track.duration;

            // Set audio source and play
            this.audio.src = track.file_path;
            this.audio.currentTime = 0;

            await this.audio.play();

            this.isPlaying = true;
            this.currentTime = 0;
            this.updatePlayerInfo();

            // Add to play history via API
            await this.addToHistory(track);

            this.showToast(`Now playing: ${track.title}`, 'success');
        } catch (error) {
            console.error('Error playing track:', error);
            this.showToast(`Could not play "${track.title}"`, 'error');
            this.isPlaying = false;
        }
    }

    togglePlay() {
        if (!this.currentTrack) {
            // Start playing first track if none selected
            this.playTrack(this.tracks[0]);
            return;
        }

        if (this.isPlaying) {
            this.audio.pause();
        } else {
            this.audio.play().catch(error => {
                console.error('Error resuming playback:', error);
                this.showToast('Could not resume playback', 'error');
            });
        }
    }

    playPrevious() {
        if (!this.currentTrack) return;

        const currentIndex = this.tracks.findIndex(t => t.id === this.currentTrack.id);
        const prevIndex = currentIndex > 0 ? currentIndex - 1 : this.tracks.length - 1;
        this.playTrack(this.tracks[prevIndex]);
    }

    async playNext() {
        if (!this.currentTrack) return;

        if (this.isShuffle) {
            const randomIndex = Math.floor(Math.random() * this.tracks.length);
            await this.playTrack(this.tracks[randomIndex]);
        } else {
            const currentIndex = this.tracks.findIndex(t => t.id === this.currentTrack.id);
            const nextIndex = (currentIndex + 1) % this.tracks.length;
            await this.playTrack(this.tracks[nextIndex]);
        }
    }

    toggleShuffle() {
        this.isShuffle = !this.isShuffle;
        const shuffleBtn = document.getElementById('shuffle-btn');

        if (shuffleBtn) {
            shuffleBtn.classList.toggle('active', this.isShuffle);
        }
    }

    toggleRepeat() {
        this.isRepeat = !this.isRepeat;
        const repeatBtn = document.getElementById('repeat-btn');

        if (repeatBtn) {
            repeatBtn.classList.toggle('active', this.isRepeat);
        }
    }

    toggleLike() {
        if (!this.currentTrack) return;

        this.toggleTrackLike(this.currentTrack.id);
    }

    async toggleTrackLike(trackId) {
        const isLiked = this.likedTracks.has(trackId);
        const action = isLiked ? 'unlike' : 'like';

        // Update UI immediately (optimistic)
        if (isLiked) {
            this.likedTracks.delete(trackId);
        } else {
            this.likedTracks.add(trackId);
        }

        // Update track object
        const track = this.tracks.find(t => t.id === trackId);
        if (track) {
            track.liked = this.likedTracks.has(trackId);
        }

        // Update player like button
        const likeBtn = document.getElementById('like-btn');
        if (likeBtn) {
            likeBtn.classList.toggle('liked', this.likedTracks.has(trackId));
        }

        // Show toast
        this.showToast(
            this.likedTracks.has(trackId) ? 'Added to Liked Songs' : 'Removed from Liked Songs',
            'success'
        );

        // Call API in background
        try {
            await fetch(`api/index.php?path=/${action}/${trackId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: 1 })
            });
        } catch (error) {
            console.warn(`Failed to ${action} track:`, error);
        }
    }

    toggleMute() {
        this.isMuted = !this.isMuted;
        const volumeBtn = document.getElementById('volume-btn');

        if (volumeBtn) {
            volumeBtn.classList.remove('low', 'muted');
            if (this.isMuted) {
                volumeBtn.classList.add('muted');
            } else if (this.volume < 0.5) {
                volumeBtn.classList.add('low');
            }
        }
    }

    setVolume(percent) {
        this.volume = Math.max(0, Math.min(1, percent));
        const volumeLevel = document.getElementById('volume-level');
        const volumeHandle = document.getElementById('volume-handle');
        const volumeBtn = document.getElementById('volume-btn');

        if (volumeLevel) volumeLevel.style.width = `${this.volume * 100}%`;
        if (volumeHandle) volumeHandle.style.right = `${(1 - this.volume) * 100}%`;

        if (volumeBtn) {
            volumeBtn.classList.remove('low', 'muted');
            if (this.volume === 0) {
                this.isMuted = true;
                volumeBtn.classList.add('muted');
            } else if (this.volume < 0.5) {
                volumeBtn.classList.add('low');
            } else {
                this.isMuted = false;
            }
        }
    }

    seekTo(percent) {
        if (this.audio && this.duration) {
            this.currentTime = percent * this.duration;
            this.audio.currentTime = this.currentTime;
            this.updateProgress();
        }
    }

    updateVolumeUI() {
        const volumeLevel = document.getElementById('volume-level');
        const volumeHandle = document.getElementById('volume-handle');
        const volumeBtn = document.getElementById('volume-btn');

        if (volumeLevel) volumeLevel.style.width = `${this.volume * 100}%`;
        if (volumeHandle) volumeHandle.style.right = `${(1 - this.volume) * 100}%`;

        if (volumeBtn) {
            volumeBtn.classList.remove('low', 'muted');
            if (this.isMuted || this.volume === 0) {
                volumeBtn.classList.add('muted');
            } else if (this.volume < 0.5) {
                volumeBtn.classList.add('low');
            }
        }
    }

    updateProgress() {
        const progress = document.getElementById('progress');
        const progressHandle = document.getElementById('progress-handle');
        const currentTimeElement = document.getElementById('current-time');
        const totalTimeElement = document.getElementById('total-time');

        if (progress) progress.style.width = `${(this.currentTime / this.duration) * 100}%`;
        if (progressHandle) progressHandle.style.left = `${(this.currentTime / this.duration) * 100}%`;
        if (currentTimeElement) currentTimeElement.textContent = this.formatTime(this.currentTime);
        if (totalTimeElement) totalTimeElement.textContent = this.formatTime(this.duration);
    }

    updatePlayerInfo() {
        if (!this.currentTrack) return;

        const playerCover = document.getElementById('player-cover-img');
        const trackName = document.getElementById('player-track-name');
        const artistName = document.getElementById('player-artist-name');
        const likeBtn = document.getElementById('like-btn');

        if (playerCover) {
            playerCover.alt = this.currentTrack.title;
        }

        if (trackName) trackName.textContent = this.currentTrack.title;
        if (artistName) artistName.textContent = this.currentTrack.artist;
        if (likeBtn) likeBtn.classList.toggle('liked', this.likedTracks.has(this.currentTrack.id));
    }

    async addToHistory(track) {
        if (!track) return;
        this.playHistory.unshift(track);
        if (this.playHistory.length > 10) {
            this.playHistory.pop();
        }

        // Also send to API
        try {
            await fetch('api/index.php?path=/play-history', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    track_id: track.id,
                    user_id: 1
                })
            });
        } catch (error) {
            console.warn('Failed to add to play history:', error);
        }
    }

    switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });

        document.getElementById(`${tab}-tab`)?.classList.add('active');
    }

    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        localStorage.setItem('theme', this.currentTheme);
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.applyTheme();
    }

    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span>${message}</span>
            <button class="toast-close">&times;</button>
        `;

        container.appendChild(toast);

        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
}

// Initialize the app when the page loads
document.addEventListener('DOMContentLoaded', () => {
    window.musicApp = new MusicApp();
});