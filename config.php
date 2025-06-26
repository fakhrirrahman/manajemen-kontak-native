<?php
// config.php - Konfigurasi Database/File Storage

// Konfigurasi penyimpanan file
define('DATA_DIR', 'data/');
define('CONTACTS_FILE', DATA_DIR . 'contacts.txt');
define('CONTACTS_CSV_FILE', DATA_DIR . 'contacts.csv');
define('ACTIVITY_LOG_FILE', DATA_DIR . 'activity.log');
define('UPLOAD_DIR', 'uploads/photos/');

// Konfigurasi database (jika ingin upgrade ke database)
define('DB_HOST', 'localhost');
define('DB_NAME', 'kontak_manager');
define('DB_USER', 'root');
define('DB_PASS', '1');

// Konfigurasi aplikasi
define('ITEMS_PER_PAGE', 10);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk membuat direktori jika belum ada
function ensureDirectoryExists($dir)
{
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Inisialisasi direktori
ensureDirectoryExists(DATA_DIR);
ensureDirectoryExists(UPLOAD_DIR);

// Fungsi koneksi database (untuk upgrade masa depan)
function getDatabaseConnection()
{
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        // Fallback ke file storage jika database tidak tersedia
        return null;
    }
}

// Fungsi untuk mengecek apakah menggunakan database atau file
function useDatabase()
{
    // Return false untuk tetap menggunakan file storage
    // Ubah ke true jika ingin menggunakan database
    return false;
}
