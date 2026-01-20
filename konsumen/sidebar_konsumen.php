<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'konsumen') {
    header('Location: ../index.php?error=Silahkan login sebagai konsumen');
    exit();
}
?>
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
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="beranda.php" class="<?php echo $current_page == 'beranda.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Beranda
        </a>
        <a href="tagihan.php" class="<?php echo $current_page == 'tagihan.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice"></i> Tagihan
        </a>
        <a href="riwayat.php" class="<?php echo $current_page == 'riwayat.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Riwayat Pembayaran
        </a>
        <a href="feedback.php" class="<?php echo $current_page == 'feedback.php' ? 'active' : ''; ?>">
            <i class="fas fa-comment"></i> Kirim Feedback
        </a>
        <a href="../auth/logout.php" class="logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>