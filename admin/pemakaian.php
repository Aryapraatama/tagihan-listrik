<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php?error=Silahkan login sebagai admin');
    exit();
}

if (isset($_GET['hapus'])) {
    $pemakaian_id = clean_input($_GET['hapus']);
    
    mysqli_begin_transaction($koneksi);
    
    try {
        $query_tagihan = "DELETE FROM tagihan WHERE pemakaian_id = '$pemakaian_id'";
        mysqli_query($koneksi, $query_tagihan);
        
        $query_pemakaian = "DELETE FROM pemakaian WHERE id = '$pemakaian_id'";
        mysqli_query($koneksi, $query_pemakaian);
        
        mysqli_commit($koneksi);
        header('Location: pemakaian.php?success=Data pemakaian berhasil dihapus');
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header('Location: pemakaian.php?error=Gagal menghapus data pemakaian');
        exit();
    }
}

$query_pemakaian = "SELECT p.*, k.nomor_kwh, k.nama_pelanggan, k.daya,
                    (p.meter_akhir - p.meter_awal) as total_pemakaian,
                    t.total_bayar, t.status
                    FROM pemakaian p
                    JOIN konsumen k ON p.konsumen_id = k.id
                    LEFT JOIN tagihan t ON p.id = t.pemakaian_id
                    ORDER BY p.tahun DESC, 
                    FIELD(p.bulan, 'Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember') DESC";
