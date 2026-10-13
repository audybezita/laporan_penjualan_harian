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
    $distribution_id = $_POST['distribution_id'] ?? null;
    $report_date = trim($_POST['report_date'] ?? '');
    $total_distributed = (int) ($_POST['total_distributed'] ?? 0);
    $total_bs = (int) ($_POST['total_bs'] ?? 0);
    $price_per = (float) ($_POST['price_per'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if (!$distribution_id) {
        $error = 'Batch distribusi wajib dipilih.';
    } elseif ($report_date === '') {
        $error = 'Tanggal laporan wajib diisi.';
    } elseif ($total_distributed < 0 || $total_bs < 0) {
        $error = 'Jumlah tidak boleh negatif.';
    } elseif ($total_bs > $total_distributed) {
        $error = 'Jumlah BS tidak boleh melebihi total dititipkan.';
    } elseif ($price_per <= 0) {
        $error = 'Harga jual per donat harus lebih dari nol.';
    } else {
        $total_sold = max(0, $total_distributed - $total_bs);
        $total_value = $total_sold * $price_per;

        if ($id) {
            $stmt = $pdo->prepare('UPDATE sales_reports SET distribution_id = ?, report_date = ?, total_distributed = ?, total_bs = ?, price_per = ?, total_value = ?, note = ? WHERE id = ?');
            $stmt->execute([$distribution_id, $report_date, $total_distributed, $total_bs, $price_per, $total_value, $note, $id]);
            $success = 'Laporan penjualan berhasil diperbarui.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO sales_reports (distribution_id, report_date, total_distributed, total_bs, price_per, total_value, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$distribution_id, $report_date, $total_distributed, $total_bs, $price_per, $total_value, $note]);
            $success = 'Laporan penjualan berhasil disimpan.';
        }
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM sales_reports WHERE id = ?')->execute([$_GET['delete']]);
    header('Location: /laporan_penjualan_harian/admin/penjualan.php?success=Laporan+penjualan+berhasil+dihapus');
    exit;
}

$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM sales_reports WHERE id = ? LIMIT 1');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

$distributions = $pdo->query('SELECT d.*, p.batch_name, p.production_date FROM distributions d LEFT JOIN productions p ON p.id = d.production_id ORDER BY d.distribution_date DESC')->fetchAll();
$rows = $pdo->query('SELECT sr.*, d.distribution_date, p.batch_name FROM sales_reports sr LEFT JOIN distributions d ON d.id = sr.distribution_id LEFT JOIN productions p ON p.id = d.production_id ORDER BY sr.report_date DESC, sr.id DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <h2 class="mb-4">Laporan Penjualan</h2>
  <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?php echo (int) $edit['id']; ?>"><?php endif; ?>

        <div class="col-md-4">
          <label class="form-label">Batch distribusi</label>
          <select name="distribution_id" class="form-select" required>
            <option value="">-- Pilih batch --</option>
            <?php foreach ($distributions as $d): ?>
              <option value="<?php echo (int)$d['id']; ?>" <?php echo (($edit['distribution_id'] ?? '') == $d['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars(($d['batch_name'] ?: $d['production_date'] ?: 'Batch') . ' / ' . $d['distribution_date'] . ' / ' . $d['distributed'] . ' pcs'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal pengecekan</label>
          <input type="date" name="report_date" class="form-control" value="<?php echo htmlspecialchars($edit['report_date'] ?? ''); ?>" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Dititipkan</label>
          <input type="number" min="0" step="1" name="total_distributed" class="form-control" value="<?php echo htmlspecialchars($edit['total_distributed'] ?? ''); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Donat BS</label>
          <input type="number" min="0" step="1" name="total_bs" class="form-control" value="<?php echo htmlspecialchars($edit['total_bs'] ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Harga jual per biji</label>
          <input type="number" min="0" step="1" name="price_per" class="form-control" value="<?php echo htmlspecialchars($edit['price_per'] ?? ''); ?>" required>
        </div>
        <div class="col-md-8">
          <label class="form-label">Catatan</label>
          <textarea name="note" class="form-control"><?php echo htmlspecialchars($edit['note'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-pink"><?php echo $edit ? 'Update' : 'Simpan'; ?></button>
          <?php if ($edit): ?><a href="/laporan_penjualan_harian/admin/penjualan.php" class="btn btn-outline-secondary ms-2">Batal</a><?php endif; ?>
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
            <th>Report</th>
            <th>Dititipkan</th>
            <th>BS</th>
            <th>Terjual</th>
            <th>Harga</th>
            <th>Nilai</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['distribution_date'] ?: '-'); ?></td>
              <td><?php echo htmlspecialchars($row['report_date']); ?></td>
              <td><?php echo number_format((int)$row['total_distributed']); ?></td>
              <td><?php echo number_format((int)$row['total_bs']); ?></td>
              <td><?php echo number_format((int)max(0, (int)$row['total_distributed'] - (int)$row['total_bs'])); ?></td>
              <td><?php echo formatRupiah($row['price_per']); ?></td>
              <td><?php echo formatRupiah($row['total_value']); ?></td>
              <td>
                <a href="/laporan_penjualan_harian/admin/penjualan.php?edit=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="/laporan_penjualan_harian/admin/penjualan.php?delete=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus laporan ini?');">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
