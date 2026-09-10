<?php
/**
 * Generate audio samples for Melodify Music App
 * Creates simple sine wave WAV files for demonstration
 */

function generateWavFile($filename, $frequency = 440, $duration = 30, $sampleRate = 44100) {
    // Create a simple sine wave
    $numSamples = $sampleRate * $duration;
    $amplitude = 0.25 * 32767; // 25% of max amplitude for 16-bit

    // WAV header
    $header = pack('A4VA8', 'RIFF', 36 + ($numSamples * 2), 'WAVEfmt ');
    $header .= pack('VVvVvvVV', 16, 1, 1, $sampleRate, $sampleRate * 2, 2, 16, 'data');
    $header .= pack('V', $numSamples * 2);

    $data = '';

    // Generate different melodic patterns based on track number
    $trackNum = intval(substr(basename($filename, '.wav'), -1));

    // Different melody for each track
    $melodies = [
        1 => [440, 493.88, 523.25, 587.33, 659.25], // C major scale
        2 => [392, 440, 493.88, 523.25, 587.33],    // G major
        3 => [329.63, 392, 440, 493.88, 523.25],    // F major
        4 => [261.63, 293.66, 329.63, 392, 440],    // C lower octave
        5 => [523.25, 587.33, 659.25, 698.46, 783.99], // C higher
        6 => [349.23, 392, 440, 493.88, 523.25],    // F
        7 => [293.66, 329.63, 392, 440, 493.88],    // D minor
        8 => [261.63, 293.66, 329.63, 349.23, 392]  // C pentatonic
    ];

    $melody = $melodies[$trackNum] ?? [440, 523.25, 659.25, 783.99];
    $notes = count($melody);
    $samplesPerNote = floor($numSamples / $notes);

    for ($i = 0; $i < $numSamples; $i++) {
        $noteIndex = floor($i / $samplesPerNote) % $notes;
        $freq = $melody[$noteIndex];

        // Add slight vibrato
        $vibrato = 1 + 0.02 * sin(2 * M_PI * 5 * $i / $sampleRate);
        $value = sin(2 * M_PI * $freq * $vibrato * $i / $sampleRate) * $amplitude;

        // Apply envelope (attack, sustain, release)
        $envelope = 1.0;
        if ($i < 4410) { // 0.1s attack
            $envelope = $i / 4410;
        } elseif ($i > $numSamples - 4410) { // 0.1s release
            $envelope = ($numSamples - $i) / 4410;
        }

        $value *= $envelope;

        // 16-bit PCM, little-endian
        $data .= pack('v', intval($value));
    }

    // Write file
    file_put_contents($filename, $header . $data);
}

// Main
echo "Generating audio files for Melodify...\n";

$audioDir = __DIR__ . '/assets/audio';
if (!is_dir($audioDir)) {
    mkdir($audioDir, 0755, true);
}

// Generate 8 tracks
$trackFiles = [
    'track_1' => [440, 25],   // A4, 25 seconds
    'track_2' => [493.88, 28], // B4
    'track_3' => [523.25, 27], // C5
    'track_4' => [587.33, 30], // D5
    'track_5' => [659.25, 26], // E5
    'track_6' => [698.46, 29], // F5
    'track_7' => [783.99, 27], // G5
    'track_8' => [880, 25]     // A5
];

foreach ($trackFiles as $track => [$baseFreq, $duration]) {
    $filename = $audioDir . '/' . $track . '.wav';
    echo "Generating: $track.wav ($duration seconds)... ";
    generateWavFile($filename, $baseFreq, $duration);
    echo "OK\n";
}

echo "\nDone! Generated " . count($trackFiles) . " audio files in $audioDir\n";
echo "File sizes:\n";

foreach (glob($audioDir . '/*.wav') as $file) {
    echo "  - " . basename($file) . ": " . filesize($file) . " bytes\n";
}

echo "\nAudio generation complete. You can now run setup_db.php to create database.\n";
?>