<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $expense_date = trim($_POST['expense_date'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $item = trim($_POST['item'] ?? '');
    $quantity = $_POST['quantity'] !== '' ? (int) $_POST['quantity'] : null;
    $unit_price = $_POST['unit_price'] !== '' ? (float) $_POST['unit_price'] : null;
    $total = (float) ($_POST['total'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($expense_date === '') {
        $error = 'Tanggal pengeluaran wajib diisi.';
    } elseif ($total < 0) {
        $error = 'Nominal pengeluaran tidak boleh negatif.';
    } else {
        $pdo->prepare('INSERT INTO expenses (expense_date, category_id, item, quantity, unit_price, total, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())')->execute([
            $expense_date,
            $category_id ?: null,
            $item ?: null,
            $quantity,
            $unit_price,
            $total,
            $note
        ]);
        $success = 'Pengeluaran berhasil disimpan.';
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM expenses WHERE id = ?')->execute([$_GET['delete']]);
    header('Location: /laporan_penjualan_harian/admin/pengeluaran.php?success=Pengeluaran+berhasil+dihapus');
    exit;
}

$categories = $pdo->query('SELECT * FROM expense_categories ORDER BY name ASC')->fetchAll();
$rows = $pdo->query('SELECT e.*, c.name AS category_name FROM expenses e LEFT JOIN expense_categories c ON c.id = e.category_id ORDER BY e.expense_date DESC, e.id DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <h2 class="mb-4">Pengeluaran</h2>
  <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        <div class="col-md-3">
          <label class="form-label">Tanggal</label>
          <input type="date" name="expense_date" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Kategori</label>
          <select name="category_id" class="form-select">
            <option value="">-- Pilih kategori --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Nama barang / keterangan</label>
          <input type="text" name="item" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Total nominal</label>
          <input type="number" min="0" step="1" name="total" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Jumlah barang</label>
          <input type="number" min="0" step="1" name="quantity" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Harga satuan</label>
          <input type="number" min="0" step="1" name="unit_price" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Catatan</label>
          <textarea name="note" class="form-control"></textarea>
        </div>
        <div class="col-12"><button class="btn btn-pink">Simpan pengeluaran</button></div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Tanggal</th>
            <th>Kategori</th>
            <th>Item</th>
            <th>Jumlah</th>
            <th>Total</th>
            <th>Catatan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['expense_date']); ?></td>
              <td><?php echo htmlspecialchars($row['category_name'] ?: '-'); ?></td>
              <td><?php echo htmlspecialchars($row['item'] ?: '-'); ?></td>
              <td><?php echo $row['quantity'] !== null ? number_format((int)$row['quantity']) : '-'; ?></td>
              <td><?php echo formatRupiah($row['total']); ?></td>
              <td><?php echo htmlspecialchars($row['note'] ?: '-'); ?></td>
              <td><a href="/laporan_penjualan_harian/admin/pengeluaran.php?delete=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengeluaran ini?');">Hapus</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
