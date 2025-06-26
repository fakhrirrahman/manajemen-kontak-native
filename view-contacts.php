<?php
// view-contacts.php - Menampilkan data kontak dari file text

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

// Fungsi untuk menghitung statistik
function getContactStats($contacts)
{
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

// Baca semua kontak
$contacts = readContacts();

// Filter dan pencarian
$searchTerm = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';

// Filter contacts berdasarkan search dan filter
$filteredContacts = $contacts;

if (!empty($searchTerm)) {
    $filteredContacts = array_filter($filteredContacts, function ($contact) use ($searchTerm) {
        $searchFields = [
            $contact['name'] ?? '',
            $contact['email'] ?? '',
            $contact['phone'] ?? '',
            $contact['notes'] ?? ''
        ];
        $searchText = strtolower(implode(' ', $searchFields));
        return strpos($searchText, strtolower($searchTerm)) !== false;
    });
}

if (!empty($categoryFilter)) {
    $filteredContacts = array_filter($filteredContacts, function ($contact) use ($categoryFilter) {
        return ($contact['category'] ?? '') === $categoryFilter;
    });
}

if (!empty($priorityFilter)) {
    $filteredContacts = array_filter($filteredContacts, function ($contact) use ($priorityFilter) {
        return ($contact['priority'] ?? '') === $priorityFilter;
    });
}

// Sorting
$sortBy = $_GET['sort'] ?? 'name';
$sortOrder = $_GET['order'] ?? 'asc';

usort($filteredContacts, function ($a, $b) use ($sortBy, $sortOrder) {
    $valA = $a[$sortBy] ?? '';
    $valB = $b[$sortBy] ?? '';

    if ($sortBy === 'date_created') {
        $valA = strtotime($valA);
        $valB = strtotime($valB);
    }

    $result = strcasecmp($valA, $valB);
    return $sortOrder === 'desc' ? -$result : $result;
});

// Statistik
$stats = getContactStats($contacts);

// Pagination
$page = max(1, $_GET['page'] ?? 1);
$perPage = 10;
$totalContacts = count($filteredContacts);
$totalPages = ceil($totalContacts / $perPage);
$offset = ($page - 1) * $perPage;
$paginatedContacts = array_slice($filteredContacts, $offset, $perPage);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Kontak - Sistem Manajemen Kontak</title>
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
                <li><a href="view-contacts.php" class="active"><i class="fas fa-list"></i> Lihat Kontak</a></li>
                <li><a href="about.html"><i class="fas fa-info-circle"></i> Tentang</a></li>
            </ul>
        </nav>
    </header>

    <!-- Table Container -->
    <div class="table-container">
        <h1><i class="fas fa-list"></i> Daftar Kontak</h1>

        <!-- Statistik -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: #4CAF50; color: white; padding: 1rem; border-radius: 10px; text-align: center;">
                <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Kontak</p>
            </div>
            <div class="stat-card" style="background: #2196F3; color: white; padding: 1rem; border-radius: 10px; text-align: center;">
                <i class="fas fa-clock" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h3><?php echo $stats['recent']; ?></h3>
                <p>Kontak Baru (7 Hari)</p>
            </div>
            <div class="stat-card" style="background: #FF9800; color: white; padding: 1rem; border-radius: 10px; text-align: center;">
                <i class="fas fa-tags" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h3><?php echo count($stats['categories']); ?></h3>
                <p>Kategori</p>
            </div>
            <div class="stat-card" style="background: #9C27B0; color: white; padding: 1rem; border-radius: 10px; text-align: center;">
                <i class="fas fa-filter" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h3><?php echo count($filteredContacts); ?></h3>
                <p>Hasil Filter</p>
            </div>
        </div>

        <!-- Filter dan Pencarian -->
        <form method="GET" style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label for="search"><i class="fas fa-search"></i> Cari Kontak</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>"
                        placeholder="Nama, email, telepon, atau catatan...">
                </div>
                <div>
                    <label for="category"><i class="fas fa-tags"></i> Kategori</label>
                    <select id="category" name="category">
                        <option value="">Semua Kategori</option>
                        <?php foreach (array_keys($stats['categories']) as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"
                                <?php echo $cat === $categoryFilter ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?> (<?php echo $stats['categories'][$cat]; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="priority"><i class="fas fa-star"></i> Prioritas</label>
                    <select id="priority" name="priority">
                        <option value="">Semua Prioritas</option>
                        <?php foreach (array_keys($stats['priorities']) as $prio): ?>
                            <?php if ($prio !== 'Tidak ada'): ?>
                                <option value="<?php echo htmlspecialchars($prio); ?>"
                                    <?php echo $prio === $priorityFilter ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prio); ?> (<?php echo $stats['priorities'][$prio]; ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <a href="view-contacts.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Reset Filter
                </a>
                <a href="export.php" class="btn btn-secondary" style="margin-left: 1rem;">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </form>

        <?php if (empty($contacts)): ?>
            <!-- Jika tidak ada kontak -->
            <div style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 10px;">
                <i class="fas fa-address-book" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                <h2 style="color: #666;">Belum Ada Kontak</h2>
                <p style="color: #888; margin-bottom: 2rem;">Mulai dengan menambahkan kontak pertama Anda!</p>
                <a href="add-contact.html" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kontak Pertama
                </a>
            </div>
        <?php elseif (empty($filteredContacts)): ?>
            <!-- Jika tidak ada hasil filter -->
            <div style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 10px;">
                <i class="fas fa-search" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                <h2 style="color: #666;">Tidak Ada Hasil</h2>
                <p style="color: #888;">Tidak ditemukan kontak yang sesuai dengan filter Anda.</p>
            </div>
        <?php else: ?>
            <!-- Sorting controls -->
            <div style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <span style="font-weight: 600;">Urutkan berdasarkan:</span>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name', 'order' => 'asc'])); ?>"
                    class="btn btn-secondary <?php echo $sortBy === 'name' ? 'active' : ''; ?>">
                    Nama A-Z
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name', 'order' => 'desc'])); ?>"
                    class="btn btn-secondary <?php echo $sortBy === 'name' && $sortOrder === 'desc' ? 'active' : ''; ?>">
                    Nama Z-A
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'date_created', 'order' => 'desc'])); ?>"
                    class="btn btn-secondary <?php echo $sortBy === 'date_created' ? 'active' : ''; ?>">
                    Terbaru
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'category', 'order' => 'asc'])); ?>"
                    class="btn btn-secondary <?php echo $sortBy === 'category' ? 'active' : ''; ?>">
                    Kategori
                </a>
            </div>

            <!-- Tabel Kontak -->
            <div style="overflow-x: auto;">
                <table class="contacts-table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Kategori</th>
                            <th>Prioritas</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paginatedContacts as $contact): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php if (!empty($contact['photo']) && file_exists($contact['photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($contact['photo']); ?>"
                                            alt="Foto" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #4CAF50; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                            <?php echo strtoupper(substr($contact['name'] ?? 'N', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($contact['name'] ?? 'N/A'); ?></strong>
                                    <?php if (!empty($contact['notes'])): ?>
                                        <br><small style="color: #666;">
                                            <?php echo htmlspecialchars(substr($contact['notes'], 0, 50)); ?>
                                            <?php echo strlen($contact['notes']) > 50 ? '...' : ''; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($contact['email'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($contact['email'] ?? 'N/A'); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($contact['phone'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($contact['phone'] ?? 'N/A'); ?>
                                    </a>
                                </td>
                                <td>
                                    <span style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.8rem;">
                                        <?php
                                        $category = $contact['category'] ?? 'Lainnya';
                                        $categoryIcons = [
                                            'Keluarga' => '👨‍👩‍👧‍👦',
                                            'Teman' => '👥',
                                            'Kerja' => '💼',
                                            'Bisnis' => '🏢',
                                            'Lainnya' => '📝'
                                        ];
                                        echo ($categoryIcons[$category] ?? '📝') . ' ' . htmlspecialchars($category);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $priority = $contact['priority'] ?? '';
                                    if (!empty($priority)) {
                                        $priorityColors = [
                                            'Tinggi' => '#e74c3c',
                                            'Sedang' => '#f39c12',
                                            'Rendah' => '#27ae60'
                                        ];
                                        $priorityIcons = [
                                            'Tinggi' => '🔴',
                                            'Sedang' => '🟡',
                                            'Rendah' => '🟢'
                                        ];
                                        $color = $priorityColors[$priority] ?? '#666';
                                        $icon = $priorityIcons[$priority] ?? '';
                                        echo "<span style='color: $color; font-weight: bold;'>$icon $priority</span>";
                                    } else {
                                        echo '<span style="color: #666;">-</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($contact['date_created'])) {
                                        $date = new DateTime($contact['date_created']);
                                        echo $date->format('d/m/Y H:i');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="contact-detail.php?id=<?php echo urlencode($contact['id'] ?? ''); ?>"
                                        class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="margin-top: 2rem; text-align: center;">
                    <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                                class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                                class="btn btn-secondary">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <p style="margin-top: 1rem; color: #666;">
                        Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?>
                        (<?php echo count($filteredContacts); ?> dari <?php echo $stats['total']; ?> kontak)
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div style="margin-top: 3rem; text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 10px;">
            <h3>Aksi Cepat</h3>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1rem;">
                <a href="add-contact.html" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kontak Baru
                </a>
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
        // Auto-refresh setiap 5 menit
        setTimeout(function() {
            location.reload();
        }, 300000);

        // Konfirmasi sebelum export
        document.querySelector('a[href="export.php"]')?.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda ingin mengexport semua data kontak ke file CSV?')) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>