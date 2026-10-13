<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $sales_report_id = $_POST['sales_report_id'] ?? null;
    $payment_date = trim($_POST['payment_date'] ?? '');
    $amount = (float) ($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if (!$sales_report_id) {
        $error = 'Laporan penjualan harus dipilih.';
    } elseif ($payment_date === '') {
        $error = 'Tanggal penerimaan wajib diisi.';
    } elseif ($amount <= 0) {
        $error = 'Nominal setoran harus lebih dari 0.';
    } else {
        $stmt = $pdo->prepare('SELECT total_value FROM sales_reports WHERE id = ? LIMIT 1');
        $stmt->execute([$sales_report_id]);
        $report = $stmt->fetch();
        if (!$report) {
            $error = 'Laporan tidak ditemukan.';
        } else {
            $paid = (float) $pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE sales_report_id = ' . (int)$sales_report_id)->fetchColumn();
            if ($paid + $amount > (float)$report['total_value'] + 0.01) {
                $error = 'Total setoran tidak boleh melebihi nilai tagihan terkait.';
            } else {
                $pdo->prepare('INSERT INTO payments (sales_report_id, payment_date, amount, note, created_at) VALUES (?, ?, ?, ?, NOW())')->execute([$sales_report_id, $payment_date, $amount, $note]);
                $success = 'Setoran berhasil dicatat.';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM payments WHERE id = ?')->execute([$_GET['delete']]);
    header('Location: /laporan_penjualan_harian/admin/setoran.php?success=Setoran+berhasil+dihapus');
    exit;
}

$reports = $pdo->query('SELECT sr.*, d.distribution_date, (SELECT COALESCE(SUM(amount),0) FROM payments WHERE sales_report_id = sr.id) AS paid FROM sales_reports sr LEFT JOIN distributions d ON d.id = sr.distribution_id ORDER BY sr.report_date DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <h2 class="mb-4">Penerimaan Setoran</h2>
  <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        <div class="col-md-4">
          <label class="form-label">Laporan penjualan</label>
          <select name="sales_report_id" class="form-select" required>
            <option value="">-- Pilih laporan --</option>
            <?php foreach ($reports as $r): ?>
              <option value="<?php echo (int)$r['id']; ?>"><?php echo htmlspecialchars(($r['distribution_date'] ?: 'Batch') . ' / ' . $r['report_date'] . ' / ' . formatRupiah($r['total_value'])); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal setoran</label>
          <input type="date" name="payment_date" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Nominal</label>
          <input type="number" min="0" step="1" name="amount" class="form-control" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Catatan</label>
          <textarea name="note" class="form-control"></textarea>
        </div>
        <div class="col-12"><button class="btn btn-pink">Simpan setoran</button></div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Laporan</th>
            <th>Total Tagihan</th>
            <th>Sudah Diterima</th>
            <th>Sisa</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reports as $r): $paid = (float)$r['paid']; $due = max(0, (float)$r['total_value'] - $paid); $status = $due <= 0 ? 'Lunas' : ($paid > 0 ? 'Dibayar Sebagian' : 'Belum Dibayar'); ?>
            <tr>
              <td><?php echo htmlspecialchars($r['report_date']); ?></td>
              <td><?php echo formatRupiah($r['total_value']); ?></td>
              <td><?php echo formatRupiah($paid); ?></td>
              <td><?php echo formatRupiah($due); ?></td>
              <td><span class="badge bg-<?php echo $status === 'Lunas' ? 'success' : ($status === 'Dibayar Sebagian' ? 'warning' : 'secondary'); ?>"><?php echo $status; ?></span></td>
              <td>
                <a href="/laporan_penjualan_harian/admin/setoran.php?delete=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data setoran?');">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
