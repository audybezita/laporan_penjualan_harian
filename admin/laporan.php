<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $opening_date = trim($_POST['opening_date'] ?? '');
    $opening_balance = (float) ($_POST['opening_balance'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($opening_date === '') {
        $error = 'Tanggal saldo awal wajib diisi.';
    } elseif ($opening_balance < 0) {
        $error = 'Saldo awal tidak boleh negatif.';
    } else {
        $pdo->prepare('INSERT INTO cash_openings (opening_date, opening_balance, note, created_at) VALUES (?, ?, ?, NOW())')->execute([$opening_date, $opening_balance, $note]);
        $success = 'Saldo awal kas berhasil disimpan.';
    }
}

$opening_total = (float) $pdo->query('SELECT COALESCE(SUM(opening_balance),0) FROM cash_openings')->fetchColumn();
$received_total = (float) $pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments')->fetchColumn();
$expense_total = (float) $pdo->query('SELECT COALESCE(SUM(total),0) FROM expenses')->fetchColumn();
$cash_balance = $opening_total + $received_total - $expense_total;
$openings = $pdo->query('SELECT * FROM cash_openings ORDER BY opening_date DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <h2 class="mb-4">Keuangan & Saldo Kas</h2>
  <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card summary-card"><div class="card-body"><small>Saldo Awal</small><h4><?php echo formatRupiah($opening_total); ?></h4></div></div></div>
    <div class="col-md-3"><div class="card summary-card"><div class="card-body"><small>Penerimaan Kas</small><h4><?php echo formatRupiah($received_total); ?></h4></div></div></div>
    <div class="col-md-3"><div class="card summary-card"><div class="card-body"><small>Pengeluaran Kas</small><h4><?php echo formatRupiah($expense_total); ?></h4></div></div></div>
    <div class="col-md-3"><div class="card summary-card"><div class="card-body"><small>Saldo Kas</small><h4><?php echo formatRupiah($cash_balance); ?></h4></div></div></div>
  </div>

  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        <div class="col-md-4"><label class="form-label">Tanggal saldo awal</label><input type="date" name="opening_date" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Saldo awal kas</label><input type="number" min="0" step="1" name="opening_balance" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Catatan</label><input type="text" name="note" class="form-control"></div>
        <div class="col-12"><button class="btn btn-pink">Simpan saldo awal</button></div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Tanggal</th><th>Saldo awal</th><th>Catatan</th></tr>
        </thead>
        <tbody>
          <?php foreach ($openings as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['opening_date']); ?></td>
              <td><?php echo formatRupiah($row['opening_balance']); ?></td>
              <td><?php echo htmlspecialchars($row['note'] ?: '-'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
