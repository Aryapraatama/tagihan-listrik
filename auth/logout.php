<?php
session_start();

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, "/");
    
    if (isset($_SESSION['user_id'])) {
        require_once '../config/koneksi.php';
        $user_id = $_SESSION['user_id'];
        $query = "UPDATE users SET remember_token = NULL WHERE id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

session_destroy();

header('Location: ../index.php?success=Anda telah berhasil logout');
exit();
?>