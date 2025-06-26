<?php
// database-functions.php - Fungsi Database yang mendukung File & MySQL

require_once 'config.php';

// Fungsi untuk membaca semua kontak
function getAllContacts()
{
    if (useDatabase()) {
        return getContactsFromDatabase();
    } else {
        return getContactsFromFile();
    }
}

// Fungsi untuk menyimpan kontak
function saveContact($contactData)
{
    if (useDatabase()) {
        return saveContactToDatabase($contactData);
    } else {
        return saveContactToFile($contactData);
    }
}

// Fungsi untuk mendapatkan kontak berdasarkan ID
function getContactById($id)
{
    if (useDatabase()) {
        return getContactFromDatabase($id);
    } else {
        return getContactFromFile($id);
    }
}

// =================== FILE STORAGE FUNCTIONS ===================

function getContactsFromFile()
{
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

    return $contacts;
}

function saveContactToFile($contactData)
{
    try {
        ensureDirectoryExists(DATA_DIR);

        // Format data untuk disimpan
        $jsonData = json_encode($contactData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $separator = "\n" . str_repeat('=', 80) . "\n";
        $dataToSave = $separator . $jsonData . $separator;

        // Simpan ke file utama
        if (!file_put_contents(CONTACTS_FILE, $dataToSave, FILE_APPEND | LOCK_EX)) {
            throw new Exception('Gagal menyimpan ke file utama');
        }

        // Simpan backup CSV
        saveContactToCSV($contactData);

        // Log aktivitas
        logActivity('KONTAK_DITAMBAH', $contactData['id'], "Nama: {$contactData['name']}, Email: {$contactData['email']}");

        return ['success' => true, 'message' => 'Kontak berhasil disimpan'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getContactFromFile($id)
{
    $contacts = getContactsFromFile();
    foreach ($contacts as $contact) {
        if ($contact['id'] === $id) {
            return $contact;
        }
    }
    return null;
}

function saveContactToCSV($contactData)
{
    $csvHeader = "ID,Nama,Email,Telepon,Kategori,Prioritas,Alamat,Tanggal Lahir,Website,Foto,Catatan,Tanggal Dibuat\n";

    if (!file_exists(CONTACTS_CSV_FILE)) {
        file_put_contents(CONTACTS_CSV_FILE, $csvHeader, LOCK_EX);
    }

    $csvData = [
        $contactData['id'],
        $contactData['name'],
        $contactData['email'],
        $contactData['phone'],
        $contactData['category'],
        $contactData['priority'] ?? '',
        str_replace(["\r", "\n"], ' ', $contactData['address'] ?? ''),
        $contactData['birthday'] ?? '',
        $contactData['website'] ?? '',
        $contactData['photo'] ?? '',
        str_replace(["\r", "\n"], ' ', $contactData['notes'] ?? ''),
        $contactData['date_created']
    ];

    $csvLine = '"' . implode('","', $csvData) . "\"\n";
    file_put_contents(CONTACTS_CSV_FILE, $csvLine, FILE_APPEND | LOCK_EX);
}

// =================== DATABASE FUNCTIONS ===================

function getContactsFromDatabase()
{
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            throw new Exception('Koneksi database gagal');
        }

        $stmt = $pdo->query("SELECT * FROM contacts ORDER BY date_created DESC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        // Fallback ke file jika database error
        return getContactsFromFile();
    }
}

function saveContactToDatabase($contactData)
{
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            throw new Exception('Koneksi database gagal');
        }

        $stmt = $pdo->prepare("
            INSERT INTO contacts (
                id, name, email, phone, category, priority,
                address, birthday, website, photo, notes,
                date_created, date_modified
            ) VALUES (
                :id, :name, :email, :phone, :category, :priority,
                :address, :birthday, :website, :photo, :notes,
                :date_created, :date_modified
            )
        ");

        $result = $stmt->execute([
            ':id' => $contactData['id'],
            ':name' => $contactData['name'],
            ':email' => $contactData['email'],
            ':phone' => $contactData['phone'],
            ':category' => $contactData['category'],
            ':priority' => $contactData['priority'] ?: null,
            ':address' => $contactData['address'] ?: null,
            ':birthday' => $contactData['birthday'] ?: null,
            ':website' => $contactData['website'] ?: null,
            ':photo' => $contactData['photo'] ?: null,
            ':notes' => $contactData['notes'] ?: null,
            ':date_created' => $contactData['date_created'],
            ':date_modified' => $contactData['date_modified']
        ]);

        if ($result) {
            logActivity('KONTAK_DITAMBAH', $contactData['id'], "Nama: {$contactData['name']}, Email: {$contactData['email']}");
            return ['success' => true, 'message' => 'Kontak berhasil disimpan ke database'];
        } else {
            throw new Exception('Gagal menyimpan ke database');
        }
    } catch (Exception $e) {
        // Fallback ke file storage
        return saveContactToFile($contactData);
    }
}

function getContactFromDatabase($id)
{
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            throw new Exception('Koneksi database gagal');
        }

        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return getContactFromFile($id);
    }
}

// =================== UTILITY FUNCTIONS ===================

function logActivity($action, $contactId = '', $details = '')
{
    $timestamp = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    if (useDatabase()) {
        try {
            $pdo = getDatabaseConnection();
            if ($pdo) {
                $stmt = $pdo->prepare("
                    INSERT INTO activity_log (action, contact_id, details, ip_address, user_agent)
                    VALUES (:action, :contact_id, :details, :ip_address, :user_agent)
                ");
                $stmt->execute([
                    ':action' => $action,
                    ':contact_id' => $contactId,
                    ':details' => $details,
                    ':ip_address' => $ipAddress,
                    ':user_agent' => $userAgent
                ]);
                return;
            }
        } catch (Exception $e) {
            // Fallback ke file log
        }
    }

    // File log fallback
    $logEntry = "[{$timestamp}] {$action} - ID: {$contactId}, Details: {$details}, IP: {$ipAddress}\n";
    file_put_contents(ACTIVITY_LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

function getContactStats($contacts = null)
{
    if ($contacts === null) {
        $contacts = getAllContacts();
    }

    $stats = [
        'total' => count($contacts),
        'categories' => [],
        'priorities' => [],
        'recent' => 0
    ];

    $oneWeekAgo = strtotime('-1 week');

    foreach ($contacts as $contact) {
        // Hitung kategori
        $category = $contact['category'] ?? 'Tidak ada';
        $stats['categories'][$category] = ($stats['categories'][$category] ?? 0) + 1;

        // Hitung prioritas
        $priority = $contact['priority'] ?? 'Tidak ada';
        $stats['priorities'][$priority] = ($stats['priorities'][$priority] ?? 0) + 1;

        // Hitung kontak baru (1 minggu terakhir)
        if (isset($contact['date_created'])) {
            $dateCreated = strtotime($contact['date_created']);
            if ($dateCreated > $oneWeekAgo) {
                $stats['recent']++;
            }
        }
    }

    return $stats;
}

// Fungsi untuk membersihkan input
function cleanInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
