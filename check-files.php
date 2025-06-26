<?php
// check-files.php - Cek file data mentah yang tersimpan
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📁 Raw Data Files - Cek File Text Murni</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #343a40 0%, #495057 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .content {
            padding: 2rem;
        }

        .file-section {
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }

        .file-header {
            background: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .file-content {
            padding: 1rem;
            background: #fff;
            max-height: 400px;
            overflow-y: auto;
        }

        .file-stats {
            font-size: 0.9rem;
            color: #6c757d;
        }

        pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .btn {
            display: inline-block;
            background: #007bff;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 0.9rem;
            margin: 0 5px;
        }

        .btn:hover {
            background: #0056b3;
        }

        .status {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            font-weight: 500;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .navigation {
            background: #f8f9fa;
            padding: 1rem;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .empty-state h3 {
            margin-bottom: 1rem;
        }

        .file-actions {
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📁 Raw Data Files</h1>
            <p>Pemeriksaan File Text Murni yang Tersimpan</p>
        </div>

        <div class="content">
            <?php
            $dataDir = 'data';
            $files = [
                'contacts.txt' => 'Data Kontak Format JSON',
                'contacts_plain.txt' => 'Data Kontak Format Plain Text',
                'contacts.csv' => 'Data Kontak Format CSV',
                'activity.log' => 'Log Aktivitas Aplikasi'
            ];

            // Cek apakah folder data ada
            if (!is_dir($dataDir)) {
                echo "<div class='status status-warning'>";
                echo "⚠️ Folder 'data' belum ada. Silakan tambah kontak terlebih dahulu untuk membuat folder data.";
                echo "</div>";
                echo "<div class='file-actions'>";
                echo "<a href='add-contact-simple.html' class='btn'>Tambah Kontak Sekarang</a>";
                echo "</div>";
            } else {
                echo "<div class='status status-success'>";
                echo "✅ Folder 'data' ditemukan di: " . realpath($dataDir);
                echo "</div>";

                // Scan semua file di folder data
                $allFiles = scandir($dataDir);
                $dataFiles = array_filter($allFiles, function ($file) use ($dataDir) {
                    return $file !== '.' && $file !== '..' && is_file($dataDir . '/' . $file);
                });

                if (empty($dataFiles)) {
                    echo "<div class='empty-state'>";
                    echo "<h3>📂 Folder Data Kosong</h3>";
                    echo "<p>Belum ada data kontak yang tersimpan.</p>";
                    echo "<div class='file-actions'>";
                    echo "<a href='add-contact-simple.html' class='btn'>Tambah Kontak Pertama</a>";
                    echo "<a href='test-storage.php' class='btn'>Test Storage System</a>";
                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<p><strong>Ditemukan " . count($dataFiles) . " file data:</strong></p>";

                    foreach ($files as $filename => $description) {
                        $filepath = $dataDir . '/' . $filename;

                        echo "<div class='file-section'>";
                        echo "<div class='file-header'>";
                        echo "<h3>📄 $filename</h3>";

                        if (file_exists($filepath)) {
                            $filesize = filesize($filepath);
                            $modified = date('Y-m-d H:i:s', filemtime($filepath));
                            echo "<div class='file-stats'>";
                            echo "Size: " . formatBytes($filesize) . " | Modified: $modified";
                            echo "</div>";
                        }
                        echo "</div>";

                        echo "<div class='file-content'>";
                        if (file_exists($filepath)) {
                            echo "<p><strong>$description</strong></p>";

                            $content = file_get_contents($filepath);
                            if ($content === false) {
                                echo "<div class='status status-error'>❌ Tidak bisa membaca file</div>";
                            } elseif (empty(trim($content))) {
                                echo "<div class='status status-warning'>⚠️ File kosong</div>";
                            } else {
                                // Batasi output untuk file besar
                                if (strlen($content) > 5000) {
                                    $content = substr($content, 0, 5000) . "\n\n... (file terpotong, terlalu besar untuk ditampilkan seluruhnya)";
                                }

                                echo "<pre>" . htmlspecialchars($content) . "</pre>";

                                // Tambahan info untuk CSV
                                if ($filename === 'contacts.csv') {
                                    $lines = count(file($filepath));
                                    echo "<p><strong>Jumlah baris:</strong> $lines (termasuk header)</p>";
                                }
                            }
                        } else {
                            echo "<div class='status status-warning'>";
                            echo "⚠️ File belum ada. Tambah kontak untuk membuat file ini.";
                            echo "</div>";
                        }
                        echo "</div>";
                        echo "</div>";
                    }

                    // Tampilkan file lain yang mungkin ada
                    $otherFiles = array_diff($dataFiles, array_keys($files));
                    if (!empty($otherFiles)) {
                        echo "<div class='file-section'>";
                        echo "<div class='file-header'>";
                        echo "<h3>📄 File Lainnya</h3>";
                        echo "</div>";
                        echo "<div class='file-content'>";
                        echo "<ul>";
                        foreach ($otherFiles as $file) {
                            $filepath = $dataDir . '/' . $file;
                            $filesize = filesize($filepath);
                            $modified = date('Y-m-d H:i:s', filemtime($filepath));
                            echo "<li>$file (" . formatBytes($filesize) . ") - $modified</li>";
                        }
                        echo "</ul>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
            }

            function formatBytes($size, $precision = 2)
            {
                $units = array('B', 'KB', 'MB', 'GB');
                $base = log($size, 1024);
                return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
            }
            ?>

            <div style="background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 8px; padding: 1.5rem; margin: 2rem 0;">
                <h4 style="color: #0066cc; margin-bottom: 1rem;">💡 Tips Verifikasi:</h4>
                <ul style="color: #333; line-height: 1.8;">
                    <li><strong>contacts.txt</strong> - Berisi data JSON yang mudah dibaca program</li>
                    <li><strong>contacts_plain.txt</strong> - Berisi data text biasa yang mudah dibaca manusia</li>
                    <li><strong>contacts.csv</strong> - Bisa dibuka dengan Excel atau aplikasi spreadsheet</li>
                    <li><strong>activity.log</strong> - Mencatat semua aktivitas aplikasi</li>
                </ul>
            </div>
        </div>

        <div class="navigation">
            <a href="test-center.html" class="btn">🔙 Kembali ke Test Center</a>
            <a href="add-contact-simple.html" class="btn">➕ Tambah Kontak</a>
            <a href="view-contacts-simple.php" class="btn">📋 Lihat Data</a>
            <a href="index.html" class="btn">🏠 Beranda</a>
        </div>
    </div>
</body>

</html>