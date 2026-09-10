# Melodify - Web Music Player

Melodify adalah aplikasi pemutar musik berbasis web modern yang terinspirasi dari antarmuka antarmuka Spotify. Aplikasi ini mendukung pemutaran lagu (simulasi & audio), manajemen playlist, pencarian lagu, serta alih tema terang/gelap (Light/Dark Mode).

---

## 🚀 Fitur Utama

- **Player Interface**: Kontrol Play, Pause, Next, Previous, Shuffle, Repeat, dan kontrol Volume.
- **Manajemen Playlist**: Membuat playlist baru secara interaktif melalui modal window.
- **Pencarian Real-time**: Filter lagu berdasarkan judul, nama penyanyi, atau album.
- **Dark / Light Mode**: Fitur pergantian tema yang tersimpan secara otomatis di `localStorage`.
- **Responsive Layout**: Tampilan antarmuka berbasis Flexbox dan CSS Grid.

---

## 🛠️ Teknologi yang Digunakan

- **Frontend**: HTML5, CSS3 (Custom Properties & Variables), JavaScript (ES6+ Native)
- **Backend (Opsional)**: PHP 8.x
- **Typography**: Inter (Google Fonts)

---

## 📂 Struktur Proyek

```text
melodify/
│
├── config/
│   └── config.php         # Konfigurasi aplikasi PHP
├── assets/
│   ├── audio/             # File audio MP3 (Opsional)
│   └── images/            # Gambar cover album (Opsional)
├── index.html             # Entry point (Versi HTML Statis)
├── index.php              # Entry point (Versi PHP Server)
├── app.js                 # Logika interaktif & state management
├── styles.css             # Styling stylesheet utama
└── README.md              # Dokumentasi proyek
