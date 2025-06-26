<?php
// contact-detail.php - Menampilkan detail kontak

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

// Ambil ID kontak dari parameter URL
$contactId = $_GET['id'] ?? '';
$contact = null;

if (!empty($contactId)) {
    $contacts = readContacts();
    foreach ($contacts as $c) {
        if ($c['id'] === $contactId) {
            $contact = $c;
            break;
        }
    }
}

// Jika kontak tidak ditemukan
if (!$contact) {
    header('Location: view-contacts.php?error=not_found');
    exit;
}

// Hitung umur jika ada tanggal lahir
$age = '';
if (!empty($contact['birthday'])) {
    $birthDate = new DateTime($contact['birthday']);
    $today = new DateTime();
    $ageObj = $today->diff($birthDate);
    $age = $ageObj->y . ' tahun, ' . $ageObj->m . ' bulan';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kontak: <?php echo htmlspecialchars($contact['name']); ?> - Sistem Manajemen Kontak</title>
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

    <!-- Detail Container -->
    <div class="form-container">
        <!-- Header Profile -->
        <div style="text-align: center; margin-bottom: 3rem; padding: 2rem; background: linear-gradient(135deg, #4CAF50, #45a049); border-radius: 15px; color: white;">
            <div style="margin-bottom: 1rem;">
                <?php if (!empty($contact['photo']) && file_exists($contact['photo'])): ?>
                    <img src="<?php echo htmlspecialchars($contact['photo']); ?>"
                        alt="Foto Profil"
                        style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                <?php else: ?>
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.2); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 2.5rem; margin: 0 auto; border: 4px solid white;">
                        <?php echo strtoupper(substr($contact['name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            <h1 style="margin: 0; font-size: 2.5rem;"><?php echo htmlspecialchars($contact['name']); ?></h1>
            <?php if (!empty($contact['category'])): ?>
                <p style="margin: 0.5rem 0; font-size: 1.2rem; opacity: 0.9;">
                    <?php
                    $categoryIcons = [
                        'Keluarga' => '👨‍👩‍👧‍👦',
                        'Teman' => '👥',
                        'Kerja' => '💼',
                        'Bisnis' => '🏢',
                        'Lainnya' => '📝'
                    ];
                    echo ($categoryIcons[$contact['category']] ?? '📝') . ' ' . htmlspecialchars($contact['category']);
                    ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($contact['priority'])): ?>
                <p style="margin: 0.5rem 0; font-size: 1rem;">
                    <?php
                    $priorityIcons = [
                        'Tinggi' => '🔴',
                        'Sedang' => '🟡',
                        'Rendah' => '🟢'
                    ];
                    echo ($priorityIcons[$contact['priority']] ?? '') . ' Prioritas: ' . htmlspecialchars($contact['priority']);
                    ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Contact Information Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <!-- Informasi Kontak -->
            <div class="info-card" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #4CAF50; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-address-card" style="margin-right: 0.5rem;"></i>
                    Informasi Kontak
                </h3>

                <div class="info-item" style="margin-bottom: 1rem; display: flex; align-items: center;">
                    <i class="fas fa-envelope" style="width: 20px; color: #4CAF50; margin-right: 1rem;"></i>
                    <div>
                        <strong>Email:</strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" style="color: #2196F3;">
                            <?php echo htmlspecialchars($contact['email']); ?>
                        </a>
                    </div>
                </div>

                <div class="info-item" style="margin-bottom: 1rem; display: flex; align-items: center;">
                    <i class="fas fa-phone" style="width: 20px; color: #4CAF50; margin-right: 1rem;"></i>
                    <div>
                        <strong>Telepon:</strong><br>
                        <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" style="color: #2196F3;">
                            <?php echo htmlspecialchars($contact['phone']); ?>
                        </a>
                    </div>
                </div>

                <?php if (!empty($contact['website'])): ?>
                    <div class="info-item" style="margin-bottom: 1rem; display: flex; align-items: center;">
                        <i class="fas fa-globe" style="width: 20px; color: #4CAF50; margin-right: 1rem;"></i>
                        <div>
                            <strong>Website:</strong><br>
                            <a href="<?php echo htmlspecialchars($contact['website']); ?>" target="_blank" style="color: #2196F3;">
                                <?php echo htmlspecialchars($contact['website']); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($contact['address'])): ?>
                    <div class="info-item" style="margin-bottom: 1rem; display: flex; align-items: flex-start;">
                        <i class="fas fa-map-marker-alt" style="width: 20px; color: #4CAF50; margin-right: 1rem; margin-top: 0.2rem;"></i>
                        <div>
                            <strong>Alamat:</strong><br>
                            <?php echo nl2br(htmlspecialchars($contact['address'])); ?>
                            <br><a href="https://maps.google.com/?q=<?php echo urlencode($contact['address']); ?>" target="_blank" style="color: #2196F3; font-size: 0.9rem;">
                                <i class="fas fa-external-link-alt"></i> Lihat di Google Maps
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Informasi Personal -->
            <div class="info-card" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #4CAF50; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-user" style="margin-right: 0.5rem;"></i>
                    Informasi Personal
                </h3>

                <?php if (!empty($contact['birthday'])): ?>
                    <div class="info-item" style="margin-bottom: 1rem; display: flex; align-items: center;">
                        <i class="fas fa-birthday-cake" style="width: 20px; color: #4CAF50; margin-right: 1rem;"></i>
                        <div>
                            <strong>Tanggal Lahir:</strong><br>
                            <?php
                            $birthDate = new DateTime($contact['birthday']);
                            echo $birthDate->format('d F Y');
                            ?>
                            <?php if (!empty($age)): ?>
                                <br><small style="color: #666;">Umur: <?php echo $age; ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="info-item" style="margin-bottom: 1rem; display: flex; align-items: center;">
                    <i class="fas fa-calendar-plus" style="width: 20px; color: #4CAF50; margin-right: 1rem;"></i>
                    <div>
                        <strong>Kontak Dibuat:</strong><br>
                        <?php
                        if (!empty($contact['date_created'])) {
                            $dateCreated = new DateTime($contact['date_created']);
                            echo $dateCreated->format('d F Y, H:i') . ' WIB';
                        } else {
                            echo 'Tidak tersedia';
                        }
                        ?>
                    </div>
                </div>

                <?php if (!empty($contact['date_modified']) && $contact['date_modified'] !== $contact['date_created']): ?>
                    <div class="info-item" style="margin-bottom: 1rem; display: flex; align-items: center;">
                        <i class="fas fa-edit" style="width: 20px; color: #4CAF50; margin-right: 1rem;"></i>
                        <div>
                            <strong>Terakhir Diupdate:</strong><br>
                            <?php
                            $dateModified = new DateTime($contact['date_modified']);
                            echo $dateModified->format('d F Y, H:i') . ' WIB';
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="info-item" style="margin-bottom: 1rem; display: flex; align-items: center;">
                    <i class="fas fa-fingerprint" style="width: 20px; color: #4CAF50; margin-right: 1rem;"></i>
                    <div>
                        <strong>ID Kontak:</strong><br>
                        <code style="background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.8rem;">
                            <?php echo htmlspecialchars($contact['id']); ?>
                        </code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan -->
        <?php if (!empty($contact['notes'])): ?>
            <div class="info-card" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 3rem;">
                <h3 style="color: #4CAF50; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-sticky-note" style="margin-right: 0.5rem;"></i>
                    Catatan
                </h3>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border-left: 4px solid #4CAF50;">
                    <?php echo nl2br(htmlspecialchars($contact['notes'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div style="background: #f8f9fa; padding: 2rem; border-radius: 15px; text-align: center;">
            <h3 style="margin-bottom: 1.5rem; color: #333;">Aksi Cepat</h3>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="btn btn-primary">
                    <i class="fas fa-envelope"></i> Kirim Email
                </a>
                <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" class="btn btn-primary">
                    <i class="fas fa-phone"></i> Telepon
                </a>
                <?php if (!empty($contact['address'])): ?>
                    <a href="https://maps.google.com/?q=<?php echo urlencode($contact['address']); ?>" target="_blank" class="btn btn-secondary">
                        <i class="fas fa-map-marker-alt"></i> Lihat Lokasi
                    </a>
                <?php endif; ?>
                <?php if (!empty($contact['website'])): ?>
                    <a href="<?php echo htmlspecialchars($contact['website']); ?>" target="_blank" class="btn btn-secondary">
                        <i class="fas fa-globe"></i> Kunjungi Website
                    </a>
                <?php endif; ?>
            </div>

            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #ddd;">
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="view-contacts.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                    <a href="add-contact.html" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Tambah Kontak Baru
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Cetak Detail
                    </button>
                    <button onclick="shareContact()" class="btn btn-secondary">
                        <i class="fas fa-share"></i> Bagikan Kontak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Sistem Manajemen Kontak. Dibuat dengan <i class="fas fa-heart"></i> untuk kemudahan Anda.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Fungsi untuk membagikan kontak
        function shareContact() {
            const contact = {
                name: <?php echo json_encode($contact['name']); ?>,
                email: <?php echo json_encode($contact['email']); ?>,
                phone: <?php echo json_encode($contact['phone']); ?>,
                category: <?php echo json_encode($contact['category'] ?? ''); ?>,
                website: <?php echo json_encode($contact['website'] ?? ''); ?>
            };

            if (navigator.share) {
                // Gunakan Web Share API jika tersedia
                navigator.share({
                    title: 'Kontak: ' + contact.name,
                    text: `Nama: ${contact.name}\nEmail: ${contact.email}\nTelepon: ${contact.phone}\nKategori: ${contact.category}`,
                    url: window.location.href
                });
            } else {
                // Fallback: copy ke clipboard
                const contactText = `Nama: ${contact.name}\nEmail: ${contact.email}\nTelepon: ${contact.phone}\nKategori: ${contact.category}${contact.website ? '\nWebsite: ' + contact.website : ''}`;

                navigator.clipboard.writeText(contactText).then(function() {
                    showToast('Informasi kontak telah disalin ke clipboard!', 'success');
                }).catch(function() {
                    // Fallback untuk browser lama
                    prompt('Salin informasi kontak berikut:', contactText);
                });
            }
        }

        // Animasi entrance
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.info-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });

        // Print styles
        const printStyles = `
            @media print {
                .navbar, footer, .btn { display: none !important; }
                body { background: white !important; }
                .form-container { margin: 0 !important; padding: 1rem !important; }
            }
        `;
        const styleSheet = document.createElement('style');
        styleSheet.textContent = printStyles;
        document.head.appendChild(styleSheet);
    </script>
</body>

</html>