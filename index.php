<?php
require_once 'config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: konsumen/beranda.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><i class="fas fa-bolt"></i> PLN Digital</h1>
                <p>Sistem Informasi Tagihan Listrik</p>
            </div>
            
            <form action="auth/proses_login.php" method="POST" class="login-form">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> 
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required 
                            placeholder="Masukkan username">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required 
                            placeholder="Masukkan password">
                </div>
                
                <div class="form-group">
                    <label for="role"><i class="fas fa-users"></i> Login sebagai</label>
                    <select id="role" name="role" required>
                        <option value="">Pilih role</option>
                        <option value="admin">Admin</option>
                        <option value="konsumen">Konsumen</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="login-footer">
                <p>Demo Account:</p>
                <p><strong>Admin:</strong> admin / password123</p>
                <p><strong>Konsumen:</strong> user123 / password123</p>
            </div>
        </div>
    </div>
</body>
</html>