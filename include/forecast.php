<?php
require_once '../classes/connection.php';

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Initialize arrays for monthly sales (1-12)
$onlineSales = array_fill(1, 12, 0);
$shopSales   = array_fill(1, 12, 0);

// Fetch total sales from orders table (paid orders only)
$query = "
    SELECT 
        MONTH(created_at) AS month,
        SUM(total_amount) AS total
    FROM orders
    WHERE YEAR(created_at) = $year
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
";

$result = $mysqli->query($query);

while ($row = $result->fetch_assoc()) {
    $month = intval($row['month']);
    $total = floatval($row['total']);
    // Assume 50% online, 50% in-shop if you don’t track type
    $onlineSales[$month] = round($total * 0.5, 2);
    $shopSales[$month]   = round($total * 0.5, 2);
}

// Forecast function: simple linear regression
function forecastSales($salesData, $monthsAhead = 12) {
    $x = [];
    $y = [];

    foreach ($salesData as $month => $sales) {
        if ($sales > 0) {
            $x[] = $month;
            $y[] = $sales;
        }
    }

    $forecast = [];
    if (count($x) > 1) {
        $n = count($x);
        $x_sum = array_sum($x);
        $y_sum = array_sum($y);
        $xy_sum = 0;
        $x2_sum = 0;

        for ($i = 0; $i < $n; $i++) {
            $xy_sum += $x[$i] * $y[$i];
            $x2_sum += $x[$i] ** 2;
        }

        $slope = ($n * $xy_sum - $x_sum * $y_sum) / ($n * $x2_sum - $x_sum ** 2);
        $intercept = ($y_sum - $slope * $x_sum) / $n;

        $lastMonth = max($x);
        for ($i = 1; $i <= $monthsAhead; $i++) {
            $m = $lastMonth + $i;
            if ($m > 12) $m -= 12;
            $forecast[$m] = round($intercept + $slope * ($lastMonth + $i), 2);
        }
    }

    return $forecast;
}

// Generate forecasts
$yearlyForecast  = forecastSales($onlineSales, 12);
$sixMonthForecast = forecastSales($onlineSales, 6);
$threeMonthForecast = forecastSales($onlineSales, 3);

// Compute overall growth rate
$lastActual = max($onlineSales);
$nextForecast = reset($yearlyForecast);
$growthRate = $lastActual ? round((($nextForecast - $lastActual) / $lastActual) * 100, 1) : 0;

// Prepare JSON
$response = [
    'onlineSales'        => array_values($onlineSales),
    'shopSales'          => array_values($shopSales),
    'yearlyForecast'     => array_values($yearlyForecast),
    'sixMonthForecast'   => array_values($sixMonthForecast),
    'threeMonthForecast' => array_values($threeMonthForecast),
    'growthRate'         => $growthRate
];

header('Content-Type: application/json');
echo json_encode($response);
