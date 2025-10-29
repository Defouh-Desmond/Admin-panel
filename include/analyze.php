<?php
require_once '../classes/connection.php';

header('Content-Type: application/json');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

function getMonthlyData($mysqli, $table, $year, $isOnline = false) {
    $query = "
        SELECT 
            MONTH(created_at) AS month,
            SUM(total_amount) AS total_sales,
            AVG(total_amount) AS avg_sale
        FROM $table
        " . ($isOnline ? "WHERE payment_status = 'paid' AND YEAR(created_at) = $year" : "WHERE YEAR(created_at) = $year") . "
        GROUP BY MONTH(created_at)
        ORDER BY MONTH(created_at)";
    $result = $mysqli->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['month']] = $row;
    }
    return $data;
}

// Fetch data
$onlineMonthly = getMonthlyData($mysqli, 'orders', $year, true);
$shopMonthly = getMonthlyData($mysqli, 'sales', $year);

$months = [];
$onlineSales = [];
$shopSales = [];
$onlineAvg = [];
$shopAvg = [];

for ($m = 1; $m <= 12; $m++) {
    $months[] = date('F', mktime(0, 0, 0, $m, 1, $year));
    $onlineSales[] = isset($onlineMonthly[$m]) ? $onlineMonthly[$m]['total_sales'] : 0;
    $shopSales[] = isset($shopMonthly[$m]) ? $shopMonthly[$m]['total_sales'] : 0;
    $onlineAvg[] = isset($onlineMonthly[$m]) ? $onlineMonthly[$m]['avg_sale'] : 0;
    $shopAvg[] = isset($shopMonthly[$m]) ? $shopMonthly[$m]['avg_sale'] : 0;
}

$totalOnline = array_sum($onlineSales);
$totalShop = array_sum($shopSales);

// Top products
$topProductsQuery = "
    SELECT p.name, SUM(t.total_qty) AS total_sold
    FROM (
        SELECT product_id, SUM(quantity) AS total_qty FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE YEAR(o.created_at) = $year AND o.payment_status = 'paid'
        GROUP BY product_id
        UNION ALL
        SELECT product_id, SUM(quantity) AS total_qty FROM sale_items si
        JOIN sales s ON si.sale_id = s.sale_id
        WHERE YEAR(s.created_at) = $year
        GROUP BY product_id
    ) AS t
    JOIN products p ON t.product_id = p.product_id
    GROUP BY p.name
    ORDER BY total_sold DESC
    LIMIT 5;
";

$topProducts = $mysqli->query($topProductsQuery);
$topProductNames = [];
$topProductSales = [];

if ($topProducts && $topProducts->num_rows > 0) {
    while ($row = $topProducts->fetch_assoc()) {
        $topProductNames[] = $row['name'];
        $topProductSales[] = $row['total_sold'];
    }
}

echo json_encode([
    'months' => $months,
    'onlineSales' => $onlineSales,
    'shopSales' => $shopSales,
    'onlineAvg' => $onlineAvg,
    'shopAvg' => $shopAvg,
    'totalOnline' => $totalOnline,
    'totalShop' => $totalShop,
    'topProductNames' => $topProductNames,
    'topProductSales' => $topProductSales
]);
?>
