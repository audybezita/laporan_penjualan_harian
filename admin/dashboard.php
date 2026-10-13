<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Ambil filter tanggal dari GET
$start = !empty($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end = !empty($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Validasi sederhana tanggal
$start_dt = DateTime::createFromFormat('Y-m-d', $start);
$end_dt = DateTime::createFromFormat('Y-m-d', $end);
if (!$start_dt || !$end_dt) {
    $start = date('Y-m-01');
    $end = date('Y-m-d');
}

// Hitung ringkasan
// total produksi
$stmt = $pdo->prepare('SELECT COALESCE(SUM(produced),0) as total FROM productions WHERE production_date BETWEEN ? AND ?');
$stmt->execute([$start, $end]);
$total_produced = $stmt->fetchColumn();

// total distributed
$stmt = $pdo->prepare('SELECT COALESCE(SUM(distributed),0) as total FROM distributions WHERE distribution_date BETWEEN ? AND ?');
$stmt->execute([$start, $end]);
$total_distributed = $stmt->fetchColumn();

// total sales sold (terjual) from sales_reports
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total_sold),0) FROM sales_reports WHERE report_date BETWEEN ? AND ?');
$stmt->execute([$start, $end]);
$total_sold = $stmt->fetchColumn();

// total BS
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total_bs),0) FROM sales_reports WHERE report_date BETWEEN ? AND ?');
$stmt->execute([$start, $end]);
$total_bs = $stmt->fetchColumn();

// total nilai penjualan
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total_value),0) FROM sales_reports WHERE report_date BETWEEN ? AND ?');
$stmt->execute([$start, $end]);
$total_value = $stmt->fetchColumn();

// total pembayaran diterima
$stmt = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date BETWEEN ? AND ?');
$stmt->execute([$start, $end]);
total_received = $stmt->fetchColumn();

// total pengeluaran
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total),0) FROM expenses WHERE expense_date BETWEEN ? AND ?');
$stmt->execute([$start, $end]);
$total_expenses = $stmt->fetchColumn();

// saldo kas: ambil saldo awal (last opening before start) lalu tambahkan penerimaan - pengeluaran di periode
$stmt = $pdo->prepare('SELECT opening_balance FROM cash_openings WHERE opening_date <= ? ORDER BY opening_date DESC LIMIT 1');
$stmt->execute([$start]);
$opening_balance = $stmt->fetchColumn();
if ($opening_balance === false) $opening_balance = 0;

$saldo = $opening_balance + $total_received - $total_expenses;

?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<h1 class="mb-4">Dashboard</h1>
<form class="row g-2 mb-4" method="get">
  <div class="col-auto">
    <input type="date" name="start" value="<?php echo htmlspecialchars($start); ?>" class="form-control">
  </div>
  <div class="col-auto">
    <input type="date" name="end" value="<?php echo htmlspecialchars($end); ?>" class="form-control">
  </div>
  <div class="col-auto">
    <button class="btn btn-pink">Terapkan</button>
  </div>
</form>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card summary-card">
      <div class="card-body">
        <small>Total Produksi</small>
        <h5><?php echo number_format($total_produced); ?> pcs</h5>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card summary-card">
      <div class="card-body">
        <small>Total Didistribusikan</small>
        <h5><?php echo number_format($total_distributed); ?> pcs</h5>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card summary-card">
      <div class="card-body">
        <small>Total Terjual</small>
        <h5><?php echo number_format($total_sold); ?> pcs</h5>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card summary-card">
      <div class="card-body">
        <small>Total Donat BS</small>
        <h5><?php echo number_format($total_bs); ?> pcs</h5>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card summary-card">
      <div class="card-body">
        <small>Total Nilai Penjualan</small>
        <h5><?php echo formatRupiah($total_value); ?></h5>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card summary-card">
      <div class="card-body">
        <small>Total Diterima</small>
        <h5><?php echo formatRupiah($total_received); ?></h5>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card summary-card">
      <div class="card-body">
        <small>Total Pengeluaran</small>
        <h5><?php echo formatRupiah($total_expenses); ?></h5>
      </div>
    </div>
  </div>

  <div class="col-md-12">
    <div class="card p-3">
      <h6>Saldo Kas (Periode)</h6>
      <h3><?php echo formatRupiah($saldo); ?></h3>
      <small class="text-muted">Saldo awal: <?php echo formatRupiah($opening_balance); ?></small>
    </div>
  </div>
</div>

<div class="mt-4">
  <canvas id="chartSales"></canvas>
</div>

<script>
// Chart akan mengambil data dari endpoint API jika tersedia
fetch('/laporan_penjualan_harian/admin/api/stats.php?start=<?php echo urlencode($start); ?>&end=<?php echo urlencode($end); ?>')
  .then(r => r.json()).then(data => {
    if (!data || !data.labels) return;
    const ctx = document.getElementById('chartSales');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: data.labels,
        datasets: [{
          label: 'Nilai Penjualan',
          borderColor: '#ff7bac',
          backgroundColor: 'rgba(255,123,188,0.1)',
          data: data.values
        }]
      }
    });
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
