<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'konsumen') {
    header('Location: ../index.php?error=Silahkan login sebagai konsumen');
    exit();
}

$user_id = $_SESSION['user_id'];

$query_konsumen = "SELECT k.* FROM konsumen k WHERE k.user_id = '$user_id'";
$result_konsumen = mysqli_query($koneksi, $query_konsumen);

if (!$result_konsumen || mysqli_num_rows($result_konsumen) == 0) {
    session_destroy();
    header('Location: ../index.php?error=Data konsumen tidak ditemukan');
    exit();
}

$konsumen = mysqli_fetch_assoc($result_konsumen);
$konsumen_id = $konsumen['id'];

$query_tagihan = "SELECT t.*, p.bulan, p.tahun 
                FROM tagihan t 
                JOIN pemakaian p ON t.pemakaian_id = p.id 
                WHERE p.konsumen_id = '{$konsumen['id']}' 
                ORDER BY p.tahun DESC, 
                FIELD(p.bulan, 'Januari','Februari','Maret','April','Mei','Juni',
                'Juli','Agustus','September','Oktober','November','Desember') DESC 
                LIMIT 1";
$result_tagihan = mysqli_query($koneksi, $query_tagihan);

if (!$result_tagihan) {
    $tagihan_data = null;
} else {
    $tagihan_data = mysqli_num_rows($result_tagihan) > 0 ? mysqli_fetch_assoc($result_tagihan) : null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-bolt"></i> PLN Digital</h2>
                <p>Konsumen Panel</p>
            </div>
            <div class="sidebar-user">
                <i class="fas fa-user-circle"></i>
                <h3><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h3>
                <p>Konsumen</p>
            </div>
            <nav class="sidebar-nav">
                <a href="beranda.php" class="active">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <a href="tagihan.php">
                    <i class="fas fa-file-invoice"></i> Tagihan
                </a>
                <a href="riwayat.php">
                    <i class="fas fa-history"></i> Riwayat Pembayaran
                </a>
                <a href="feedback.php">
                    <i class="fas fa-comment"></i> Kirim Feedback
                </a>
                <a href="../auth/logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <div class="main-content">
            <header class="header">
                <h1>Beranda Konsumen</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                </div>
            </header>

            <div class="content">
                <div class="card">
                    <h2><i class="fas fa-user"></i> Informasi Pelanggan</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Nomor KWH</label>
                            <p><?php echo htmlspecialchars($konsumen['nomor_kwh']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Nama Pelanggan</label>
                            <p><?php echo htmlspecialchars($konsumen['nama_pelanggan']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Daya</label>
                            <p><?php echo number_format($konsumen['daya']); ?> Watt</p>
                        </div>
                        <div class="info-item">
                            <label>Tarif per kWh</label>
                            <p>Rp <?php echo number_format($konsumen['tarif_per_kwh'], 2, ',', '.'); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Alamat</label>
                            <p><?php echo htmlspecialchars($konsumen['alamat']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-clock"></i> Tagihan Terbaru</h2>
                    <?php if ($tagihan_data): ?>
                    <div class="tagihan-info">
                        <div class="tagihan-header">
                            <h3>Tagihan <?php echo htmlspecialchars($tagihan_data['bulan'] . ' ' . $tagihan_data['tahun']); ?></h3>
                            <span class="status-badge <?php echo $tagihan_data['status']; ?>">
                                <?php echo $tagihan_data['status'] == 'lunas' ? 'Lunas' : 'Belum Bayar'; ?>
                            </span>
                        </div>
                        
                        <div class="tagihan-details">
                            <div class="detail-item">
                                <span>Total Pemakaian</span>
                                <strong><?php echo number_format($tagihan_data['total_pemakaian']); ?> kWh</strong>
                            </div>
                            <div class="detail-item">
                                <span>Total Tagihan</span>
                                <strong class="total-tagihan">
                                    Rp <?php echo number_format($tagihan_data['total_bayar'], 0, ',', '.'); ?>
                                </strong>
                            </div>
                            <div class="detail-item">
                                <span>Jatuh Tempo</span>
                                <strong><?php echo date('d F Y', strtotime($tagihan_data['tanggal_jatuh_tempo'])); ?></strong>
                            </div>
                        </div>
                        
                        <?php if ($tagihan_data['status'] == 'belum_bayar'): ?>
                        <button class="btn-pay" onclick="location.href='tagihan.php'">
                            <i class="fas fa-money-bill-wave"></i> Bayar Sekarang
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <p class="no-data">Belum ada tagihan</p>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2><i class="fas fa-calculator"></i> Cara Perhitungan Tagihan</h2>
                    <div class="calculation-info">
                        <p>Tagihan listrik dihitung berdasarkan rumus:</p>
                        <div class="formula">
                            <strong>Total Tagihan = Pemakaian (kWh) × Tarif per kWh</strong>
                        </div>
                        <div class="example">
                            <h4>Contoh Perhitungan:</h4>
                            <p>Jika daya Anda <?php echo number_format($konsumen['daya']); ?> Watt dan tarif Rp <?php echo number_format($konsumen['tarif_per_kwh'], 2, ',', '.'); ?> per kWh:</p>
                            <ul>
                                <li>Pemakaian: 150 kWh</li>
                                <li>Perhitungan: 150 kWh × Rp <?php echo number_format($konsumen['tarif_per_kwh'], 2, ',', '.'); ?></li>
                                <li>Total Tagihan: <strong>Rp <?php echo number_format(150 * $konsumen['tarif_per_kwh'], 0, ',', '.'); ?></strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>