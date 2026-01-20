<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php?error=Silahkan login sebagai admin');
    exit();
}

$admin_name = $_SESSION['nama_lengkap'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-bolt"></i> PLN Digital</h2>
                <p>Admin Panel</p>
            </div>
            <div class="sidebar-user">
                <i class="fas fa-user-circle"></i>
                <h3><?php echo htmlspecialchars($admin_name); ?></h3>
                <p>Administrator</p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="konsumen.php">
                    <i class="fas fa-users"></i> Data Konsumen
                </a>
                <a href="pemakaian.php">
                    <i class="fas fa-file-invoice"></i> Pemakaian Listrik
                </a>
                <a href="tagihan.php">
                    <i class="fas fa-money-bill-wave"></i> Tagihan
                </a>
                <a href="feedback.php">
                    <i class="fas fa-comments"></i> Feedback
                </a>
                <a href="../auth/logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <div class="main-content">
            <header class="header">
                <h1>Dashboard Admin</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                </div>
            </header>

            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #4CAF50;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Konsumen</h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM konsumen";
                            $result = mysqli_query($koneksi, $query);
                            $data = mysqli_fetch_assoc($result);
                            ?>
                            <p class="stat-number"><?php echo $data['total']; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #2196F3;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Tagihan Belum Bayar</h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM tagihan WHERE status = 'belum_bayar'";
                            $result = mysqli_query($koneksi, $query);
                            $data = mysqli_fetch_assoc($result);
                            ?>
                            <p class="stat-number"><?php echo $data['total']; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #FF9800;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Tagihan Lunas</h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM tagihan WHERE status = 'lunas'";
                            $result = mysqli_query($koneksi, $query);
                            $data = mysqli_fetch_assoc($result);
                            ?>
                            <p class="stat-number"><?php echo $data['total']; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #9C27B0;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Feedback Baru</h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM feedback WHERE status = 'belum_dibaca'";
                            $result = mysqli_query($koneksi, $query);
                            $data = mysqli_fetch_assoc($result);
                            ?>
                            <p class="stat-number"><?php echo $data['total']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-history"></i> Aktivitas Terbaru</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Konsumen</th>
                                    <th>Bulan</th>
                                    <th>Pemakaian</th>
                                    <th>Total Tagihan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT k.nama_pelanggan, p.bulan, p.tahun, 
                                            t.total_pemakaian, t.total_bayar, t.status
                                            FROM tagihan t
                                            JOIN pemakaian p ON t.pemakaian_id = p.id
                                            JOIN konsumen k ON p.konsumen_id = k.id
                                            ORDER BY p.tahun DESC, 
                                            FIELD(p.bulan, 'Januari','Februari','Maret','April','Mei','Juni',
                                            'Juli','Agustus','September','Oktober','November','Desember') DESC
                                            LIMIT 5";
                                $result = mysqli_query($koneksi, $query);
                                $no = 1;
                                
                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                    <td><?php echo htmlspecialchars($row['bulan'] . ' ' . $row['tahun']); ?></td>
                                    <td><?php echo number_format($row['total_pemakaian']); ?> kWh</td>
                                    <td>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $row['status']; ?>">
                                            <?php echo $row['status'] == 'lunas' ? 'Lunas' : 'Belum Bayar'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>