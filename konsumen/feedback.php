<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'konsumen') {
    header('Location: ../index.php?error=Silahkan login sebagai konsumen');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kirim_feedback'])) {
    $pesan = clean_input($_POST['pesan']);
    $tanggal = date('Y-m-d');
    
    $query = "INSERT INTO feedback (user_id, pesan, tanggal) 
                VALUES ('$user_id', '$pesan', '$tanggal')";
    
    if (mysqli_query($koneksi, $query)) {
        header('Location: feedback.php?success=Feedback berhasil dikirim');
        exit();
    } else {
        header('Location: feedback.php?error=Gagal mengirim feedback');
        exit();
    }
}

$query_feedback = "SELECT * FROM feedback 
                    WHERE user_id = '$user_id' 
                    ORDER BY tanggal DESC";
$result_feedback = mysqli_query($koneksi, $query_feedback);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Feedback - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar_konsumen.php'; ?>

        <div class="main-content">
            <header class="header">
                <h1><i class="fas fa-comment"></i> Kirim Feedback</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                </div>
            </header>

            <div class="content">
                <div class="card">
                    <h2><i class="fas fa-paper-plane"></i> Kirim Feedback Baru</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="pesan">Pesan Feedback</label>
                            <textarea id="pesan" name="pesan" rows="6" required 
                                        placeholder="Masukkan saran, kritik, atau keluhan Anda..."></textarea>
                        </div>
                        
                        <div class="form-tips">
                            <h4><i class="fas fa-lightbulb"></i> Tips Menulis Feedback yang Baik:</h4>
                            <ul>
                                <li>Sebutkan tanggal/periode yang berkaitan</li>
                                <li>Jelaskan masalah dengan jelas dan spesifik</li>
                                <li>Sertakan saran perbaikan jika ada</li>
                                <li>Gunakan bahasa yang sopan dan konstruktif</li>
                            </ul>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="kirim_feedback" class="btn-send">
                                <i class="fas fa-paper-plane"></i> Kirim Feedback
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <h2><i class="fas fa-history"></i> Riwayat Feedback Anda</h2>
                    <?php if (mysqli_num_rows($result_feedback) > 0): ?>
                        <div class="feedback-history">
                            <?php while ($row = mysqli_fetch_assoc($result_feedback)): ?>
                            <div class="history-item">
                                <div class="history-header">
                                    <span class="history-date">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('d F Y', strtotime($row['tanggal'])); ?>
                                    </span>
                                    <span class="history-status <?php echo $row['status']; ?>">
                                        <i class="fas fa-<?php echo $row['status'] == 'belum_dibaca' ? 'envelope' : 'envelope-open'; ?>"></i>
                                        <?php echo $row['status'] == 'belum_dibaca' ? 'Belum Dibaca' : 'Sudah Dibaca'; ?>
                                    </span>
                                </div>
                                <div class="history-content">
                                    <p><?php echo nl2br(htmlspecialchars($row['pesan'])); ?></p>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Belum ada riwayat feedback</p>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2><i class="fas fa-headset"></i> Kontak Layanan Pelanggan</h2>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Telepon</h3>
                                <p>123 (Bebas Pulsa)</p>
                                <p>1500169 (PLN Care)</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Email</h3>
                                <p>pln123@pln.co.id</p>
                                <p>plndigital@pln.co.id</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Jam Operasional</h3>
                                <p>Senin - Jumat: 08.00 - 16.00 WIB</p>
                                <p>Sabtu: 08.00 - 12.00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .form-tips {
        background: #fff8e1;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
        border-left: 4px solid #ffc107;
    }
    
    .form-tips h4 {
        margin: 0 0 10px 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-tips ul {
        margin: 0;
        padding-left: 20px;
        color: #666;
    }
    
    .form-tips li {
        margin-bottom: 5px;
    }
    
    .btn-send {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: transform 0.3s;
    }
    
    .btn-send:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #1976D2 0%, #1565C0 100%);
    }
    
    .feedback-history {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .history-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border-left: 4px solid #2196F3;
    }
    
    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .history-date {
        color: #666;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .history-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .history-status.belum_dibaca {
        background: #bbdefb;
        color: #1565c0;
    }
    
    .history-status.dibaca {
        background: #c8e6c9;
        color: #2e7d32;
    }
    
    .history-content {
        color: #333;
        line-height: 1.6;
    }
    
    .contact-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .contact-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        display: flex;
        gap: 15px;
        align-items: flex-start;
    }
    
    .contact-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
    }
    
    .contact-details h3 {
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .contact-details p {
        margin: 0 0 5px 0;
        color: #666;
        font-size: 14px;
    }
    
    textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        font-family: inherit;
        resize: vertical;
    }
    
    textarea:focus {
        outline: none;
        border-color: #4CAF50;
    }
    </style>
    
    <script>
    const textarea = document.getElementById('pesan');
    const charCounter = document.createElement('div');
    charCounter.className = 'char-counter';
    charCounter.style.cssText = `
        text-align: right;
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    `;
    textarea.parentNode.appendChild(charCounter);
    
    function updateCharCounter() {
        const length = textarea.value.length;
        charCounter.textContent = `${length}/2000 karakter`;
        
        if (length > 2000) {
            charCounter.style.color = '#f44336';
            textarea.style.borderColor = '#f44336';
        } else if (length > 1500) {
            charCounter.style.color = '#ff9800';
            textarea.style.borderColor = '#ff9800';
        } else {
            charCounter.style.color = '#666';
            textarea.style.borderColor = '#ddd';
        }
    }
    
    textarea.addEventListener('input', updateCharCounter);
    updateCharCounter(); 
    </script>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>