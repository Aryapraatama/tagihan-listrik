<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php?error=Silahkan login sebagai admin');
    exit();
}

function getTagihanById($id, $koneksi) {
    $id = clean_input($id);
    $query = "SELECT t.*, p.konsumen_id, p.bulan, p.tahun, p.meter_awal, p.meter_akhir, 
                        p.tanggal_catat, k.nomor_kwh, k.nama_pelanggan, k.daya, k.tarif_per_kwh
                FROM tagihan t
                JOIN pemakaian p ON t.pemakaian_id = p.id
                JOIN konsumen k ON p.konsumen_id = k.id
                WHERE t.id = '$id'";
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

if (isset($_GET['bayar'])) {
    $tagihan_id = clean_input($_GET['bayar']);
    $tanggal_bayar = date('Y-m-d');
    
    $query = "UPDATE tagihan SET status = 'lunas', tanggal_bayar = '$tanggal_bayar' 
                WHERE id = '$tagihan_id'";
    
    if (mysqli_query($koneksi, $query)) {
        header('Location: tagihan.php?success=Status tagihan berhasil diupdate');
        exit();
    } else {
        header('Location: tagihan.php?error=Gagal mengupdate status tagihan: ' . mysqli_error($koneksi));
        exit();
    }
}

if (isset($_GET['hapus'])) {
    $tagihan_id = clean_input($_GET['hapus']);
    
    $query = "DELETE p FROM pemakaian p 
                JOIN tagihan t ON p.id = t.pemakaian_id 
                WHERE t.id = '$tagihan_id'";
        
    if (mysqli_query($koneksi, $query)) {
        header('Location: tagihan.php?success=Tagihan berhasil dihapus');
        exit();
    } else {
        header('Location: tagihan.php?error=Gagal menghapus tagihan: ' . mysqli_error($koneksi));
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_tagihan'])) {
    $konsumen_id = clean_input($_POST['konsumen_id']);
    $bulan = clean_input($_POST['bulan']);
    $tahun = clean_input($_POST['tahun']);
    $meter_awal = clean_input($_POST['meter_awal']);
    $meter_akhir = clean_input($_POST['meter_akhir']);
    $tanggal_catat = clean_input($_POST['tanggal_catat']);
    $tanggal_jatuh_tempo = clean_input($_POST['tanggal_jatuh_tempo']);
    
    if ($meter_akhir <= $meter_awal) {
        header('Location: tagihan.php?error=Meter akhir harus lebih besar dari meter awal');
        exit();
    }
    
    $total_pemakaian = $meter_akhir - $meter_awal;
    
    $query_tarif = "SELECT tarif_per_kwh FROM konsumen WHERE id = '$konsumen_id'";
    $result_tarif = mysqli_query($koneksi, $query_tarif);
    
    if (!$result_tarif || mysqli_num_rows($result_tarif) == 0) {
        header('Location: tagihan.php?error=Data konsumen tidak ditemukan');
        exit();
    }
    
    $data_tarif = mysqli_fetch_assoc($result_tarif);
    $tarif_per_kwh = $data_tarif['tarif_per_kwh'];
    
    $total_bayar = $total_pemakaian * $tarif_per_kwh;
    
    mysqli_begin_transaction($koneksi);
    
    try {
        $query_pemakaian = "INSERT INTO pemakaian (konsumen_id, bulan, tahun, meter_awal, meter_akhir, tanggal_catat) 
                            VALUES ('$konsumen_id', '$bulan', '$tahun', '$meter_awal', '$meter_akhir', '$tanggal_catat')";
        
        if (!mysqli_query($koneksi, $query_pemakaian)) {
            throw new Exception("Gagal menyimpan data pemakaian: " . mysqli_error($koneksi));
        }
        
        $pemakaian_id = mysqli_insert_id($koneksi);
        
        $query_tagihan = "INSERT INTO tagihan (pemakaian_id, total_pemakaian, total_bayar, tanggal_jatuh_tempo, status) 
                            VALUES ('$pemakaian_id', '$total_pemakaian', '$total_bayar', '$tanggal_jatuh_tempo', 'belum_bayar')";
        
        if (!mysqli_query($koneksi, $query_tagihan)) {
            throw new Exception("Gagal menyimpan data tagihan: " . mysqli_error($koneksi));
        }
        
        mysqli_commit($koneksi);
        header('Location: tagihan.php?success=Tagihan berhasil ditambahkan');
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header('Location: tagihan.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_tagihan'])) {
    $tagihan_id = clean_input($_POST['tagihan_id']);
    $konsumen_id = clean_input($_POST['konsumen_id']);
    $bulan = clean_input($_POST['bulan']);
    $tahun = clean_input($_POST['tahun']);
    $meter_awal = clean_input($_POST['meter_awal']);
    $meter_akhir = clean_input($_POST['meter_akhir']);
    $tanggal_catat = clean_input($_POST['tanggal_catat']);
    $tanggal_jatuh_tempo = clean_input($_POST['tanggal_jatuh_tempo']);
    $status = clean_input($_POST['status']);
    $tanggal_bayar = clean_input($_POST['tanggal_bayar']);

    error_log("DEBUG Edit Tagihan: ID=$tagihan_id, Konsumen=$konsumen_id, Status=$status, Tanggal Bayar=$tanggal_bayar");
    
    if ($meter_akhir <= $meter_awal) {
        header('Location: tagihan.php?error=Meter akhir harus lebih besar dari meter awal');
        exit();
    }
    
    $total_pemakaian = $meter_akhir - $meter_awal;
    
    $query_tarif = "SELECT tarif_per_kwh FROM konsumen WHERE id = '$konsumen_id'";
    $result_tarif = mysqli_query($koneksi, $query_tarif);
    
    if (!$result_tarif || mysqli_num_rows($result_tarif) == 0) {
        header('Location: tagihan.php?error=Data konsumen tidak ditemukan');
        exit();
    }
    
    $data_tarif = mysqli_fetch_assoc($result_tarif);
    $tarif_per_kwh = $data_tarif['tarif_per_kwh'];
    
    $total_bayar = $total_pemakaian * $tarif_per_kwh;
    
    mysqli_begin_transaction($koneksi);
    
    try {
        $query_get_pemakaian = "SELECT pemakaian_id FROM tagihan WHERE id = '$tagihan_id'";
        $result_get = mysqli_query($koneksi, $query_get_pemakaian);
        
        if (!$result_get || mysqli_num_rows($result_get) == 0) {
            throw new Exception("Data tagihan tidak ditemukan");
        }
        
        $data_tagihan = mysqli_fetch_assoc($result_get);
        $pemakaian_id = $data_tagihan['pemakaian_id'];
        
        // Update data pemakaian
        $query_update_pemakaian = "UPDATE pemakaian 
                                    SET konsumen_id = '$konsumen_id', 
                                        bulan = '$bulan', 
                                        tahun = '$tahun', 
                                        meter_awal = '$meter_awal', 
                                        meter_akhir = '$meter_akhir', 
                                        tanggal_catat = '$tanggal_catat'
                                    WHERE id = '$pemakaian_id'";
            
        if (!mysqli_query($koneksi, $query_update_pemakaian)) {
            throw new Exception("Gagal mengupdate data pemakaian: " . mysqli_error($koneksi));
        }
        
        $query_update_tagihan = "UPDATE tagihan 
                                    SET total_pemakaian = '$total_pemakaian', 
                                        total_bayar = '$total_bayar', 
                                        tanggal_jatuh_tempo = '$tanggal_jatuh_tempo', 
                                        status = '$status'";
        
        if ($status == 'lunas') {
            if (!empty($tanggal_bayar)) {
                $query_update_tagihan .= ", tanggal_bayar = '$tanggal_bayar'";
            } else {
                $query_update_tagihan .= ", tanggal_bayar = CURDATE()";
            }
        } else {
            $query_update_tagihan .= ", tanggal_bayar = NULL";
        }
        
        $query_update_tagihan .= " WHERE id = '$tagihan_id'";
        
        error_log("DEBUG Query Update: " . $query_update_tagihan);
        
        if (!mysqli_query($koneksi, $query_update_tagihan)) {
            throw new Exception("Gagal mengupdate data tagihan: " . mysqli_error($koneksi));
        }
        
        mysqli_commit($koneksi);
        header('Location: tagihan.php?success=Tagihan berhasil diupdate');
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        error_log("ERROR Update Tagihan: " . $e->getMessage());
        header('Location: tagihan.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : 'semua';

$query = "SELECT t.*, p.bulan, p.tahun, p.tanggal_catat, 
            k.nomor_kwh, k.nama_pelanggan, k.daya
            FROM tagihan t
            JOIN pemakaian p ON t.pemakaian_id = p.id
            JOIN konsumen k ON p.konsumen_id = k.id";
            
if ($filter_status != 'semua') {
    $query .= " WHERE t.status = '$filter_status'";
}

$query .= " ORDER BY p.tahun DESC, 
            FIELD(p.bulan, 'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember') DESC";
        
$result = mysqli_query($koneksi, $query);

$query_konsumen = "SELECT id, nomor_kwh, nama_pelanggan, daya, tarif_per_kwh 
                    FROM konsumen 
                    ORDER BY nama_pelanggan";
$result_konsumen = mysqli_query($koneksi, $query_konsumen);

$tagihan_edit = null;
if (isset($_GET['edit'])) {
    $tagihan_edit = getTagihanById($_GET['edit'], $koneksi);
    if (!$tagihan_edit) {
        header('Location: tagihan.php?error=Data tagihan tidak ditemukan');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tagihan - Sistem Tagihan Listrik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            display: none;
            margin-bottom: 30px;
        }
        
        .form-container.show {
            display: block;
        }
        
        .btn-toggle-form {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .btn-edit-form {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .btn-toggle-form:hover,
        .btn-edit-form:hover {
            transform: translateY(-2px);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-cancel {
            background: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-cancel:hover {
            background: #e0e0e0;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-update {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-save:hover,
        .btn-update:hover {
            transform: translateY(-2px);
        }
        
        .calculation-result {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 4px solid #2196F3;
        }
        
        .calculation-result h4 {
            margin-top: 0;
            color: #333;
        }
        
        .tarif-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .auto-calc-btn {
            background: #ff9800;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .auto-calc-btn:hover {
            background: #f57c00;
        }
        
        .btn-action {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 2px;
        }
        
        .btn-edit {
            background: #FF9800;
            color: white;
        }
        
        .btn-edit:hover {
            background: #F57C00;
            transform: translateY(-2px);
        }
        
        .debug-info {
            background: #f0f0f0;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .header-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-toggle-form,
            .btn-edit-form {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <header class="header">
                <h1><i class="fas fa-money-bill-wave"></i> Kelola Tagihan Listrik</h1>
                <div class="header-actions">
                    <span class="date"><?php echo date('d F Y'); ?></span>
                    <div class="header-buttons" style="display: flex; gap: 10px; align-items: center;">
                        <?php if (!isset($_GET['edit'])): ?>
                        <button type="button" class="btn-toggle-form" id="toggleFormBtn">
                            <i class="fas fa-plus"></i> Tambah Tagihan
                        </button>
                        <?php else: ?>
                        <a href="tagihan.php" class="btn-toggle-form">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <?php endif; ?>
                        <div class="filter-group">
                            <form method="GET" action="" class="filter-form">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="semua" <?php echo $filter_status == 'semua' ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="belum_bayar" <?php echo $filter_status == 'belum_bayar' ? 'selected' : ''; ?>>Belum Bayar</option>
                                    <option value="lunas" <?php echo $filter_status == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                </select>
                                <?php if (isset($_GET['edit'])): ?>
                                <input type="hidden" name="edit" value="<?php echo $_GET['edit']; ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content">
                <div id="formTambahTagihan" class="form-container card <?php echo isset($_GET['edit']) ? 'show' : ''; ?>">
                    <h2>
                        <i class="fas <?php echo isset($tagihan_edit) ? 'fa-edit' : 'fa-plus-circle'; ?>"></i> 
                        <?php echo isset($tagihan_edit) ? 'Edit Tagihan' : 'Tambah Tagihan Baru'; ?>
                    </h2>
                    
                    <?php if (isset($_GET['edit']) && !$tagihan_edit): ?>
                    <div class="debug-info">
                        <strong>Error:</strong> Data tagihan tidak ditemukan. ID: <?php echo $_GET['edit']; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="tagihanForm">
                        <?php if (isset($tagihan_edit)): ?>
                        <input type="hidden" name="tagihan_id" value="<?php echo $tagihan_edit['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="konsumen_id">Konsumen *</label>
                                <select id="konsumen_id" name="konsumen_id" required>
                                    <option value="">Pilih Konsumen</option>
                                    <?php 
                                    mysqli_data_seek($result_konsumen, 0);
                                    if ($result_konsumen && mysqli_num_rows($result_konsumen) > 0):
                                        while ($kons = mysqli_fetch_assoc($result_konsumen)):
                                    ?>
                                    <option value="<?php echo $kons['id']; ?>" data-tarif="<?php echo $kons['tarif_per_kwh']; ?>"
                                        <?php echo (isset($tagihan_edit) && $tagihan_edit['konsumen_id'] == $kons['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kons['nomor_kwh'] . ' - ' . $kons['nama_pelanggan'] . ' (' . $kons['daya'] . ' Watt)'); ?>
                                    </option>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <option value="">Tidak ada data konsumen</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="bulan">Bulan *</label>
                                <select id="bulan" name="bulan" required>
                                    <option value="">Pilih Bulan</option>
                                    <?php
                                    $bulan_array = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    foreach ($bulan_array as $bulan):
                                    ?>
                                    <option value="<?php echo $bulan; ?>" 
                                        <?php echo (isset($tagihan_edit) && $tagihan_edit['bulan'] == $bulan) ? 'selected' : ''; ?>>
                                        <?php echo $bulan; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tahun">Tahun *</label>
                                <input type="number" id="tahun" name="tahun" min="2020" max="2030" 
                                        value="<?php echo isset($tagihan_edit) ? $tagihan_edit['tahun'] : date('Y'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="meter_awal">Meter Awal (kWh) *</label>
                                <input type="number" id="meter_awal" name="meter_awal" 
                                        min="0" step="1" 
                                        value="<?php echo isset($tagihan_edit) ? $tagihan_edit['meter_awal'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="meter_akhir">Meter Akhir (kWh) *</label>
                                <input type="number" id="meter_akhir" name="meter_akhir" 
                                        min="0" step="1" 
                                        value="<?php echo isset($tagihan_edit) ? $tagihan_edit['meter_akhir'] : ''; ?>" required>
                                <button type="button" class="auto-calc-btn" onclick="autoCalculate()">
                                    <i class="fas fa-calculator"></i> Hitung Otomatis
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tanggal_catat">Tanggal Pencatatan *</label>
                                <input type="date" id="tanggal_catat" name="tanggal_catat" 
                                        value="<?php echo isset($tagihan_edit) ? $tagihan_edit['tanggal_catat'] : date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo *</label>
                                <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" 
                                        value="<?php echo isset($tagihan_edit) ? $tagihan_edit['tanggal_jatuh_tempo'] : date('Y-m-d', strtotime('+20 days')); ?>" required>
                            </div>
                        </div>
                        
                        <?php if (isset($tagihan_edit)): ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select id="status" name="status" required>
                                    <option value="belum_bayar" <?php echo $tagihan_edit['status'] == 'belum_bayar' ? 'selected' : ''; ?>>Belum Bayar</option>
                                    <option value="lunas" <?php echo $tagihan_edit['status'] == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_bayar">Tanggal Bayar</label>
                                <input type="date" id="tanggal_bayar" name="tanggal_bayar" 
                                        value="<?php echo $tagihan_edit['tanggal_bayar'] ? $tagihan_edit['tanggal_bayar'] : ''; ?>">
                                <small class="tarif-info">* Hanya diisi jika status Lunas</small>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div id="calculationResult" class="calculation-result" style="display: none;">
                            <h4><i class="fas fa-calculator"></i> Hasil Perhitungan</h4>
                            <div class="calc-details">
                                <p>Pemakaian: <strong id="calcPemakaian">0</strong> kWh</p>
                                <p>Tarif: Rp <span id="calcTarif">0</span> per kWh</p>
                                <p>Total Tagihan: <strong id="calcTotal">Rp 0</strong></p>
                                <p class="tarif-info">* Tarif diambil dari data konsumen yang dipilih</p>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <?php if (isset($tagihan_edit)): ?>
                            <a href="tagihan.php" class="btn-cancel">Batal</a>
                            <button type="submit" name="edit_tagihan" class="btn-update">
                                <i class="fas fa-save"></i> Update Tagihan
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn-cancel" id="cancelBtn">Batal</button>
                            <button type="submit" name="tambah_tagihan" class="btn-save">
                                <i class="fas fa-save"></i> Simpan Tagihan
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #FF9800;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Tagihan</h3>
                            <?php
                            $query_total = "SELECT COUNT(*) as total FROM tagihan";
                            $result_total = mysqli_query($koneksi, $query_total);
                            $data_total = mysqli_fetch_assoc($result_total);
                            ?>
                            <p class="stat-number"><?php echo number_format($data_total['total']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #F44336;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Belum Bayar</h3>
                            <?php
                            $query_belum = "SELECT COUNT(*) as total FROM tagihan WHERE status = 'belum_bayar'";
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
                            <h3>Lunas</h3>
                            <?php
                            $query_lunas = "SELECT COUNT(*) as total FROM tagihan WHERE status = 'lunas'";
                            $result_lunas = mysqli_query($koneksi, $query_lunas);
                            $data_lunas = mysqli_fetch_assoc($result_lunas);
                            ?>
                            <p class="stat-number"><?php echo number_format($data_lunas['total']); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #2196F3;">
                            <i class="fas fa-money-bill"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Penerimaan</h3>
                            <?php
                            $query_penerimaan = "SELECT SUM(total_bayar) as total FROM tagihan WHERE status = 'lunas'";
                            $result_penerimaan = mysqli_query($koneksi, $query_penerimaan);
                            $data_penerimaan = mysqli_fetch_assoc($result_penerimaan);
                            ?>
                            <p class="stat-number">Rp <?php echo number_format($data_penerimaan['total'] ?? 0, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-list"></i> Daftar Tagihan</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Konsumen</th>
                                    <th>Periode</th>
                                    <th>Pemakaian</th>
                                    <th>Total Tagihan</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                    <?php 
                                    mysqli_data_seek($result, 0);
                                    $no = 1; 
                                    while ($row = mysqli_fetch_assoc($result)): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['nama_pelanggan']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($row['nomor_kwh']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['bulan'] . ' ' . $row['tahun']); ?></td>
                                        <td><?php echo number_format($row['total_pemakaian']); ?> kWh</td>
                                        <td>
                                            <strong>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])); ?><br>
                                            <small class="<?php echo (strtotime($row['tanggal_jatuh_tempo']) < time() && $row['status'] == 'belum_bayar') ? 'text-danger' : ''; ?>">
                                                <?php 
                                                if ($row['status'] == 'belum_bayar') {
                                                    $diff = strtotime($row['tanggal_jatuh_tempo']) - time();
                                                    $days = ceil($diff / (60 * 60 * 24));
                                                    echo $days > 0 ? "$days hari lagi" : "Terlambat " . abs($days) . " hari";
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $row['status']; ?>">
                                                <?php echo $row['status'] == 'lunas' ? 'Lunas' : 'Belum Bayar'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $row['tanggal_bayar'] ? date('d/m/Y', strtotime($row['tanggal_bayar'])) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'belum_bayar'): ?>
                                                <a href="?bayar=<?php echo $row['id']; ?>" 
                                                    class="btn-action btn-success" title="Tandai Lunas"
                                                    onclick="return confirm('Tandai tagihan sebagai lunas?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?edit=<?php echo $row['id']; ?>" 
                                                class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?hapus=<?php echo $row['id']; ?>" 
                                                class="btn-action btn-delete" title="Hapus"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus tagihan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="no-data">Belum ada data tagihan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-print"></i> Cetak Laporan</h2>
                    <div class="report-options">
                        <form method="GET" action="cetak_laporan.php" target="_blank">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="bulan_laporan">Bulan</label>
                                    <select id="bulan_laporan" name="bulan" required>
                                        <?php
                                        $bulan_ini = date('n');
                                        $bulan_array = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                        foreach ($bulan_array as $key => $bulan):
                                        ?>
                                        <option value="<?php echo $key + 1; ?>" <?php echo ($key + 1 == $bulan_ini) ? 'selected' : ''; ?>>
                                            <?php echo $bulan; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="tahun_laporan">Tahun</label>
                                    <input type="number" id="tahun_laporan" name="tahun" 
                                            value="<?php echo date('Y'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="jenis_laporan">Jenis Laporan</label>
                                    <select id="jenis_laporan" name="jenis" required>
                                        <option value="tagihan">Laporan Tagihan</option>
                                        <option value="pembayaran">Laporan Pembayaran</option>
                                        <option value="konsumen">Laporan Konsumen</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn-print">
                                <i class="fas fa-print"></i> Cetak Laporan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const formContainer = document.getElementById('formTambahTagihan');
    const cancelBtn = document.getElementById('cancelBtn');
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('edit')) {
        if (formContainer) {
            formContainer.classList.add('show');
        }

        setTimeout(() => {
            calculateTagihan();
        }, 100);
    }
    
    if (toggleFormBtn) {
        toggleFormBtn.addEventListener('click', function() {
            if (formContainer) {
                formContainer.classList.toggle('show');
                if (formContainer.classList.contains('show')) {
                    toggleFormBtn.innerHTML = '<i class="fas fa-times"></i> Tutup Form';
                    // Reset form jika bukan mode edit
                    if (!urlParams.has('edit')) {
                        resetForm();
                    }
                } else {
                    toggleFormBtn.innerHTML = '<i class="fas fa-plus"></i> Tambah Tagihan';
                }
            }
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (formContainer) {
                formContainer.classList.remove('show');
            }
            if (toggleFormBtn) {
                toggleFormBtn.innerHTML = '<i class="fas fa-plus"></i> Tambah Tagihan';
            }
            resetForm();
        });
    }
    
    function resetForm() {
        const form = document.getElementById('tagihanForm');
        if (form) {
            form.reset();
            document.getElementById('tahun').value = new Date().getFullYear();
            document.getElementById('tanggal_catat').valueAsDate = new Date();
            
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 20);
            document.getElementById('tanggal_jatuh_tempo').valueAsDate = tomorrow;
            
            document.getElementById('calculationResult').style.display = 'none';
        }
    }
    
    function calculateTagihan() {
        const konsumenSelect = document.getElementById('konsumen_id');
        const meterAwal = parseFloat(document.getElementById('meter_awal').value) || 0;
        const meterAkhir = parseFloat(document.getElementById('meter_akhir').value) || 0;
        const selectedOption = konsumenSelect.options[konsumenSelect.selectedIndex];
        const tarif = parseFloat(selectedOption.getAttribute('data-tarif')) || 0;
        
        if (meterAkhir > meterAwal && tarif > 0) {
            const pemakaian = meterAkhir - meterAwal;
            const totalTagihan = pemakaian * tarif;
            
            const calcPemakaian = document.getElementById('calcPemakaian');
            const calcTarif = document.getElementById('calcTarif');
            const calcTotal = document.getElementById('calcTotal');
            const calcResult = document.getElementById('calculationResult');
            
            if (calcPemakaian && calcTarif && calcTotal && calcResult) {
                calcPemakaian.textContent = pemakaian.toLocaleString();
                calcTarif.textContent = tarif.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                calcTotal.textContent = 'Rp ' + totalTagihan.toLocaleString('id-ID');
                calcResult.style.display = 'block';
            }
            
            return { pemakaian, totalTagihan };
        } else {
            const calcResult = document.getElementById('calculationResult');
            if (calcResult) {
                calcResult.style.display = 'none';
            }
            return null;
        }
    }
    
    function autoCalculate() {
        const meterAwal = document.getElementById('meter_awal');
        const meterAkhir = document.getElementById('meter_akhir');
        
        if (meterAwal && meterAwal.value) {
            // Auto set meter akhir 150 kWh lebih besar (contoh)
            const meterAkhirValue = parseFloat(meterAwal.value) + 150;
            meterAkhir.value = meterAkhirValue;
            calculateTagihan();
        } else {
            alert('Isi meter awal terlebih dahulu');
        }
    }
    
    const konsumenSelect = document.getElementById('konsumen_id');
    const meterAwalInput = document.getElementById('meter_awal');
    const meterAkhirInput = document.getElementById('meter_akhir');
    
    if (konsumenSelect) {
        konsumenSelect.addEventListener('change', calculateTagihan);
    }
    if (meterAwalInput) {
        meterAwalInput.addEventListener('input', calculateTagihan);
    }
    if (meterAkhirInput) {
        meterAkhirInput.addEventListener('input', calculateTagihan);
    }
    
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const tanggalBayarInput = document.getElementById('tanggal_bayar');
            if (this.value === 'lunas' && tanggalBayarInput && !tanggalBayarInput.value) {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                tanggalBayarInput.value = `${year}-${month}-${day}`;
            }
        });
    }
    
    const tagihanForm = document.getElementById('tagihanForm');
    if (tagihanForm) {
        tagihanForm.addEventListener('submit', function(e) {
            const konsumenId = document.getElementById('konsumen_id');
            const meterAwal = parseFloat(document.getElementById('meter_awal').value) || 0;
            const meterAkhir = parseFloat(document.getElementById('meter_akhir').value) || 0;
            const tanggalCatat = document.getElementById('tanggal_catat').value;
            const tanggalJatuhTempo = document.getElementById('tanggal_jatuh_tempo').value;
            const status = document.getElementById('status') ? document.getElementById('status').value : 'belum_bayar';
            const tanggalBayar = document.getElementById('tanggal_bayar') ? document.getElementById('tanggal_bayar').value : '';
            
            let errors = [];
            
            if (!konsumenId || !konsumenId.value) {
                errors.push('Pilih konsumen');
            }
            
            if (meterAkhir <= meterAwal) {
                errors.push('Meter akhir harus lebih besar dari meter awal');
            }
            
            if (!tanggalCatat) {
                errors.push('Tanggal pencatatan harus diisi');
            }
            
            if (!tanggalJatuhTempo) {
                errors.push('Tanggal jatuh tempo harus diisi');
            }
            
            if (tanggalJatuhTempo && tanggalCatat && new Date(tanggalJatuhTempo) <= new Date(tanggalCatat)) {
                errors.push('Tanggal jatuh tempo harus setelah tanggal pencatatan');
            }
            
            if (status === 'lunas' && !tanggalBayar) {
                errors.push('Tanggal bayar harus diisi jika status Lunas');
            }
            
            if (tanggalBayar && new Date(tanggalBayar) < new Date(tanggalCatat)) {
                errors.push('Tanggal bayar tidak boleh sebelum tanggal pencatatan');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Perbaiki kesalahan berikut:\n\n' + errors.join('\n'));
            }
        });
    }
    
    const tanggalCatatInput = document.getElementById('tanggal_catat');
    if (tanggalCatatInput) {
        tanggalCatatInput.addEventListener('change', function() {
            if (this.value) {
                const tanggalJatuhTempoInput = document.getElementById('tanggal_jatuh_tempo');
                if (tanggalJatuhTempoInput) {
                    const tanggalCatat = new Date(this.value);
                    tanggalCatat.setDate(tanggalCatat.getDate() + 20);
                    
                    const year = tanggalCatat.getFullYear();
                    const month = String(tanggalCatat.getMonth() + 1).padStart(2, '0');
                    const day = String(tanggalCatat.getDate()).padStart(2, '0');
                    
                    tanggalJatuhTempoInput.value = `${year}-${month}-${day}`;
                }
            }
        });
    }
    
    if (urlParams.has('success')) {
        alert(urlParams.get('success'));
        urlParams.delete('success');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
    
    if (urlParams.has('error')) {
        alert('Error: ' + urlParams.get('error'));
        urlParams.delete('error');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>