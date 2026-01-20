<?php

/**
 * 
 * @param string $required_role 
 */
function checkLogin($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        if (isset($_COOKIE['remember_token'])) {
            require_once 'koneksi.php';
            $token = $_COOKIE['remember_token'];
            
            $query = "SELECT u.* FROM users u WHERE u.remember_token = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                mysqli_stmt_close($stmt);
                return;
            }
            mysqli_stmt_close($stmt);
        }

        header('Location: ../index.php?error=Silahkan login terlebih dahulu');
        exit();
    }
    
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 28800)) {
        session_destroy();
        header('Location: ../index.php?error=Sesi telah habis, silahkan login kembali');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
    
    if ($required_role && $_SESSION['role'] != $required_role) {
        header('Location: ../index.php?error=Akses ditolak');
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        if ($_SESSION['role'] == 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: konsumen/beranda.php');
        }
        exit();
    }
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    require_once 'koneksi.php';
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT u.*, 
                CASE 
                    WHEN u.role = 'konsumen' THEN k.nama_pelanggan 
                    ELSE u.nama_lengkap 
                END as nama_tampil,
                k.nomor_kwh, k.alamat, k.daya, k.tarif_per_kwh
                FROM users u
                LEFT JOIN konsumen k ON u.id = k.user_id
                WHERE u.id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}
?>