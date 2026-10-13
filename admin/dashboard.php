<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$start = !empty($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end = !empty($_GET['end']) ? $_GET['end'] : date('Y-m-d');

$produced = (int) $pdo->query('SELECT COALESCE(SUM(produced),0) FROM productions WHERE production_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end))->fetchColumn();
$distributed = (int) $pdo->query('SELECT COALESCE(SUM(distributed),0) FROM distributions WHERE distribution_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end))->fetchColumn();
$sold = (int) $pdo->query('SELECT COALESCE(SUM(total_sold),0) FROM sales_reports WHERE report_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end))->fetchColumn();
$bs = (int) $pdo->query('SELECT COALESCE(SUM(total_bs),0) FROM sales_reports WHERE report_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end))->fetchColumn();
$salesValue = (float) $pdo->query('SELECT COALESCE(SUM(total_value),0) FROM sales_reports WHERE report_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end))->fetchColumn();
$received = (float) $pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end))->fetchColumn();
$expenses = (float) $pdo->query('SELECT COALESCE(SUM(total),0) FROM expenses WHERE expense_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end))->fetchColumn();
$opening = (float) $pdo->query('SELECT COALESCE(SUM(opening_balance),0) FROM cash_openings WHERE opening_date <= ' . $pdo->quote($end))->fetchColumn();
$cash = $opening + $received - $expenses;

include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Dashboard</h2>
    <form class="row g-2" method="get">
      <div class="col-auto"><input type="date" name="start" class="form-control" value="<?php echo htmlspecialchars($start); ?>"></div>
      <div class="col-auto"><input type="date" name="end" class="form-control" value="<?php echo htmlspecialchars($end); ?>"></div>
      <div class="col-auto"><button class="btn btn-pink">Filter</button></div>
    </form>
  </div>

  <div class="row g-4">
    <div class="col-md-3"><div class="card summary-card"><div class="card-body"><small>Total Produksi</small><h4 class="mt-2"><?php echo number_format($produced); ?> pcs</h4></div></div></div>
    <div class="col-md-3"><div class="card summary-card"><div class="card-body"><small>Total Didistribusikan</small><h4 class="mt-2"><?php echo number_format($distributed); ?> pcs</h4></div></div></div>
    <div class="col-md-3"><div class="card summary-card"><div class="card-body"><small>Total Terjual</small><h4 class="mt-2"><?php echo number_format($sold); ?> pcs</h4></div></div></div>
    <div class="col-md-3"><div class="card summary-card"><div class="card-body"><small>Donat BS</small><h4 class="mt-2"><?php echo number_format($bs); ?> pcs</h4></div></div></div>

    <div class="col-md-4"><div class="card summary-card"><div class="card-body"><small>Nilai Penjualan</small><h4 class="mt-2"><?php echo formatRupiah($salesValue); ?></h4></div></div></div>
    <div class="col-md-4"><div class="card summary-card"><div class="card-body"><small>Setoran Diterima</small><h4 class="mt-2"><?php echo formatRupiah($received); ?></h4></div></div></div>
    <div class="col-md-4"><div class="card summary-card"><div class="card-body"><small>Pengeluaran</small><h4 class="mt-2"><?php echo formatRupiah($expenses); ?></h4></div></div></div>

    <div class="col-12"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5>Saldo Kas</h5><h3 class="mb-0"><?php echo formatRupiah($cash); ?></h3><small class="text-muted">Saldo awal: <?php echo formatRupiah($opening); ?></small></div></div></div>
  </div>

  <div class="mt-4 card border-0 shadow-sm rounded-4">
    <div class="card-body">
      <canvas id="salesChart"></canvas>
    </div>
  </div>
</div>
<script>
fetch('/laporan_penjualan_harian/admin/api/stats.php?start=<?php echo urlencode($start); ?>&end=<?php echo urlencode($end); ?>')
.then(res => res.json())
.then(data => {
  if (!data.labels || !data.labels.length) {
    document.getElementById('salesChart').insertAdjacentHTML('beforeend', '<p class="text-muted mb-0">Belum ada data untuk periode ini.</p>');
    return;
  }
  new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Nilai Penjualan',
        data: data.values,
        borderColor: '#ff7bac',
        backgroundColor: 'rgba(255,123,188,0.15)',
        tension: 0.4,
        fill: true
      }]
    },
    options: { responsive: true }
  });
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
