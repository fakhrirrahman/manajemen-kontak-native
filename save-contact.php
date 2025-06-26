<?php
// save-contact.php - Menyimpan data kontak ke file text

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk membersihkan input
function cleanInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk upload foto
function uploadPhoto($file)
{
    $uploadDir = 'uploads/photos/';

    // Buat direktori jika belum ada
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validasi tipe file
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.'];
        }

        // Validasi ukuran file
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
        }

        // Generate nama file unik
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'photo_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;

        // Upload file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => true, 'path' => $filePath];
        } else {
            return ['success' => false, 'message' => 'Gagal mengupload file.'];
        }
    }

    return ['success' => true, 'path' => ''];
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data form
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);
    $category = cleanInput($_POST['category']);
    $priority = cleanInput($_POST['priority'] ?? '');
    $address = cleanInput($_POST['address'] ?? '');
    $birthday = cleanInput($_POST['birthday'] ?? '');
    $website = cleanInput($_POST['website'] ?? '');
    $notes = cleanInput($_POST['notes'] ?? '');

    // Validasi data wajib
    if (empty($name) || empty($email) || empty($phone) || empty($category)) {
        $message = 'Data wajib (nama, email, telepon, kategori) harus diisi!';
        $messageType = 'error';
    } else {
        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Format email tidak valid!';
            $messageType = 'error';
        } else {
            // Handle upload foto
            $photoPath = '';
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = uploadPhoto($_FILES['photo']);
                if (!$uploadResult['success']) {
                    $message = $uploadResult['message'];
                    $messageType = 'error';
                } else {
                    $photoPath = $uploadResult['path'];
                }
            }

            if ($messageType !== 'error') {
                // Generate ID unik untuk kontak
                $contactId = 'CONTACT_' . time() . '_' . uniqid();
                $dateCreated = date('Y-m-d H:i:s');

                // Format data untuk disimpan
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
                    'photo' => $photoPath,
                    'notes' => $notes,
                    'date_created' => $dateCreated,
                    'date_modified' => $dateCreated
                ];

                // Konversi ke format text (JSON untuk kemudahan parsing)
                $jsonData = json_encode($contactData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                // Nama file untuk menyimpan data
                $dataFile = 'data/contacts.txt';

                // Buat direktori jika belum ada
                if (!file_exists('data/')) {
                    mkdir('data/', 0777, true);
                }

                // Simpan data ke file text
                $separator = "\n" . str_repeat('=', 80) . "\n";
                $dataToSave = $separator . $jsonData . $separator;

                if (file_put_contents($dataFile, $dataToSave, FILE_APPEND | LOCK_EX)) {
                    // Juga simpan dalam format CSV untuk backup
                    $csvFile = 'data/contacts.csv';
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
                        $photoPath,
                        str_replace(["\r", "\n"], ' ', $notes),
                        $dateCreated
                    ];

                    // Header CSV jika file belum ada
                    if (!file_exists($csvFile)) {
                        $csvHeader = "ID,Nama,Email,Telepon,Kategori,Prioritas,Alamat,Tanggal Lahir,Website,Foto,Catatan,Tanggal Dibuat\n";
                        file_put_contents($csvFile, $csvHeader, LOCK_EX);
                    }

                    // Simpan data CSV
                    $csvLine = '"' . implode('","', $csvData) . "\"\n";
                    file_put_contents($csvFile, $csvLine, FILE_APPEND | LOCK_EX);

                    $message = "Kontak '{$name}' berhasil disimpan dengan ID: {$contactId}";
                    $messageType = 'success';

                    // Log aktivitas
                    $logFile = 'data/activity.log';
                    $logEntry = "[" . date('Y-m-d H:i:s') . "] KONTAK DITAMBAH - ID: {$contactId}, Nama: {$name}, Email: {$email}\n";
                    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
                } else {
                    $message = 'Gagal menyimpan data kontak. Periksa permission folder.';
                    $messageType = 'error';
                }
            }
        }
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
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($messageType === 'success'): ?>
            <div style="text-align: center; margin-top: 2rem;">
                <h2><i class="fas fa-check-circle" style="color: #4CAF50;"></i> Kontak Berhasil Disimpan!</h2>

                <div style="background: #f8f9fa; padding: 2rem; border-radius: 10px; margin: 2rem 0; text-align: left;">
                    <h3>Detail Kontak yang Disimpan:</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><strong><i class="fas fa-user"></i> Nama:</strong> <?php echo htmlspecialchars($name); ?></li>
                        <li><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($email); ?></li>
                        <li><strong><i class="fas fa-phone"></i> Telepon:</strong> <?php echo htmlspecialchars($phone); ?></li>
                        <li><strong><i class="fas fa-tags"></i> Kategori:</strong> <?php echo htmlspecialchars($category); ?></li>
                        <?php if (!empty($priority)): ?>
                            <li><strong><i class="fas fa-star"></i> Prioritas:</strong> <?php echo htmlspecialchars($priority); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($address)): ?>
                            <li><strong><i class="fas fa-map-marker-alt"></i> Alamat:</strong> <?php echo nl2br(htmlspecialchars($address)); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($birthday)): ?>
                            <li><strong><i class="fas fa-birthday-cake"></i> Tanggal Lahir:</strong> <?php echo htmlspecialchars($birthday); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($website)): ?>
                            <li><strong><i class="fas fa-globe"></i> Website:</strong> <a href="<?php echo htmlspecialchars($website); ?>" target="_blank"><?php echo htmlspecialchars($website); ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($photoPath)): ?>
                            <li><strong><i class="fas fa-camera"></i> Foto:</strong> <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Foto Profil" style="max-width: 100px; max-height: 100px; border-radius: 10px; margin-left: 10px;"></li>
                        <?php endif; ?>
                        <?php if (!empty($notes)): ?>
                            <li><strong><i class="fas fa-sticky-note"></i> Catatan:</strong> <?php echo nl2br(htmlspecialchars($notes)); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="add-contact.html" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Kontak Lagi
                    </a>
                    <a href="view-contacts.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Lihat Semua Kontak
                    </a>
                    <a href="index.html" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 2rem;">
                <h2><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Terjadi Kesalahan</h2>
                <p>Silakan perbaiki kesalahan di atas dan coba lagi.</p>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                    <a href="add-contact.html" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Form
                    </a>
                    <a href="index.html" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Kembali ke Beranda
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

    <script src="script.js"></script>
    <?php if ($messageType === 'success'): ?>
        <script>
            // Auto redirect setelah 10 detik
            setTimeout(function() {
                if (confirm('Ingin melihat semua kontak yang tersimpan?')) {
                    window.location.href = 'view-contacts.php';
                }
            }, 10000);

            // Show success toast
            showToast('Kontak berhasil disimpan!', 'success');
        </script>
    <?php endif; ?>
</body>

</html>