$result_pemakaian = mysqli_query($koneksi, $query_pemakaian);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemakaian Listrik - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-action {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 5px;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .btn-delete:hover {
            background: #d32f2f;
        }
        
        .btn-tagihan {
            background: #4CAF50;
            color: white;
        }
        
        .btn-tagihan:hover {
            background: #45a049;
        }
        
        .info-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-warning {
            background: #fff3e0;
            color: #ef6c00;
        }
        
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        
        .filter-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .filter-group label {
            font-weight: 500;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .btn-filter {
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-filter:hover {
            background: #1976D2;
        }
        
        .btn-reset {
            background: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-reset:hover {
            background: #e0e0e0;
        }
        
        .stats-summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 150px;
        }
        
        .stat-item h4 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-unit {
            font-size: 14px;
            color: #666;
            margin-left: 5px;
        }
        
        @media (max-width: 768px) {
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-summary {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <header class="header">
                <h1><i class="fas fa-file-invoice"></i> Data Pemakaian Listrik</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                    <a href="tagihan.php" class="btn-add">
                        <i class="fas fa-plus"></i> Tambah Tagihan
                    </a>
                </div>
            </header>

            <div class="content">
                <div class="stats-summary">
                    <?php
                    $query_stats = "SELECT 
                                    COUNT(*) as total_pemakaian,
                                    SUM(meter_akhir - meter_awal) as total_kwh,
                                    AVG(meter_akhir - meter_awal) as rata_kwh,
                                    MAX(meter_akhir - meter_awal) as max_kwh,
                                    MIN(meter_akhir - meter_awal) as min_kwh
                                    FROM pemakaian";
                    $result_stats = mysqli_query($koneksi, $query_stats);
                    $stats = mysqli_fetch_assoc($result_stats);
                    ?>
                    <div class="stat-item">
                        <h4>Total Pemakaian</h4>
                        <div class="stat-value">
                            <?php echo number_format($stats['total_pemakaian']); ?>
                            <span class="stat-unit">data</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <h4>Total kWh</h4>
                        <div class="stat-value">
                            <?php echo number_format($stats['total_kwh']); ?>
                            <span class="stat-unit">kWh</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <h4>Rata-rata per Bulan</h4>
                        <div class="stat-value">
                            <?php echo number_format($stats['rata_kwh'], 1); ?>
                            <span class="stat-unit">kWh</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <h4>Tertinggi</h4>
                        <div class="stat-value">
                            <?php echo number_format($stats['max_kwh']); ?>
                            <span class="stat-unit">kWh</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2><i class="fas fa-history"></i> Riwayat Pemakaian</h2>
                        <div class="filter-section">
                            <div class="filter-group">
                                <label for="filter_bulan">Bulan:</label>
                                <select id="filter_bulan" onchange="filterData()">
                                    <option value="">Semua Bulan</option>
                                    <?php
                                    $bulan_array = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    foreach ($bulan_array as $bulan):
                                    ?>
                                    <option value="<?php echo $bulan; ?>"><?php echo $bulan; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="filter_tahun">Tahun:</label>
                                <input type="number" id="filter_tahun" min="2020" max="2030" 
                                        value="<?php echo date('Y'); ?>" onchange="filterData()">
                            </div>
                            
                            <div class="filter-group">
                                <label for="filter_konsumen">Konsumen:</label>
                                <select id="filter_konsumen" onchange="filterData()">
                                    <option value="">Semua Konsumen</option>
                                    <?php
                                    $query_all_konsumen = "SELECT id, nama_pelanggan FROM konsumen ORDER BY nama_pelanggan";
                                    $result_all_konsumen = mysqli_query($koneksi, $query_all_konsumen);
                                    if ($result_all_konsumen && mysqli_num_rows($result_all_konsumen) > 0):
                                        while ($kons = mysqli_fetch_assoc($result_all_konsumen)):
                                    ?>
                                    <option value="<?php echo $kons['id']; ?>">
                                        <?php echo htmlspecialchars($kons['nama_pelanggan']); ?>
                                    </option>
                                    <?php 
                                        endwhile;
                                    endif;
                                    ?>
                                </select>
                            </div>
                            
                            <a href="pemakaian.php" class="btn-reset">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="pemakaianTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Konsumen</th>
                                    <th>Periode</th>
                                    <th>Meter Awal</th>
                                    <th>Meter Akhir</th>
                                    <th>Pemakaian</th>
                                    <th>Total Tagihan</th>
                                    <th>Status</th>
                                    <th>Tanggal Catat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($result_pemakaian && mysqli_num_rows($result_pemakaian) > 0): 
                                    $no = 1; 
                                    mysqli_data_seek($result_pemakaian, 0);
                                    while ($row = mysqli_fetch_assoc($result_pemakaian)): 
                                        $total_pemakaian = $row['meter_akhir'] - $row['meter_awal'];
                                ?>
                                <tr data-bulan="<?php echo $row['bulan']; ?>" 
                                    data-tahun="<?php echo $row['tahun']; ?>"
                                    data-konsumen="<?php echo $row['konsumen_id']; ?>">
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['nama_pelanggan']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['nomor_kwh']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['bulan'] . ' ' . $row['tahun']); ?>
                                    </td>
                                    <td><?php echo number_format($row['meter_awal']); ?> kWh</td>
                                    <td><?php echo number_format($row['meter_akhir']); ?> kWh</td>
                                    <td>
                                        <?php echo number_format($total_pemakaian); ?> kWh
                                        <?php 
                                        if ($total_pemakaian > 500): ?>
                                            <span class="info-badge badge-danger">Sangat Tinggi</span>
                                        <?php elseif ($total_pemakaian > 300): ?>
                                            <span class="info-badge badge-warning">Tinggi</span>
                                        <?php elseif ($total_pemakaian < 100): ?>
                                            <span class="info-badge badge-success">Rendah</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['total_bayar']): ?>
                                            <strong>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status']): ?>
                                            <span class="status-badge <?php echo $row['status']; ?>">
                                                <?php echo $row['status'] == 'lunas' ? 'Lunas' : 'Belum Bayar'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_catat'])); ?></td>
                                    <td>
                                        <a href="tagihan.php?edit_pemakaian=<?php echo $row['id']; ?>" 
                                            class="btn-action btn-tagihan" title="Lihat/Edit Tagihan">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </a>
                                        <a href="?hapus=<?php echo $row['id']; ?>" 
                                            class="btn-action btn-delete" title="Hapus"
                                            onclick="return confirm('Hapus data pemakaian ini?\nTagihan terkait juga akan dihapus.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="no-data">Belum ada data pemakaian</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-info-circle"></i> Informasi</h2>
                    <div class="info-content">
                        <h4>Catatan Penting:</h4>
                        <ul>
                            <li>Data pemakaian dibuat <strong>otomatis</strong> saat menambahkan tagihan baru</li>
                            <li>Untuk menambah pemakaian baru, gunakan menu <strong>Tambah Tagihan</strong></li>
                            <li>Data pemakaian yang dihapus akan menghapus juga tagihan terkait</li>
                            <li>Pemakaian dihitung: <strong>Meter Akhir - Meter Awal</strong></li>
                            <li>Warna badge menunjukkan tingkat pemakaian:
                                <ul>
                                    <li><span class="info-badge badge-success">Rendah</span>: < 100 kWh</li>
                                    <li><span class="info-badge badge-warning">Tinggi</span>: 100-500 kWh</li>
                                    <li><span class="info-badge badge-danger">Sangat Tinggi</span>: > 500 kWh</li>
                                </ul>
                            </li>
                        </ul>
                        
                        <div class="alert" style="background: #fff3e0; border-left: 4px solid #ff9800;">
                            <h4><i class="fas fa-exclamation-triangle"></i> Perhatian</h4>
                            <p>Halaman ini hanya untuk melihat dan menghapus data pemakaian. 
                            Untuk menambah data baru, silakan gunakan halaman <strong>Tagihan</strong>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function filterData() {
        const filterBulan = document.getElementById('filter_bulan').value;
        const filterTahun = document.getElementById('filter_tahun').value;
        const filterKonsumen = document.getElementById('filter_konsumen').value;
        const rows = document.querySelectorAll('#pemakaianTable tbody tr');
        
        let visibleCount = 0;
        
        rows.forEach(row => {
            const bulan = row.getAttribute('data-bulan');
            const tahun = row.getAttribute('data-tahun');
            const konsumen = row.getAttribute('data-konsumen');
            
            let show = true;
            
            if (filterBulan && bulan !== filterBulan) {
                show = false;
            }
            
            if (filterTahun && tahun !== filterTahun) {
                show = false;
            }
            
            if (filterKonsumen && konsumen !== filterKonsumen) {
                show = false;
            }
            
            if (show) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        let counter = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                row.cells[0].textContent = counter++;
            }
        });
        
        const noDataRow = document.querySelector('.no-data');
        if (visibleCount === 0) {
            if (!noDataRow) {
                const tbody = document.querySelector('#pemakaianTable tbody');
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="10" class="no-data">Tidak ada data yang sesuai dengan filter</td>';
                tbody.appendChild(tr);
            }
        } else if (noDataRow && !noDataRow.closest('tr').classList.contains('no-data-permanent')) {
            noDataRow.closest('tr').remove();
        }
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert(urlParams.get('success'));
        
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    if (urlParams.has('error')) {
        alert('Error: ' + urlParams.get('error'));
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    function exportToExcel() {
        const table = document.getElementById('pemakaianTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        rows.forEach(row => {
            const rowData = [];
            const cells = row.querySelectorAll('td, th');
            
            cells.forEach(cell => {
                let text = cell.textContent || cell.innerText;
                text = text.replace(/\n/g, ' ').trim();
                
                rowData.push('"' + text.replace(/"/g, '""') + '"');
            });
            
            csv.push(rowData.join(','));
        });

        const csvString = csv.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (navigator.msSaveBlob) { 
            navigator.msSaveBlob(blob, 'data_pemakaian.csv');
        } else {
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', 'data_pemakaian.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const cardHeader = document.querySelector('.card-header');
        const exportBtn = document.createElement('button');
        exportBtn.className = 'btn-filter';
        exportBtn.innerHTML = '<i class="fas fa-file-export"></i> Export Excel';
        exportBtn.onclick = exportToExcel;
        exportBtn.style.marginLeft = '10px';
        
        cardHeader.querySelector('.filter-section').appendChild(exportBtn);
    });

    document.getElementById('filter_tahun').value = new Date().getFullYear();
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>