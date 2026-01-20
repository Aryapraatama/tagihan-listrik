<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php?error=Silahkan login sebagai admin');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_konsumen'])) {
    $username = clean_input($_POST['username']);
    $password = password_hash('password123', PASSWORD_DEFAULT); // Default password
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $nomor_kwh = clean_input($_POST['nomor_kwh']);
    $nama_pelanggan = clean_input($_POST['nama_pelanggan']);
    $alamat = clean_input($_POST['alamat']);
    $daya = clean_input($_POST['daya']);
    $tarif_per_kwh = clean_input($_POST['tarif_per_kwh']);
    
    mysqli_begin_transaction($koneksi);
    
    try {
        $query_user = "INSERT INTO users (username, password, nama_lengkap, role) 
                        VALUES ('$username', '$password', '$nama_lengkap', 'konsumen')";
        mysqli_query($koneksi, $query_user);
        $user_id = mysqli_insert_id($koneksi);
        
        $query_konsumen = "INSERT INTO konsumen (user_id, nomor_kwh, nama_pelanggan, alamat, daya, tarif_per_kwh) 
                            VALUES ('$user_id', '$nomor_kwh', '$nama_pelanggan', '$alamat', '$daya', '$tarif_per_kwh')";
        mysqli_query($koneksi, $query_konsumen);
        
        mysqli_commit($koneksi);
        header('Location: konsumen.php?success=Konsumen berhasil ditambahkan');
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header('Location: konsumen.php?error=Gagal menambahkan konsumen');
        exit();
    }
}

if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    $query = "DELETE FROM users WHERE id = '$id' AND role = 'konsumen'";
    
    if (mysqli_query($koneksi, $query)) {
        header('Location: konsumen.php?success=Konsumen berhasil dihapus');
        exit();
    } else {
        header('Location: konsumen.php?error=Gagal menghapus konsumen');
        exit();
    }
}

$query = "SELECT u.id, u.username, u.nama_lengkap, u.created_at, 
          k.nomor_kwh, k.nama_pelanggan, k.alamat, k.daya, k.tarif_per_kwh
          FROM users u
          LEFT JOIN konsumen k ON u.id = k.user_id
          WHERE u.role = 'konsumen'
          ORDER BY u.created_at DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Konsumen - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <header class="header">
                <h1><i class="fas fa-users"></i> Kelola Data Konsumen</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                    <button class="btn-add" onclick="toggleForm()">
                        <i class="fas fa-plus"></i> Tambah Konsumen
                    </button>
                </div>
            </header>

            <div class="content">
                <div id="formTambah" class="card" style="display: none;">
                    <h2><i class="fas fa-user-plus"></i> Tambah Konsumen Baru</h2>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required 
                                        placeholder="Masukkan username">
                            </div>
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" required 
                                        placeholder="Masukkan nama lengkap">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nomor_kwh">Nomor KWH</label>
                                <input type="text" id="nomor_kwh" name="nomor_kwh" required 
                                        placeholder="Contoh: KWH0012345">
                            </div>
                            <div class="form-group">
                                <label for="nama_pelanggan">Nama Pelanggan</label>
                                <input type="text" id="nama_pelanggan" name="nama_pelanggan" required 
                                        placeholder="Nama pada tagihan listrik">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" rows="3" required 
                                        placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="daya">Daya Listrik (Watt)</label>
                                <select id="daya" name="daya" required>
                                    <option value="">Pilih Daya</option>
                                    <option value="450">450 Watt</option>
                                    <option value="900">900 Watt</option>
                                    <option value="1300">1300 Watt</option>
                                    <option value="2200">2200 Watt</option>
                                    <option value="3500">3500 Watt</option>
                                    <option value="5500">5500 Watt</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tarif_per_kwh">Tarif per kWh</label>
                                <input type="number" id="tarif_per_kwh" name="tarif_per_kwh" step="0.01" required 
                                        placeholder="Contoh: 1444.70">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="toggleForm()">Batal</button>
                            <button type="submit" name="tambah_konsumen" class="btn-save">
                                <i class="fas fa-save"></i> Simpan Konsumen
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <h2><i class="fas fa-list"></i> Daftar Konsumen</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Nomor KWH</th>
                                    <th>Daya</th>
                                    <th>Tarif/kWh</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nomor_kwh']); ?></td>
                                        <td><?php echo number_format($row['daya']); ?> Watt</td>
                                        <td>Rp <?php echo number_format($row['tarif_per_kwh'], 2, ',', '.'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="pemakaian.php?konsumen=<?php echo $row['id']; ?>" 
                                                class="btn-action" title="Catat Pemakaian">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                            <a href="?hapus=<?php echo $row['id']; ?>" 
                                                class="btn-action btn-delete" title="Hapus"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus konsumen ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="no-data">Belum ada data konsumen</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-info-circle"></i> Informasi Penting</h2>
                    <div class="info-content">
                        <p><strong>Password default:</strong> password123</p>
                        <p><strong>Tarif per kWh:</strong> Sesuai dengan daya yang dipilih</p>
                        <ul>
                            <li>450 Watt: Rp 415 per kWh</li>
                            <li>900 Watt: Rp 605 per kWh</li>
                            <li>1300 Watt: Rp 1.444,70 per kWh</li>
                            <li>2200 Watt: Rp 1.444,70 per kWh</li>
                            <li>3500 Watt: Rp 1.444,70 per kWh</li>
                            <li>5500 Watt: Rp 1.669,53 per kWh</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleForm() {
        const form = document.getElementById('formTambah');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
    
    document.getElementById('daya').addEventListener('change', function() {
        const tarifMap = {
            '450': 415,
            '900': 605,
            '1300': 1444.70,
            '2200': 1444.70,
            '3500': 1444.70,
            '5500': 1669.53
        };
        
        const tarif = tarifMap[this.value];
        if (tarif) {
            document.getElementById('tarif_per_kwh').value = tarif;
        }
    });
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>