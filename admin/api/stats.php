<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

global $pdo;

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');

$stmt = $pdo->prepare('SELECT report_date, COALESCE(SUM(total_value), 0) AS total FROM sales_reports WHERE report_date BETWEEN ? AND ? GROUP BY report_date ORDER BY report_date ASC');
$stmt->execute([$start, $end]);
$rows = $stmt->fetchAll();

$labels = [];
$values = [];
foreach ($rows as $row) {
    $labels[] = $row['report_date'];
    $values[] = (float) $row['total'];
}

header('Content-Type: application/json');
echo json_encode(['labels' => $labels, 'values' => $values]);
