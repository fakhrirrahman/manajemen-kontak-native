<?php
// view-contacts-simple.php - Menampilkan data text murni

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk membaca data dari file JSON
function readContactsFromJSON()
{
    $contacts = [];
    $jsonFile = 'data/contacts.txt';

    if (file_exists($jsonFile)) {
        $content = file_get_contents($jsonFile);
        $separator = str_repeat('=', 50);
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

// Fungsi untuk membaca data dari file plain text
function readContactsFromPlainText()
{
    $plainFile = 'data/contacts_plain.txt';
    if (file_exists($plainFile)) {
        return file_get_contents($plainFile);
    }
    return '';
}

// Fungsi untuk membaca data dari file CSV
function readContactsFromCSV()
{
    $csvFile = 'data/contacts.csv';
    if (file_exists($csvFile)) {
        return file_get_contents($csvFile);
    }
    return '';
}

// Baca data
$contacts = readContactsFromJSON();
$plainTextData = readContactsFromPlainText();
$csvData = readContactsFromCSV();

// Hitung statistik
$totalContacts = count($contacts);
$categories = [];
foreach ($contacts as $contact) {
    $cat = $contact['category'] ?? 'Lainnya';
    $categories[$cat] = ($categories[$cat] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Data Text Murni - Sistem Manajemen Kontak</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="no-animations">
    <!-- Header dengan Navigasi -->
    <header>
        <nav class="navbar">
            <div class="nav-brand">
                <i class="fas fa-address-book"></i>
                <span>Kontak Manager</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.html"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="add-contact-simple.html"><i class="fas fa-plus"></i> Form Sederhana</a></li>
                <li><a href="view-contacts-simple.php" class="active"><i class="fas fa-list"></i> Lihat Data Text</a></li>
                <li><a href="test-save.php"><i class="fas fa-flask"></i> Test System</a></li>
            </ul>
        </nav>
    </header>

    <!-- Container -->
    <div class="table-container">
        <h1><i class="fas fa-file-alt"></i> Data Text Murni yang Tersimpan</h1>

        <!-- Statistik -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: #4CAF50; color: white; padding: 1rem; border-radius: 10px; text-align: center;">
                <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h3><?php echo $totalContacts; ?></h3>
                <p>Total Kontak</p>
            </div>
            <div class="stat-card" style="background: #2196F3; color: white; padding: 1rem; border-radius: 10px; text-align: center;">
                <i class="fas fa-tags" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h3><?php echo count($categories); ?></h3>
                <p>Kategori</p>
            </div>
            <div class="stat-card" style="background: #FF9800; color: white; padding: 1rem; border-radius: 10px; text-align: center;">
                <i class="fas fa-file" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h3>3</h3>
                <p>Format File</p>
            </div>
            <div class="stat-card" style="background: #9C27B0; color: white; padding: 1rem; border-radius: 10px; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h3>Text</h3>
                <p>Format Murni</p>
            </div>
        </div>

        <?php if ($totalContacts === 0): ?>
            <!-- Jika tidak ada data -->
            <div style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 10px;">
                <i class="fas fa-folder-open" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                <h2 style="color: #666;">Belum Ada Data Text</h2>
                <p style="color: #888; margin-bottom: 2rem;">Mulai dengan menambahkan data melalui form sederhana!</p>
                <a href="add-contact-simple.html" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Data Pertama
                </a>
                <a href="test-save.php" class="btn btn-secondary" style="margin-left: 1rem;">
                    <i class="fas fa-flask"></i> Test System
                </a>
            </div>
        <?php else: ?>

            <!-- Tab Navigation -->
            <div style="margin-bottom: 2rem;">
                <div style="border-bottom: 2px solid #ddd; margin-bottom: 1rem;">
                    <button class="tab-btn active" onclick="showTab('json')" style="padding: 1rem 2rem; background: #4CAF50; color: white; border: none; border-radius: 5px 5px 0 0; margin-right: 0.5rem; cursor: pointer;">
                        <i class="fas fa-file-code"></i> Format JSON
                    </button>
                    <button class="tab-btn" onclick="showTab('plain')" style="padding: 1rem 2rem; background: #f5f5f5; color: #333; border: none; border-radius: 5px 5px 0 0; margin-right: 0.5rem; cursor: pointer;">
                        <i class="fas fa-file-alt"></i> Format Plain Text
                    </button>
                    <button class="tab-btn" onclick="showTab('csv')" style="padding: 1rem 2rem; background: #f5f5f5; color: #333; border: none; border-radius: 5px 5px 0 0; margin-right: 0.5rem; cursor: pointer;">
                        <i class="fas fa-file-csv"></i> Format CSV
                    </button>
                    <button class="tab-btn" onclick="showTab('table')" style="padding: 1rem 2rem; background: #f5f5f5; color: #333; border: none; border-radius: 5px 5px 0 0; cursor: pointer;">
                        <i class="fas fa-table"></i> Tabel
                    </button>
                </div>
            </div>

            <!-- Tab Content: JSON -->
            <div id="json-content" class="tab-content" style="display: block;">
                <h3><i class="fas fa-file-code"></i> Data JSON (data/contacts.txt)</h3>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border: 1px solid #ddd;">
                    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
                        <span style="font-weight: bold;">File: data/contacts.txt</span>
                        <button onclick="copyToClipboard('json-data')" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <pre id="json-data" style="background: white; padding: 1rem; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 0.9rem;"><?php
                                                                                                                                                                if (file_exists('data/contacts.txt')) {
                                                                                                                                                                    echo htmlspecialchars(file_get_contents('data/contacts.txt'));
                                                                                                                                                                } else {
                                                                                                                                                                    echo "File contacts.txt belum ada";
                                                                                                                                                                }
                                                                                                                                                                ?></pre>
                </div>
            </div>

            <!-- Tab Content: Plain Text -->
            <div id="plain-content" class="tab-content" style="display: none;">
                <h3><i class="fas fa-file-alt"></i> Data Plain Text (data/contacts_plain.txt)</h3>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border: 1px solid #ddd;">
                    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
                        <span style="font-weight: bold;">File: data/contacts_plain.txt</span>
                        <button onclick="copyToClipboard('plain-data')" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <pre id="plain-data" style="background: white; padding: 1rem; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 0.9rem;"><?php
                                                                                                                                                                echo htmlspecialchars($plainTextData ?: "File contacts_plain.txt belum ada");
                                                                                                                                                                ?></pre>
                </div>
            </div>

            <!-- Tab Content: CSV -->
            <div id="csv-content" class="tab-content" style="display: none;">
                <h3><i class="fas fa-file-csv"></i> Data CSV (data/contacts.csv)</h3>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border: 1px solid #ddd;">
                    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
                        <span style="font-weight: bold;">File: data/contacts.csv</span>
                        <button onclick="copyToClipboard('csv-data')" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <pre id="csv-data" style="background: white; padding: 1rem; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 0.9rem;"><?php
                                                                                                                                                                echo htmlspecialchars($csvData ?: "File contacts.csv belum ada");
                                                                                                                                                                ?></pre>
                </div>
            </div>

            <!-- Tab Content: Table -->
            <div id="table-content" class="tab-content" style="display: none;">
                <h3><i class="fas fa-table"></i> Data dalam Bentuk Tabel</h3>
                <div style="overflow-x: auto;">
                    <table class="contacts-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Kategori</th>
                                <th>Prioritas</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars(substr($contact['id'] ?? '', -8)); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($contact['name'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($contact['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($contact['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($contact['category'] ?? 'Lainnya'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($contact['priority'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($contact['date_created'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- File Info -->
            <div style="background: #e8f5e8; padding: 2rem; border-radius: 10px; margin-top: 2rem; border-left: 4px solid #4CAF50;">
                <h3><i class="fas fa-info-circle"></i> Informasi File</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div>
                        <h4><i class="fas fa-file-code"></i> contacts.txt</h4>
                        <p><strong>Format:</strong> JSON</p>
                        <p><strong>Ukuran:</strong> <?php echo file_exists('data/contacts.txt') ? number_format(filesize('data/contacts.txt')) . ' bytes' : 'File tidak ada'; ?></p>
                    </div>
                    <div>
                        <h4><i class="fas fa-file-alt"></i> contacts_plain.txt</h4>
                        <p><strong>Format:</strong> Plain Text</p>
                        <p><strong>Ukuran:</strong> <?php echo file_exists('data/contacts_plain.txt') ? number_format(filesize('data/contacts_plain.txt')) . ' bytes' : 'File tidak ada'; ?></p>
                    </div>
                    <div>
                        <h4><i class="fas fa-file-csv"></i> contacts.csv</h4>
                        <p><strong>Format:</strong> CSV</p>
                        <p><strong>Ukuran:</strong> <?php echo file_exists('data/contacts.csv') ? number_format(filesize('data/contacts.csv')) . ' bytes' : 'File tidak ada'; ?></p>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <!-- Quick Actions -->
        <div style="margin-top: 3rem; text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 10px;">
            <h3>Aksi Cepat</h3>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1rem;">
                <a href="add-contact-simple.html" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Data Baru
                </a>
                <a href="test-save.php" class="btn btn-secondary">
                    <i class="fas fa-flask"></i> Test System
                </a>
                <a href="index.html" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <?php if ($totalContacts > 0): ?>
                    <button onclick="downloadFile('data/contacts.txt', 'contacts.txt')" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Download JSON
                    </button>
                    <button onclick="downloadFile('data/contacts.csv', 'contacts.csv')" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Download CSV
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Sistem Manajemen Kontak - <strong>Data Text Murni</strong></p>
        </div>
    </footer>

    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.style.display = 'none');

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => {
                btn.style.background = '#f5f5f5';
                btn.style.color = '#333';
            });

            // Show selected tab
            document.getElementById(tabName + '-content').style.display = 'block';

            // Set active button
            event.target.style.background = '#4CAF50';
            event.target.style.color = 'white';
        }

        // Copy to clipboard
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;

            navigator.clipboard.writeText(text).then(function() {
                alert('Data berhasil disalin ke clipboard!');
            }).catch(function() {
                // Fallback untuk browser lama
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Data berhasil disalin ke clipboard!');
            });
        }

        // Download file
        function downloadFile(filepath, filename) {
            const link = document.createElement('a');
            link.href = filepath;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Auto refresh setiap 30 detik
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>

</html>