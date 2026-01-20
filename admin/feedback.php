<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php?error=Silahkan login sebagai admin');
    exit();
}

if (isset($_GET['dibaca'])) {
    $feedback_id = clean_input($_GET['dibaca']);
    $query = "UPDATE feedback SET status = 'dibaca' WHERE id = '$feedback_id'";
    
    if (mysqli_query($koneksi, $query)) {
        header('Location: feedback.php?success=Status feedback diperbarui');
        exit();
    } else {
        header('Location: feedback.php?error=Gagal memperbarui status');
        exit();
    }
}

if (isset($_GET['hapus'])) {
    $feedback_id = clean_input($_GET['hapus']);
    $query = "DELETE FROM feedback WHERE id = '$feedback_id'";
    
    if (mysqli_query($koneksi, $query)) {
        header('Location: feedback.php?success=Feedback berhasil dihapus');
        exit();
    } else {
        header('Location: feedback.php?error=Gagal menghapus feedback');
        exit();
    }
}

$query = "SELECT f.*, u.nama_lengkap, u.username 
            FROM feedback f
            JOIN users u ON f.user_id = u.id
            ORDER BY f.status ASC, f.tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <header class="header">
                <h1><i class="fas fa-comments"></i> Feedback Pelanggan</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                </div>
            </header>

            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #9C27B0;">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Feedback</h3>
                            <?php
                            $query_total = "SELECT COUNT(*) as total FROM feedback";
                            $result_total = mysqli_query($koneksi, $query_total);
                            $data_total = mysqli_fetch_assoc($result_total);
                            ?>
                            <p class="stat-number"><?php echo number_format($data_total['total']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #2196F3;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Belum Dibaca</h3>
                            <?php
                            $query_belum = "SELECT COUNT(*) as total FROM feedback WHERE status = 'belum_dibaca'";
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
                            <h3>Sudah Dibaca</h3>
                            <?php
                            $query_sudah = "SELECT COUNT(*) as total FROM feedback WHERE status = 'dibaca'";
                            $result_sudah = mysqli_query($koneksi, $query_sudah);
                            $data_sudah = mysqli_fetch_assoc($result_sudah);
                            ?>
                            <p class="stat-number"><?php echo number_format($data_sudah['total']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-inbox"></i> Kotak Masuk</h2>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="feedback-list">
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="feedback-item <?php echo $row['status']; ?>">
                                <div class="feedback-header">
                                    <div class="feedback-user">
                                        <i class="fas fa-user-circle"></i>
                                        <div>
                                            <h4><?php echo htmlspecialchars($row['nama_lengkap']); ?></h4>
                                            <small><?php echo htmlspecialchars($row['username']); ?></small>
                                        </div>
                                    </div>
                                    <div class="feedback-meta">
                                        <span class="feedback-date">
                                            <i class="far fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($row['tanggal'])); ?>
                                        </span>
                                        <span class="feedback-status <?php echo $row['status']; ?>">
                                            <i class="fas fa-<?php echo $row['status'] == 'belum_dibaca' ? 'envelope' : 'envelope-open'; ?>"></i>
                                            <?php echo $row['status'] == 'belum_dibaca' ? 'Baru' : 'Dibaca'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="feedback-content">
                                    <p><?php echo nl2br(htmlspecialchars($row['pesan'])); ?></p>
                                </div>
                                <div class="feedback-actions">
                                    <?php if ($row['status'] == 'belum_dibaca'): ?>
                                        <a href="?dibaca=<?php echo $row['id']; ?>" class="btn-action btn-success">
                                            <i class="fas fa-check"></i> Tandai Dibaca
                                        </a>
                                    <?php endif; ?>
                                    <a href="?hapus=<?php echo $row['id']; ?>" 
                                       class="btn-action btn-delete"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus feedback ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Belum ada feedback</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
    .feedback-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .feedback-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        border-left: 4px solid #ddd;
    }
    
    .feedback-item.belum_dibaca {
        background: #e3f2fd;
        border-left-color: #2196F3;
    }
    
    .feedback-item.dibaca {
        background: #f8f9fa;
        border-left-color: #4CAF50;
    }
    
    .feedback-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .feedback-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .feedback-user i {
        font-size: 32px;
        color: #666;
    }
    
    .feedback-user h4 {
        margin: 0;
        color: #333;
    }
    
    .feedback-user small {
        color: #666;
        font-size: 12px;
    }
    
    .feedback-meta {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .feedback-date {
        color: #666;
        font-size: 14px;
    }
    
    .feedback-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .feedback-status.belum_dibaca {
        background: #bbdefb;
        color: #1565c0;
    }
    
    .feedback-status.dibaca {
        background: #c8e6c9;
        color: #2e7d32;
    }
    
    .feedback-content {
        background: white;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        border: 1px solid #eee;
    }
    
    .feedback-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    
    .btn-action {
        padding: 8px 16px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s;
    }
    
    .btn-success {
        background: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
    
    .btn-success:hover {
        background: #45a049;
    }
    
    .btn-delete {
        background: #f44336;
        color: white;
        border: none;
        cursor: pointer;
    }
    
    .btn-delete:hover {
        background: #d32f2f;
    }
    </style>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>