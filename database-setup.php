<?php
// database-setup.php - Setup Database MySQL (Opsional)

require_once 'config.php';

// SQL untuk membuat database dan tabel
$sql = "
CREATE DATABASE IF NOT EXISTS kontak_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE kontak_manager;

CREATE TABLE IF NOT EXISTS contacts (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    category ENUM('Keluarga', 'Teman', 'Kerja', 'Bisnis', 'Lainnya') NOT NULL,
    priority ENUM('Tinggi', 'Sedang', 'Rendah') DEFAULT NULL,
    address TEXT,
    birthday DATE,
    website VARCHAR(255),
    photo VARCHAR(255),
    notes TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_date_created (date_created)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(100) NOT NULL,
    contact_id VARCHAR(50),
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp),
    INDEX idx_contact_id (contact_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Fungsi untuk migrasi data dari file ke database
function migrateFromFileToDatabase()
{
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            throw new Exception('Tidak dapat koneksi ke database');
        }

        // Baca data dari file
        $contacts = [];
        if (file_exists(CONTACTS_FILE)) {
            $content = file_get_contents(CONTACTS_FILE);
            $separator = str_repeat('=', 80);
            $chunks = explode($separator, $content);

            foreach ($chunks as $chunk) {
                $chunk = trim($chunk);
                if (!empty($chunk)) {
                    $contact = json_decode($chunk, true);
                    if ($contact && is_array($contact)) {
                        $contacts[] = $contact;
                    }
                }
            }
        }

        // Insert ke database
        $stmt = $pdo->prepare("
            INSERT INTO contacts (
                id, name, email, phone, category, priority, 
                address, birthday, website, photo, notes, 
                date_created, date_modified
            ) VALUES (
                :id, :name, :email, :phone, :category, :priority,
                :address, :birthday, :website, :photo, :notes,
                :date_created, :date_modified
            ) ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                email = VALUES(email),
                phone = VALUES(phone),
                category = VALUES(category),
                priority = VALUES(priority),
                address = VALUES(address),
                birthday = VALUES(birthday),
                website = VALUES(website),
                photo = VALUES(photo),
                notes = VALUES(notes),
                date_modified = VALUES(date_modified)
        ");

        $migrated = 0;
        foreach ($contacts as $contact) {
            $stmt->execute([
                ':id' => $contact['id'] ?? '',
                ':name' => $contact['name'] ?? '',
                ':email' => $contact['email'] ?? '',
                ':phone' => $contact['phone'] ?? '',
                ':category' => $contact['category'] ?? 'Lainnya',
                ':priority' => $contact['priority'] ?: null,
                ':address' => $contact['address'] ?: null,
                ':birthday' => $contact['birthday'] ?: null,
                ':website' => $contact['website'] ?: null,
                ':photo' => $contact['photo'] ?: null,
                ':notes' => $contact['notes'] ?: null,
                ':date_created' => $contact['date_created'] ?? date('Y-m-d H:i:s'),
                ':date_modified' => $contact['date_modified'] ?? date('Y-m-d H:i:s')
            ]);
            $migrated++;
        }

        return [
            'success' => true,
            'message' => "Berhasil migrasi {$migrated} kontak ke database"
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Jalankan setup jika file ini diakses langsung
if (basename($_SERVER['PHP_SELF']) === 'database-setup.php') {
    echo "<h1>Setup Database Kontak Manager</h1>";

    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            echo "<p style='color: red;'>❌ Tidak dapat koneksi ke database. Periksa konfigurasi di config.php</p>";
            exit;
        }

        // Eksekusi SQL
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Database dan tabel berhasil dibuat</p>";

        // Migrasi data
        $result = migrateFromFileToDatabase();
        if ($result['success']) {
            echo "<p style='color: green;'>✅ " . $result['message'] . "</p>";
            echo "<p><strong>Langkah selanjutnya:</strong></p>";
            echo "<ol>";
            echo "<li>Ubah <code>useDatabase()</code> di config.php return true</li>";
            echo "<li>Backup file data lama</li>";
            echo "<li>Test aplikasi dengan database</li>";
            echo "</ol>";
        } else {
            echo "<p style='color: orange;'>⚠️ " . $result['message'] . "</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
