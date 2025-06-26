<?php
// test-save.php - Test penyimpanan data dalam text murni

// Set timezone
date_default_timezone_set('Asia/Jakarta');

echo "<h1>Test Penyimpanan Data Text Murni</h1>";

// Test buat direktori
$dataDir = 'data/';
if (!file_exists($dataDir)) {
    if (mkdir($dataDir, 0777, true)) {
        echo "<p style='color: green;'>✅ Direktori 'data/' berhasil dibuat</p>";
    } else {
        echo "<p style='color: red;'>❌ Gagal membuat direktori 'data/'</p>";
        exit;
    }
} else {
    echo "<p style='color: blue;'>📁 Direktori 'data/' sudah ada</p>";
}

// Test data dummy
$testContact = [
    'id' => 'TEST_' . time(),
    'name' => 'John Doe Test',
    'email' => 'john@test.com',
    'phone' => '+62812345678',
    'category' => 'Teman',
    'priority' => 'Tinggi',
    'address' => 'Jl. Test No. 123',
    'birthday' => '1990-01-01',
    'website' => 'https://test.com',
    'photo' => '',
    'notes' => 'Ini adalah data test untuk memastikan penyimpanan text murni bekerja',
    'date_created' => date('Y-m-d H:i:s'),
    'date_modified' => date('Y-m-d H:i:s')
];

echo "<h2>Data Test:</h2>";
echo "<pre>";
print_r($testContact);
echo "</pre>";

// Test simpan ke format JSON
$jsonFile = $dataDir . 'contacts.txt';
$jsonData = json_encode($testContact, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$separator = "\n" . str_repeat('=', 80) . "\n";
$dataToSave = $separator . $jsonData . $separator;

if (file_put_contents($jsonFile, $dataToSave, FILE_APPEND | LOCK_EX)) {
    echo "<p style='color: green;'>✅ Data berhasil disimpan ke contacts.txt</p>";
} else {
    echo "<p style='color: red;'>❌ Gagal menyimpan ke contacts.txt</p>";
}

// Test simpan ke format CSV
$csvFile = $dataDir . 'contacts.csv';
$csvHeader = "ID,Nama,Email,Telepon,Kategori,Prioritas,Alamat,Tanggal Lahir,Website,Foto,Catatan,Tanggal Dibuat\n";

if (!file_exists($csvFile)) {
    if (file_put_contents($csvFile, $csvHeader, LOCK_EX)) {
        echo "<p style='color: green;'>✅ Header CSV berhasil dibuat</p>";
    }
}

$csvData = [
    $testContact['id'],
    $testContact['name'],
    $testContact['email'],
    $testContact['phone'],
    $testContact['category'],
    $testContact['priority'],
    str_replace(["\r", "\n"], ' ', $testContact['address']),
    $testContact['birthday'],
    $testContact['website'],
    $testContact['photo'],
    str_replace(["\r", "\n"], ' ', $testContact['notes']),
    $testContact['date_created']
];

$csvLine = '"' . implode('","', $csvData) . "\"\n";
if (file_put_contents($csvFile, $csvLine, FILE_APPEND | LOCK_EX)) {
    echo "<p style='color: green;'>✅ Data berhasil disimpan ke contacts.csv</p>";
} else {
    echo "<p style='color: red;'>❌ Gagal menyimpan ke contacts.csv</p>";
}

// Test log aktivitas
$logFile = $dataDir . 'activity.log';
$logEntry = "[" . date('Y-m-d H:i:s') . "] TEST - Data test berhasil disimpan\n";
if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
    echo "<p style='color: green;'>✅ Log aktivitas berhasil disimpan</p>";
} else {
    echo "<p style='color: red;'>❌ Gagal menyimpan log aktivitas</p>";
}

// Test baca data
echo "<h2>Test Baca Data:</h2>";
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    echo "<h3>Isi file contacts.txt:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 1rem; border-radius: 5px;'>";
    echo htmlspecialchars($content);
    echo "</pre>";
}

if (file_exists($csvFile)) {
    $csvContent = file_get_contents($csvFile);
    echo "<h3>Isi file contacts.csv:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 1rem; border-radius: 5px;'>";
    echo htmlspecialchars($csvContent);
    echo "</pre>";
}

if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    echo "<h3>Isi file activity.log:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 1rem; border-radius: 5px;'>";
    echo htmlspecialchars($logContent);
    echo "</pre>";
}

// Test permissions
echo "<h2>Test Permissions:</h2>";
echo "<p>Data directory: " . (is_writable($dataDir) ? "✅ Writable" : "❌ Not writable") . "</p>";
echo "<p>Contacts file: " . (file_exists($jsonFile) && is_writable($jsonFile) ? "✅ Writable" : "❌ Not writable") . "</p>";
echo "<p>CSV file: " . (file_exists($csvFile) && is_writable($csvFile) ? "✅ Writable" : "❌ Not writable") . "</p>";

echo "<hr>";
echo "<p><strong>Jika semua test berhasil, maka sistem penyimpanan text murni sudah berfungsi!</strong></p>";
echo "<p><a href='add-contact.html'>← Kembali ke Form Tambah Kontak</a></p>";
echo "<p><a href='view-contacts.php'>← Lihat Data yang Tersimpan</a></p>";
