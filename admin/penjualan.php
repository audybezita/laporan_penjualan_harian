<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $id = $_POST['id'] ?? null;
    $production_id = $_POST['production_id'] ?? null;
    $distribution_date = trim($_POST['distribution_date'] ?? '');
    $distributed = (int) ($_POST['distributed'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($distribution_date === '') {
        $error = 'Tanggal distribusi wajib diisi.';
    } elseif ($distributed < 0) {
        $error = 'Jumlah distribusi tidak boleh negatif.';
    } else {
        $prod = null;
        if ($production_id) {
            $prod = $pdo->prepare('SELECT produced, distributed FROM productions WHERE id = ? LIMIT 1');
            $prod->execute([$production_id]);
            $prod = $prod->fetch();
            if (!$prod) {
                $error = 'Data produksi tidak ditemukan.';
            } elseif ($distributed > ((int)$prod['produced'] - (int)$prod['distributed'])) {
                $error = 'Distribusi melebihi jumlah donat yang tersedia.';
            }
        }

        if (empty($error)) {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE distributions SET production_id = ?, distribution_date = ?, distributed = ?, note = ? WHERE id = ?');
                $stmt->execute([$production_id ?: null, $distribution_date, $distributed, $note, $id]);
                $success = 'Data distribusi berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO distributions (production_id, distribution_date, distributed, note, created_at) VALUES (?, ?, ?, ?, NOW())');
                $stmt->execute([$production_id ?: null, $distribution_date, $distributed, $note]);
                $success = 'Data distribusi berhasil disimpan.';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM distributions WHERE id = ?')->execute([$_GET['delete']]);
    header('Location: /laporan_penjualan_harian/admin/distribusi.php?success=Data+distribusi+berhasil+dihapus');
    exit;
}

$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM distributions WHERE id = ? LIMIT 1');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

$productions = $pdo->query('SELECT * FROM productions ORDER BY production_date DESC')->fetchAll();
$rows = $pdo->query('SELECT d.*, p.production_date, p.batch_name FROM distributions d LEFT JOIN productions p ON p.id = d.production_id ORDER BY d.distribution_date DESC, d.id DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <h2 class="mb-4">Distribusi Donat</h2>
  <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?php echo (int) $edit['id']; ?>"><?php endif; ?>

        <div class="col-md-4">
          <label class="form-label">Produksi terkait</label>
          <select name="production_id" class="form-select">
            <option value="">-- Pilih produksi --</option>
            <?php foreach ($productions as $p): ?>
              <option value="<?php echo (int)$p['id']; ?>" <?php echo (($edit['production_id'] ?? '') == $p['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($p['production_date'] . ' - ' . ($p['batch_name'] ?: 'Batch umum')); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Tanggal distribusi</label>
          <input type="date" name="distribution_date" class="form-control" value="<?php echo htmlspecialchars($edit['distribution_date'] ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Jumlah dititipkan</label>
          <input type="number" min="0" step="1" name="distributed" class="form-control" value="<?php echo htmlspecialchars($edit['distributed'] ?? ''); ?>" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Catatan</label>
          <textarea name="note" class="form-control"><?php echo htmlspecialchars($edit['note'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-pink"><?php echo $edit ? 'Update' : 'Simpan'; ?></button>
          <?php if ($edit): ?><a href="/laporan_penjualan_harian/admin/distribusi.php" class="btn btn-outline-secondary ms-2">Batal</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Tanggal distribusi</th>
            <th>Produksi</th>
            <th>Jumlah</th>
            <th>Catatan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['distribution_date']); ?></td>
              <td><?php echo htmlspecialchars($row['batch_name'] ?: $row['production_date'] ?: '-'); ?></td>
              <td><?php echo number_format((int)$row['distributed']); ?></td>
              <td><?php echo htmlspecialchars($row['note'] ?: '-'); ?></td>
              <td>
                <a href="/laporan_penjualan_harian/admin/distribusi.php?edit=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="/laporan_penjualan_harian/admin/distribusi.php?delete=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data distribusi ini?');">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
