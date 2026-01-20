<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'konsumen') {
    header('Location: ../index.php?error=Silahkan login sebagai konsumen');
    exit();
}

$user_id = $_SESSION['user_id'];

$query_konsumen = "SELECT k.* FROM konsumen k WHERE k.user_id = '$user_id'";
$result_konsumen = mysqli_query($koneksi, $query_konsumen);
$konsumen = mysqli_fetch_assoc($result_konsumen);
$konsumen_id = $konsumen['id'];

$query_riwayat = "SELECT t.*, p.bulan, p.tahun, p.meter_awal, p.meter_akhir
                    FROM tagihan t
                    JOIN pemakaian p ON t.pemakaian_id = p.id
                    WHERE p.konsumen_id = '$konsumen_id'
                    AND t.status = 'lunas'
                    ORDER BY t.tanggal_bayar DESC";
$result_riwayat = mysqli_query($koneksi, $query_riwayat);

$query_statistik = "SELECT 
                    COUNT(*) as total_bayar,
                    SUM(t.total_bayar) as total_pembayaran,
                    AVG(t.total_bayar) as rata_rata,
                    MIN(t.total_bayar) as terendah,
                    MAX(t.total_bayar) as tertinggi
                    FROM tagihan t
                    JOIN pemakaian p ON t.pemakaian_id = p.id
                    WHERE p.konsumen_id = '$konsumen_id'
                    AND t.status = 'lunas'";
$result_statistik = mysqli_query($koneksi, $query_statistik);
$statistik = mysqli_fetch_assoc($result_statistik);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembayaran - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar_konsumen.php'; ?>

        <div class="main-content">
            <header class="header">
                <h1><i class="fas fa-history"></i> Riwayat Pembayaran</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                </div>
            </header>

            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #4CAF50;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Pembayaran</h3>
                            <p class="stat-number"><?php echo number_format($statistik['total_bayar']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #2196F3;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Uang</h3>
                            <p class="stat-number">Rp <?php echo number_format($statistik['total_pembayaran'] ?? 0, 0, ',', '.'); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #FF9800;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rata-rata</h3>
                            <p class="stat-number">Rp <?php echo number_format($statistik['rata_rata'] ?? 0, 0, ',', '.'); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #9C27B0;">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Tertinggi</h3>
                            <p class="stat-number">Rp <?php echo number_format($statistik['tertinggi'] ?? 0, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-receipt"></i> Riwayat Pembayaran Tagihan</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Periode</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Pemakaian</th>
                                    <th>Total Tagihan</th>
                                    <th>Status</th>
                                    <th>Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_riwayat) > 0): ?>
                                    <?php 
                                    $no = 1; 
                                    mysqli_data_seek($result_riwayat, 0);
                                    while ($row = mysqli_fetch_assoc($result_riwayat)): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['bulan'] . ' ' . $row['tahun']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_bayar'])); ?></td>
                                        <td><?php echo number_format($row['total_pemakaian']); ?> kWh</td>
                                        <td>
                                            <strong>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="status-badge lunas">
                                                <i class="fas fa-check"></i> Lunas
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-action" onclick="cetakBukti(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-print"></i> Cetak
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="no-data">Belum ada riwayat pembayaran</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-chart-area"></i> Grafik Pemakaian Listrik</h2>
                    <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-download"></i> Ekspor Data</h2>
                    <div class="export-options">
                        <button class="btn-export" onclick="cetakRiwayat()">
                            <i class="fas fa-print"></i> Cetak Riwayat
                        </button>
                        <button class="btn-export" onclick="unduhPDF()">
                            <i class="fas fa-file-pdf"></i> Unduh PDF
                        </button>
                        <button class="btn-export" onclick="unduhExcel()">
                            <i class="fas fa-file-excel"></i> Unduh Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script>

    const chartData = [
        <?php
        mysqli_data_seek($result_riwayat, 0);
        while ($row = mysqli_fetch_assoc($result_riwayat)):
            $periode = $row['bulan'] . ' ' . $row['tahun'];
        ?>
        {
            label: "<?php echo $periode; ?>",
            y: <?php echo $row['total_pemakaian']; ?>,
            tagihan: <?php echo $row['total_bayar']; ?>
        },
        <?php endwhile; ?>
    ];

    window.onload = function() {
        if (chartData.length > 0) {
            const chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Pemakaian Listrik (kWh)"
                },
                axisY: {
                    title: "Pemakaian (kWh)",
                    includeZero: true
                },
                data: [{
                    type: "column",
                    dataPoints: chartData,
                    click: function(e) {
                        alert("Pemakaian: " + e.dataPoint.y + " kWh\nTagihan: Rp " + 
                                e.dataPoint.tagihan.toLocaleString('id-ID'));
                    }
                }]
            });
            chart.render();
        }
    };

    function cetakBukti(id) {
        window.open('cetak_bukti.php?id=' + id, '_blank');
    }

    function cetakRiwayat() {
        window.open('cetak_riwayat.php', '_blank');
    }

    function unduhPDF() {
        alert('Fitur unduh PDF akan tersedia dalam versi selanjutnya');
    }

    function unduhExcel() {
        alert('Fitur unduh Excel akan tersedia dalam versi selanjutnya');
    }
    </script>

    <style>
    .export-options {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    .btn-export {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: transform 0.3s;
    }
    
    .btn-export:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #1976D2 0%, #1565C0 100%);
    }
    
    .btn-export i {
        font-size: 18px;
    }
    
    @media print {
        .sidebar, .header-actions, .btn-export {
            display: none !important;
        }
        
        .main-content {
            margin-left: 0 !important;
        }
    }
    </style>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>