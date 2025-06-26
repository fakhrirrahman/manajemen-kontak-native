<?php
// save-contact-simple.php - Penyimpanan data dalam text murni (SEDERHANA)

// Set timezone
date_default_timezone_set('Asia/Jakarta');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: tampilkan data yang diterima
    echo "<h3>Debug - Data yang diterima:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    // Ambil data dari form
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $priority = trim($_POST['priority'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Validasi data wajib
    if (empty($name) || empty($email) || empty($phone) || empty($category)) {
        $message = 'Data wajib (nama, email, telepon, kategori) harus diisi!';
        $messageType = 'error';
    } else {
        // Buat direktori data jika belum ada
        if (!file_exists('data/')) {
            mkdir('data/', 0777, true);
        }

        // Generate ID unik
        $contactId = 'CONTACT_' . date('YmdHis') . '_' . uniqid();
        $dateCreated = date('Y-m-d H:i:s');

        // ========== FORMAT 1: JSON SEDERHANA ==========
        $contactData = [
            'id' => $contactId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'category' => $category,
            'priority' => $priority,
            'address' => $address,
            'birthday' => $birthday,
            'website' => $website,
            'notes' => $notes,
            'date_created' => $dateCreated
        ];

        $jsonData = json_encode($contactData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $separator = str_repeat('=', 50) . "\n";
        $dataToSave = $separator . $jsonData . "\n" . $separator . "\n";

        // Simpan ke file JSON
        $jsonFile = 'data/contacts.txt';
        if (file_put_contents($jsonFile, $dataToSave, FILE_APPEND | LOCK_EX)) {
            echo "<p style='color: green;'>✅ Data berhasil disimpan ke contacts.txt</p>";
        } else {
            echo "<p style='color: red;'>❌ Gagal menyimpan ke contacts.txt</p>";
        }

        // ========== FORMAT 2: TEXT PLAIN SEDERHANA ==========
        $plainTextData = "KONTAK ID: $contactId\n";
        $plainTextData .= "Nama: $name\n";
        $plainTextData .= "Email: $email\n";
        $plainTextData .= "Telepon: $phone\n";
        $plainTextData .= "Kategori: $category\n";
        $plainTextData .= "Prioritas: $priority\n";
        $plainTextData .= "Alamat: $address\n";
        $plainTextData .= "Tanggal Lahir: $birthday\n";
        $plainTextData .= "Website: $website\n";
        $plainTextData .= "Catatan: $notes\n";
        $plainTextData .= "Tanggal Dibuat: $dateCreated\n";
        $plainTextData .= str_repeat('-', 50) . "\n\n";

        // Simpan ke file plain text
        $plainFile = 'data/contacts_plain.txt';
        if (file_put_contents($plainFile, $plainTextData, FILE_APPEND | LOCK_EX)) {
            echo "<p style='color: green;'>✅ Data berhasil disimpan ke contacts_plain.txt</p>";
        } else {
            echo "<p style='color: red;'>❌ Gagal menyimpan ke contacts_plain.txt</p>";
        }

        // ========== FORMAT 3: CSV SEDERHANA ==========
        $csvFile = 'data/contacts.csv';

        // Buat header jika file belum ada
        if (!file_exists($csvFile)) {
            $csvHeader = "ID,Nama,Email,Telepon,Kategori,Prioritas,Alamat,Tanggal_Lahir,Website,Catatan,Tanggal_Dibuat\n";
            file_put_contents($csvFile, $csvHeader, LOCK_EX);
        }

        // Data CSV
        $csvData = [
            $contactId,
            $name,
            $email,
            $phone,
            $category,
            $priority,
            str_replace(["\r", "\n"], ' ', $address),
            $birthday,
            $website,
            str_replace(["\r", "\n"], ' ', $notes),
            $dateCreated
        ];

        $csvLine = '"' . implode('","', $csvData) . "\"\n";
        if (file_put_contents($csvFile, $csvLine, FILE_APPEND | LOCK_EX)) {
            echo "<p style='color: green;'>✅ Data berhasil disimpan ke contacts.csv</p>";
        } else {
            echo "<p style='color: red;'>❌ Gagal menyimpan ke contacts.csv</p>";
        }

        // ========== LOG AKTIVITAS ==========
        $logFile = 'data/activity.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] KONTAK DITAMBAH - ID: $contactId, Nama: $name, Email: $email\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        $message = "Kontak '$name' berhasil disimpan dengan ID: $contactId";
        $messageType = 'success';

        echo "<h3>Data yang tersimpan:</h3>";
        echo "<h4>1. Format JSON (contacts.txt):</h4>";
        echo "<pre style='background: #f5f5f5; padding: 1rem; border-radius: 5px;'>";
        echo htmlspecialchars($jsonData);
        echo "</pre>";

        echo "<h4>2. Format Plain Text (contacts_plain.txt):</h4>";
        echo "<pre style='background: #f5f5f5; padding: 1rem; border-radius: 5px;'>";
        echo htmlspecialchars($plainTextData);
        echo "</pre>";

        echo "<h4>3. Format CSV (contacts.csv):</h4>";
        echo "<pre style='background: #f5f5f5; padding: 1rem; border-radius: 5px;'>";
        echo htmlspecialchars($csvLine);
        echo "</pre>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Penyimpanan - Sistem Manajemen Kontak</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <!-- Header dengan Navigasi -->
    <header>
        <nav class="navbar">
            <div class="nav-brand">
                <i class="fas fa-address-book"></i>
                <span>Kontak Manager</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.html"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="add-contact.html"><i class="fas fa-plus"></i> Tambah Kontak</a></li>
                <li><a href="view-contacts.php"><i class="fas fa-list"></i> Lihat Kontak</a></li>
                <li><a href="about.html"><i class="fas fa-info-circle"></i> Tentang</a></li>
            </ul>
        </nav>
    </header>

    <!-- Result Container -->
    <div class="form-container">
        <h1><i class="fas fa-save"></i> Hasil Penyimpanan Data Text Murni</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($messageType === 'success'): ?>
            <div style="text-align: center; margin-top: 2rem;">
                <h2><i class="fas fa-check-circle" style="color: #4CAF50;"></i> Data Berhasil Disimpan dalam Text Murni!</h2>

                <div style="background: #e8f5e8; padding: 2rem; border-radius: 10px; margin: 2rem 0; text-align: left;">
                    <h3>📁 File yang dibuat:</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><strong><i class="fas fa-file-code"></i> data/contacts.txt</strong> - Format JSON</li>
                        <li><strong><i class="fas fa-file-alt"></i> data/contacts_plain.txt</strong> - Format Plain Text</li>
                        <li><strong><i class="fas fa-file-csv"></i> data/contacts.csv</strong> - Format CSV</li>
                        <li><strong><i class="fas fa-file"></i> data/activity.log</strong> - Log Aktivitas</li>
                    </ul>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="add-contact-simple.html" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Kontak Lagi
                    </a>
                    <a href="view-contacts-simple.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Lihat Data Text
                    </a>
                    <a href="test-save.php" class="btn btn-secondary">
                        <i class="fas fa-flask"></i> Test System
                    </a>
                    <a href="index.html" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Beranda
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 2rem;">
                <h2><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Terjadi Kesalahan</h2>
                <p>Silakan perbaiki kesalahan dan coba lagi.</p>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                    <a href="add-contact-simple.html" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Form
                    </a>
                    <a href="test-save.php" class="btn btn-secondary">
                        <i class="fas fa-flask"></i> Test System
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Sistem Manajemen Kontak. Dibuat dengan <i class="fas fa-heart"></i> untuk kemudahan Anda.</p>
        </div>
    </footer>
</body>

</html>