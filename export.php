<?php
// export.php - Export data kontak ke CSV

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk membaca data kontak dari file
function readContacts()
{
    $dataFile = 'data/contacts.txt';
    $contacts = [];

    if (file_exists($dataFile)) {
        $content = file_get_contents($dataFile);

        // Split berdasarkan separator
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

// Baca semua kontak
$contacts = readContacts();

if (empty($contacts)) {
    header('Location: view-contacts.php?error=no_data');
    exit;
}

// Set headers untuk download CSV
$filename = 'kontak_export_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Buka output stream
$output = fopen('php://output', 'w');

// Tulis BOM untuk UTF-8 agar Excel bisa membaca karakter khusus dengan benar
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header CSV
$headers = [
    'ID Kontak',
    'Nama Lengkap',
    'Email',
    'Nomor Telepon',
    'Kategori',
    'Prioritas',
    'Alamat',
    'Tanggal Lahir',
    'Website/Social Media',
    'Foto Profil',
    'Catatan',
    'Tanggal Dibuat',
    'Terakhir Dimodifikasi'
];

fputcsv($output, $headers);

// Tulis data kontak
foreach ($contacts as $contact) {
    $row = [
        $contact['id'] ?? '',
        $contact['name'] ?? '',
        $contact['email'] ?? '',
        $contact['phone'] ?? '',
        $contact['category'] ?? '',
        $contact['priority'] ?? '',
        str_replace(["\r\n", "\r", "\n"], ' | ', $contact['address'] ?? ''),
        $contact['birthday'] ?? '',
        $contact['website'] ?? '',
        $contact['photo'] ?? '',
        str_replace(["\r\n", "\r", "\n"], ' | ', $contact['notes'] ?? ''),
        $contact['date_created'] ?? '',
        $contact['date_modified'] ?? ''
    ];

    fputcsv($output, $row);
}

// Tutup stream
fclose($output);

// Log export activity
$logFile = 'data/activity.log';
$logEntry = "[" . date('Y-m-d H:i:s') . "] EXPORT CSV - " . count($contacts) . " kontak diekspor ke {$filename}\n";
file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

exit;
