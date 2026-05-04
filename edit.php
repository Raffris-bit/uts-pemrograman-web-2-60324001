<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config/database.php';
    
    $errors = [];
    
    //ambil get di id
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: index.php?error=ID Kategori tidak valid.");
        exit();
    }
    
    $id_kategori = $_GET['id'];
    
    //dapatkan data berdasarkan id
    $query_get = "SELECT * FROM kategori WHERE id_kategori = ?";
    $stmt_get = $conn->prepare($query_get);
    $stmt_get->bind_param("i", $id_kategori);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    //bila data tidak ditemukan di database
    if ($result->num_rows == 0) {
        $stmt_get->close();
        header("Location: index.php?error=Kategori tidak ditemukan di database.");
        exit();
    }
    
    //prefil data awal dari database
    $kategori = $result->fetch_assoc();
    $kode = $kategori['kode_kategori'];
    $nama = $kategori['nama_kategori'];
    $deskripsi = $kategori['deskripsi'];
    $status = $kategori['status'];
    $stmt_get->close();
    
    //jika post, maka proses update
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //ambil dan sanitasikan data dari form
        $kode = htmlspecialchars(trim($_POST['kode_kategori']));
        $nama = htmlspecialchars(trim($_POST['nama_kategori']));
        $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));
        $status = $_POST['status'] ?? 'Aktif';
        
        //cek kategori
        if (empty($kode)) {
            $errors[] = "Kode Kategori wajib diisi.";
        } else {
            if (strlen($kode) < 4 || strlen($kode) > 10) {
                $errors[] = "Kode Kategori harus 4-10 karakter.";
            }
            if (substr($kode, 0, 4) !== "KAT-") {
                $errors[] = "Kode Kategori harus diawali dengan 'KAT-'.";
            }
        }
        
        //cek nama kategori
        if (empty($nama)) {
            $errors[] = "Nama Kategori wajib diisi.";
        } else {
            if (strlen($nama) < 3 || strlen($nama) > 50) {
                $errors[] = "Nama Kategori harus 3-50 karakter.";
            }
        }
        
        //cek deskripsi
        if (!empty($deskripsi) && strlen($deskripsi) > 200) {
            $errors[] = "Deskripsi maksimal 200 karakter.";
        }

        //cek status
        if ($status !== 'Aktif' && $status !== 'Nonaktif') {
            $errors[] = "Pilihan status tidak valid.";
        }
        
        //cek duplikat kode
        if (empty($errors)) {
            $cek_query = "SELECT id_kategori FROM kategori WHERE kode_kategori = ? AND id_kategori != ?";
            $stmt_cek = $conn->prepare($cek_query);
            $stmt_cek->bind_param("si", $kode, $id_kategori);
            $stmt_cek->execute();
            $stmt_cek->store_result();
            
            if ($stmt_cek->num_rows > 0) {
                $errors[] = "Kode Kategori '$kode' sudah dipakai oleh kategori lain.";
            }
            $stmt_cek->close();
        }
        
        //bila gak error, akan update
        if (empty($errors)) {
            $update_query = "UPDATE kategori SET kode_kategori = ?, nama_kategori = ?, deskripsi = ?, status = ? WHERE id_kategori = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("ssssi", $kode, $nama, $deskripsi, $status, $id_kategori);
            
            if ($stmt_update->execute()) {
                $stmt_update->close();
                header("Location: index.php?success=" . urlencode("Data Kategori berhasil diperbarui."));
                exit();
            } else {
                $errors[] = "Gagal mengupdate data: " . $conn->error;
            }
        }
    }
    ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0">Edit Kategori</h4>
                    </div>
                    <div class="card-body">
                        <!--menampilkan eror jika ada -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!--form dengan data prefiled -->
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="kode_kategori" class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" value="<?= $kode; ?>" required>
                                <div class="form-text">Format wajib: KAT-XXX (4-10 karakter).</div>
                            </div>

                            <div class="mb-3">
                                <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= $nama; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $deskripsi; ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="statusAktif" value="Aktif" <?= ($status == 'Aktif') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="statusAktif">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="statusNonaktif" value="Nonaktif" <?= ($status == 'Nonaktif') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="statusNonaktif">Nonaktif</label>
                                </div>
                            </div>
                            
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">Update Data</button>
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>