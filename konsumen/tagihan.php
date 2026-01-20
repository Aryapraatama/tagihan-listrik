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

if (isset($_GET['bayar'])) {
    $tagihan_id = clean_input($_GET['bayar']);
    $tanggal_bayar = date('Y-m-d');
    
    $query = "UPDATE tagihan SET status = 'lunas', tanggal_bayar = '$tanggal_bayar' 
              WHERE id = '$tagihan_id' AND pemakaian_id IN (
                  SELECT id FROM pemakaian WHERE konsumen_id = '$konsumen_id'
              )";
    
    if (mysqli_query($koneksi, $query)) {
        header('Location: tagihan.php?success=Tagihan berhasil dibayar');
        exit();
    } else {
        header('Location: tagihan.php?error=Gagal melakukan pembayaran');
        exit();
    }
}

$query_tagihan = "SELECT t.*, p.bulan, p.tahun, p.meter_awal, p.meter_akhir
                    FROM tagihan t
                    JOIN pemakaian p ON t.pemakaian_id = p.id
                    WHERE p.konsumen_id = '$konsumen_id'
                    ORDER BY p.tahun DESC, 
                    FIELD(p.bulan, 'Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember') DESC";
$result_tagihan = mysqli_query($koneksi, $query_tagihan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar_konsumen.php'; ?>

        <div class="main-content">
            <header class="header">
                <h1><i class="fas fa-file-invoice"></i> Tagihan Listrik</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                </div>
            </header>

            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #2196F3;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Tagihan</h3>
                            <?php
                            $query_total = "SELECT COUNT(*) as total FROM tagihan t 
                                            JOIN pemakaian p ON t.pemakaian_id = p.id 
                                            WHERE p.konsumen_id = '$konsumen_id'";
                            $result_total = mysqli_query($koneksi, $query_total);
                            $data_total = mysqli_fetch_assoc($result_total);
                            ?>
                            <p class="stat-number"><?php echo number_format($data_total['total']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #FF9800;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Belum Bayar</h3>
                            <?php
                            $query_belum = "SELECT COUNT(*) as total FROM tagihan t 
                                            JOIN pemakaian p ON t.pemakaian_id = p.id 
                                            WHERE p.konsumen_id = '$konsumen_id' 
                                            AND t.status = 'belum_bayar'";
                            $result_belum = mysqli_query($koneksi, $query_belum);
                            $data_belum = mysqli_fetch_assoc($result_belum);
                            ?>
                            <p class="stat-number"><?php echo number_format($data_belum['total']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #4CAF50;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Lunas</h3>
                            <?php
                            $query_lunas = "SELECT COUNT(*) as total FROM tagihan t 
                                            JOIN pemakaian p ON t.pemakaian_id = p.id 
                                            WHERE p.konsumen_id = '$konsumen_id' 
                                            AND t.status = 'lunas'";
                            $result_lunas = mysqli_query($koneksi, $query_lunas);
                            $data_lunas = mysqli_fetch_assoc($result_lunas);
                            ?>
                            <p class="stat-number"><?php echo number_format($data_lunas['total']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #9C27B0;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Dibayar</h3>
                            <?php
                            $query_bayar = "SELECT SUM(t.total_bayar) as total FROM tagihan t 
                                            JOIN pemakaian p ON t.pemakaian_id = p.id 
                                            WHERE p.konsumen_id = '$konsumen_id' 
                                            AND t.status = 'lunas'";
                            $result_bayar = mysqli_query($koneksi, $query_bayar);
                            $data_bayar = mysqli_fetch_assoc($result_bayar);
                            ?>
                            <p class="stat-number">Rp <?php echo number_format($data_bayar['total'] ?? 0, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-history"></i> Riwayat Tagihan</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Periode</th>
                                    <th>Meter Awal</th>
                                    <th>Meter Akhir</th>
                                    <th>Pemakaian</th>
                                    <th>Tarif/kWh</th>
                                    <th>Total Tagihan</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_tagihan) > 0): ?>
                                    <?php 
                                    $no = 1; 
                                    mysqli_data_seek($result_tagihan, 0);
                                    while ($row = mysqli_fetch_assoc($result_tagihan)): 
                                        $tarif_per_kwh = $konsumen['tarif_per_kwh'];
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['bulan'] . ' ' . $row['tahun']); ?></td>
                                        <td><?php echo number_format($row['meter_awal']); ?> kWh</td>
                                        <td><?php echo number_format($row['meter_akhir']); ?> kWh</td>
                                        <td><?php echo number_format($row['total_pemakaian']); ?> kWh</td>
                                        <td>Rp <?php echo number_format($tarif_per_kwh, 2, ',', '.'); ?></td>
                                        <td>
                                            <strong>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])); ?><br>
                                            <small class="<?php echo (strtotime($row['tanggal_jatuh_tempo']) < time() && $row['status'] == 'belum_bayar') ? 'text-danger' : ''; ?>">
                                                <?php 
                                                if ($row['status'] == 'belum_bayar') {
                                                    $diff = strtotime($row['tanggal_jatuh_tempo']) - time();
                                                    $days = ceil($diff / (60 * 60 * 24));
                                                    echo $days > 0 ? "$days hari lagi" : "Terlambat " . abs($days) . " hari";
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $row['status']; ?>">
                                                <?php echo $row['status'] == 'lunas' ? 'Lunas' : 'Belum Bayar'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'belum_bayar'): ?>
                                                <a href="?bayar=<?php echo $row['id']; ?>" 
                                                    class="btn-action btn-pay"
                                                    onclick="return confirm('Konfirmasi pembayaran tagihan Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?>?')">
                                                    <i class="fas fa-money-bill-wave"></i> Bayar
                                                </a>
                                            <?php else: ?>
                                                <span class="text-success">
                                                    <i class="fas fa-check"></i> Lunas
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="no-data">Belum ada data tagihan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-credit-card"></i> Cara Pembayaran</h2>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fas fa-bank"></i>
                            </div>
                            <div class="payment-info">
                                <h3>Transfer Bank</h3>
                                <p>Transfer ke rekening berikut:</p>
                                <ul>
                                    <li>Bank: BCA (Bank Central Asia)</li>
                                    <li>No. Rekening: 1234567890</li>
                                    <li>Atas Nama: PLN Digital</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="payment-info">
                                <h3>E-Wallet</h3>
                                <p>Pembayaran via e-wallet:</p>
                                <ul>
                                    <li>OVO: 081234567890</li>
                                    <li>DANA: 081234567890</li>
                                    <li>GoPay: 081234567890</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="payment-info">
                                <h3>Pembayaran Tunai</h3>
                                <p>Bayar langsung di:</p>
                                <ul>
                                    <li>Kantor PLN Terdekat</li>
                                    <li>Alfamart / Indomaret</li>
                                    <li>Kantor Kelurahan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .payment-method {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        display: flex;
        gap: 15px;
        align-items: flex-start;
    }
    
    .payment-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }
    
    .payment-info h3 {
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .payment-info p {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 14px;
    }
    
    .payment-info ul {
        margin: 0;
        padding-left: 20px;
        font-size: 14px;
        color: #666;
    }
    
    .payment-info li {
        margin-bottom: 5px;
    }
    
    .btn-pay {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-pay:hover {
        background: linear-gradient(135deg, #45a049 0%, #388E3C 100%);
    }
    
    .text-danger {
        color: #f44336 !important;
    }
    
    .text-success {
        color: #4CAF50 !important;
    }
    </style>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>