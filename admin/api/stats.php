<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');
$export = $_GET['export'] ?? null;

if ($export === 'csv') {
    $stmt = $pdo->query('SELECT * FROM sales_reports WHERE report_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end) . ' ORDER BY report_date DESC');
    $rows = $stmt->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_penjualan.csv"');
    echo "Tanggal Laporan,Total Dititipkan,Total BS,Total Terjual,Harga per Unit,Total Nilai\n";
    foreach ($rows as $row) {
        echo implode(',', [
            $row['report_date'],
            $row['total_distributed'],
            $row['total_bs'],
            max(0, $row['total_distributed'] - $row['total_bs']),
            $row['price_per'],
            $row['total_value']
        ]) . "\n";
    }
    exit;
}

$reports = $pdo->query('SELECT sr.*, d.distribution_date FROM sales_reports sr LEFT JOIN distributions d ON d.id = sr.distribution_id WHERE sr.report_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end) . ' ORDER BY sr.report_date DESC')->fetchAll();
$expenses = $pdo->query('SELECT * FROM expenses WHERE expense_date BETWEEN ' . $pdo->quote($start) . ' AND ' . $pdo->quote($end) . ' ORDER BY expense_date DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Laporan</h2>
    <div class="d-flex gap-2">
      <a href="/laporan_penjualan_harian/admin/laporan.php?start=<?php echo urlencode($start); ?>&end=<?php echo urlencode($end); ?>&export=csv" class="btn btn-outline-success">Export CSV</a>
      <button class="btn btn-outline-secondary" onclick="window.print()">Cetak</button>
    </div>
  </div>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-3"><input type="date" name="start" class="form-control" value="<?php echo htmlspecialchars($start); ?>"></div>
    <div class="col-md-3"><input type="date" name="end" class="form-control" value="<?php echo htmlspecialchars($end); ?>"></div>
    <div class="col-md-2"><button class="btn btn-pink">Filter</button></div>
  </form>

  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <h5>Penjualan</h5>
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr><th>Tgl Distribusi</th><th>Tgl Report</th><th>Dititipkan</th><th>BS</th><th>Terjual</th><th>Harga</th><th>Total</th></tr>
          </thead>
          <tbody>
            <?php foreach ($reports as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['distribution_date'] ?: '-'); ?></td>
                <td><?php echo htmlspecialchars($row['report_date']); ?></td>
                <td><?php echo number_format((int)$row['total_distributed']); ?></td>
                <td><?php echo number_format((int)$row['total_bs']); ?></td>
                <td><?php echo number_format(max(0, (int)$row['total_distributed'] - (int)$row['total_bs'])); ?></td>
                <td><?php echo formatRupiah($row['price_per']); ?></td>
                <td><?php echo formatRupiah($row['total_value']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
      <h5>Pengeluaran</h5>
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr><th>Tanggal</th><th>Item</th><th>Nominal</th></tr>
          </thead>
          <tbody>
            <?php foreach ($expenses as $e): ?>
              <tr>
                <td><?php echo htmlspecialchars($e['expense_date']); ?></td>
                <td><?php echo htmlspecialchars($e['item'] ?: '-'); ?></td>
                <td><?php echo formatRupiah($e['total']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
