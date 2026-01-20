<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php?error=Silahkan login sebagai admin');
    exit();
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-bolt"></i> PLN Digital</h2>
        <p>Admin Panel</p>
    </div>
    <div class="sidebar-user">
        <i class="fas fa-user-circle"></i>
        <h3><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h3>
        <p>Administrator</p>
    </div>
    <nav class="sidebar-nav">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="konsumen.php" class="<?php echo $current_page == 'konsumen.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Data Konsumen
        </a>
        <a href="pemakaian.php" class="<?php echo $current_page == 'pemakaian.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice"></i> Pemakaian Listrik
        </a>
        <a href="tagihan.php" class="<?php echo $current_page == 'tagihan.php' ? 'active' : ''; ?>">
            <i class="fas fa-money-bill-wave"></i> Tagihan
        </a>
        <a href="feedback.php" class="<?php echo $current_page == 'feedback.php' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i> Feedback
        </a>
        <a href="../auth/logout.php" class="logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>