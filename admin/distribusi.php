<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

global $pdo;

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $id = $_POST['id'] ?? null;
    $production_date = trim($_POST['production_date'] ?? '');
    $batch_name = trim($_POST['batch_name'] ?? '');
    $produced = (int) ($_POST['produced'] ?? 0);
    $distributed = (int) ($_POST['distributed'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($production_date === '') {
        $error = 'Tanggal produksi wajib diisi.';
    } elseif ($produced < 0 || $distributed < 0) {
        $error = 'Jumlah tidak boleh negatif.';
    } elseif ($distributed > $produced) {
        $error = 'Jumlah distribusi tidak boleh melebihi jumlah donat yang diproduksi.';
    } else {
        $uniq = $batch_name !== '' ? $batch_name : null;
        $check = $pdo->prepare('SELECT id FROM productions WHERE production_date = ? AND (? IS NULL AND batch_name IS NULL OR batch_name = ?) AND id != ?');
        $check->execute([$production_date, $uniq, $uniq, $id ?? 0]);
        if ($check->fetch()) {
            $error = 'Data produksi untuk tanggal dan batch yang sama sudah ada.';
        } else {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE productions SET production_date = ?, batch_name = ?, produced = ?, distributed = ?, note = ? WHERE id = ?');
                $stmt->execute([$production_date, $uniq, $produced, $distributed, $note, $id]);
                $success = 'Data produksi berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO productions (production_date, batch_name, produced, distributed, note, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$production_date, $uniq, $produced, $distributed, $note]);
                $success = 'Data produksi berhasil disimpan.';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM productions WHERE id = ?')->execute([$_GET['delete']]);
    header('Location: /laporan_penjualan_harian/admin/produksi.php?success=Data+produksi+berhasil+dihapus');
    exit;
}

$edit = null;
if (!empty($_GET['edit'])) {
    $edit = $pdo->prepare('SELECT * FROM productions WHERE id = ? LIMIT 1');
    $edit->execute([$_GET['edit']]);
    $edit = $edit->fetch();
}

$rows = $pdo->query('SELECT * FROM productions ORDER BY production_date DESC, id DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Produksi Donat</h2>
  </div>

  <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?php echo (int) $edit['id']; ?>"><?php endif; ?>

        <div class="col-md-3">
          <label class="form-label">Tanggal produksi</label>
          <input type="date" name="production_date" class="form-control" value="<?php echo htmlspecialchars($edit['production_date'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Batch / label</label>
          <input type="text" name="batch_name" class="form-control" value="<?php echo htmlspecialchars($edit['batch_name'] ?? ''); ?>" placeholder="Opsional">
        </div>
        <div class="col-md-3">
          <label class="form-label">Jumlah diproduksi</label>
          <input type="number" min="0" step="1" name="produced" class="form-control" value="<?php echo htmlspecialchars($edit['produced'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Jumlah didistribusikan</label>
          <input type="number" min="0" step="1" name="distributed" class="form-control" value="<?php echo htmlspecialchars($edit['distributed'] ?? ''); ?>" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Catatan</label>
          <textarea name="note" class="form-control"><?php echo htmlspecialchars($edit['note'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-pink"><?php echo $edit ? 'Update' : 'Simpan'; ?></button>
          <?php if ($edit): ?><a href="/laporan_penjualan_harian/admin/produksi.php" class="btn btn-outline-secondary ms-2">Batal</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Tanggal</th>
            <th>Batch</th>
            <th>Produksi</th>
            <th>Distribusi</th>
            <th>Sisa</th>
            <th>Catatan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['production_date']); ?></td>
              <td><?php echo htmlspecialchars($row['batch_name'] ?: '-'); ?></td>
              <td><?php echo number_format((int)$row['produced']); ?></td>
              <td><?php echo number_format((int)$row['distributed']); ?></td>
              <td><?php echo number_format((int)$row['produced'] - (int)$row['distributed']); ?></td>
              <td><?php echo htmlspecialchars($row['note'] ?: '-'); ?></td>
              <td>
                <a href="/laporan_penjualan_harian/admin/produksi.php?edit=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="/laporan_penjualan_harian/admin/produksi.php?delete=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data produksi ini?');">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
