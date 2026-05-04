<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config/database.php';
    
    $errors = [];
    $kode = '';
    $nama = '';
    $deskripsi = '';
    $status = 'Aktif';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // ambil dan sanitasi data dari form
        $kode = htmlspecialchars(trim($_POST['kode_kategori']));
        $nama = htmlspecialchars(trim($_POST['nama_kategori']));
        $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));
        $status = $_POST['status'] ?? 'Aktif';
        
        //cek kategori
        if (empty($kode)) {
            $errors[] = "Kode Kategori wajib diisi.";
        } else {
            //Cek pajang karakter
            if (strlen($kode) < 4 || strlen($kode) > 10) {
                $errors[] = "Kode Kategori harus 4-10 karakter.";
            }
            //Cek format KAT- gunakan substr
            if (substr($kode, 0, 4) !== "KAT-") {
                $errors[] = "Kode Kategori harus diawali dengan 'KAT-'.";
            }
        }
        
        //cek kategori
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

        //cek status tambahan
        if ($status !== 'Aktif' && $status !== 'Nonaktif') {
            $errors[] = "Pilihan status tidak valid.";
        }
        
        //cek duplikasi kode
        if (empty($errors)) {
            $cek_query = "SELECT id_kategori FROM kategori WHERE kode_kategori = ?";
            $stmt_cek = $conn->prepare($cek_query);
            $stmt_cek->bind_param("s", $kode);
            $stmt_cek->execute();
            $stmt_cek->store_result();
            
            if ($stmt_cek->num_rows > 0) {
                $errors[] = "Kode Kategori '$kode' sudah digunakan di database. Silakan ganti.";
            }
            $stmt_cek->close();
        }
        
        //insert data,bila aman
        if (empty($errors)) {
            $insert_query = "INSERT INTO kategori (kode_kategori, nama_kategori, deskripsi, status) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_query);
            $stmt_insert->bind_param("ssss", $kode, $nama, $deskripsi, $status);
            
            if ($stmt_insert->execute()) {
                //direct jika berhasil
                $stmt_insert->close();
                header("Location: index.php?success=" . urlencode("Kategori baru berhasil ditambahkan."));
                exit();
            } else {
                $errors[] = "Sistem gagal menyimpan data: " . $conn->error;
            }
        }
    }
    ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Tambah Kategori Baru</h4>
                    </div>
                    <div class="card-body">
                        <!--ampilkan error jika ada -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <!-- form fields -->
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
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>