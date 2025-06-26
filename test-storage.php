<?php
// Test Storage - Uji Penyimpanan Text Murni
echo "<h2>🧪 Test Penyimpanan Text Murni</h2>";

// Test 1: Cek permissions folder data
$dataDir = 'data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
    echo "✅ Folder data dibuat<br>";
} else {
    echo "✅ Folder data sudah ada<br>";
}

if (is_writable($dataDir)) {
    echo "✅ Folder data bisa ditulis<br>";
} else {
    echo "❌ Folder data tidak bisa ditulis!<br>";
    echo "Jalankan: chmod 755 " . realpath($dataDir) . "<br>";
}

// Test 2: Tulis file text sederhana
$testFile = $dataDir . '/test.txt';
$testData = "Test data: " . date('Y-m-d H:i:s') . "\n";

if (file_put_contents($testFile, $testData, FILE_APPEND | LOCK_EX)) {
    echo "✅ Berhasil menulis ke file text<br>";
} else {
    echo "❌ Gagal menulis ke file text<br>";
}

// Test 3: Baca file text
if (file_exists($testFile)) {
    $content = file_get_contents($testFile);
    echo "✅ Berhasil membaca file text:<br>";
    echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd;'>";
    echo htmlspecialchars($content);
    echo "</pre>";
} else {
    echo "❌ File test tidak ditemukan<br>";
}

// Test 4: Test JSON storage
$jsonFile = $dataDir . '/test.json';
$jsonData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'test' => 'JSON storage test',
    'status' => 'working'
];

if (file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT))) {
    echo "✅ Berhasil menulis JSON<br>";

    $readJson = json_decode(file_get_contents($jsonFile), true);
    if ($readJson) {
        echo "✅ Berhasil membaca JSON:<br>";
        echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd;'>";
        echo json_encode($readJson, JSON_PRETTY_PRINT);
        echo "</pre>";
    }
} else {
    echo "❌ Gagal menulis JSON<br>";
}

// Test 5: Test CSV storage
$csvFile = $dataDir . '/test.csv';
$csvData = [
    ['Nama', 'Email', 'Timestamp'],
    ['Test User', 'test@example.com', date('Y-m-d H:i:s')]
];

$csvHandle = fopen($csvFile, 'w');
if ($csvHandle) {
    foreach ($csvData as $row) {
        fputcsv($csvHandle, $row);
    }
    fclose($csvHandle);
    echo "✅ Berhasil menulis CSV<br>";

    if (file_exists($csvFile)) {
        echo "✅ File CSV berhasil dibuat:<br>";
        echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd;'>";
        echo htmlspecialchars(file_get_contents($csvFile));
        echo "</pre>";
    }
} else {
    echo "❌ Gagal menulis CSV<br>";
}

echo "<hr>";
echo "<h3>📁 Isi Folder Data:</h3>";
$files = scandir($dataDir);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $filepath = $dataDir . '/' . $file;
        $size = filesize($filepath);
        $modified = date('Y-m-d H:i:s', filemtime($filepath));
        echo "📄 $file ($size bytes) - Modified: $modified<br>";
    }
}

echo "<hr>";
echo "<p><strong>Kesimpulan:</strong> Jika semua test menunjukkan ✅, maka penyimpanan text murni berjalan dengan baik!</p>";
echo "<p><a href='add-contact-simple.html'>🔗 Test Form Sederhana</a> | <a href='view-contacts-simple.php'>📋 Lihat Data</a></p>";
