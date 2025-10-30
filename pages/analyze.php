<?php
require_once '../include/header.php'; 


// Default year
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Generate year options
$yearOptions = [];
$currentYear = date('Y');
for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
    $yearOptions[] = $i;
}
?>

<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-between align-items-center">
                <h1 class="page-header">Sales Analysis - <span id="selectedYear"><?php echo $year; ?></span></h1>

                <div class="form-inline" style="margin-top:15px;">
                    <label for="yearSelect" style="margin-right:10px;">Select Year:</label>
                    <select id="yearSelect" class="form-control">
                        <?php foreach ($yearOptions as $y): ?>
                            <option value="<?php echo $y; ?>" <?php if ($y == $year) echo 'selected'; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row">
            <div class="col-lg-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart"></i> Monthly Sales Comparison
                    </div>
                    <div class="panel-body">
                        <canvas id="barChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-line-chart"></i> Average Monthly Sales Trend
                    </div>
                    <div class="panel-body">
                        <canvas id="lineChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row">
            <div class="col-lg-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-pie-chart"></i> Sales Proportion (Online vs In-Shop)
                    </div>
                    <div class="panel-body">
                        <canvas id="pieChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-trophy"></i> Top 5 Most Sold Products
                    </div>
                    <div class="panel-body">
                        <canvas id="topProductsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div> <!-- /#wrapper -->

<!-- JS -->
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/metisMenu.min.js"></script>
<script src="../js/startmin.js"></script>
<script src="../js/chart.js"></script>

<script>
let barChart, lineChart, pieChart, topProductsChart;

// Render charts inside panels
function renderCharts(data) {
    const ctxBar = document.getElementById('barChart').getContext('2d');
    const ctxLine = document.getElementById('lineChart').getContext('2d');
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    const ctxTop = document.getElementById('topProductsChart').getContext('2d');

    if (barChart) barChart.destroy();
    if (lineChart) lineChart.destroy();
    if (pieChart) pieChart.destroy();
    if (topProductsChart) topProductsChart.destroy();

    // --- Bar Chart (Monthly Sales)
    barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: data.months,
            datasets: [
                {
                    label: 'Online Sales (XAF)',
                    data: data.onlineSales,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'In-Shop Sales (XAF)',
                    data: data.shopSales,
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // --- Line Chart (Average Sales)
    lineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: data.months,
            datasets: [
                {
                    label: 'Avg Online Sale',
                    data: data.onlineAvg,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.2
                },
                {
                    label: 'Avg In-Shop Sale',
                    data: data.shopAvg,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    tension: 0.2
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // --- Pie Chart (Proportion)
    pieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Online', 'In-Shop'],
            datasets: [{
                data: [data.totalOnline, data.totalShop],
                backgroundColor: ['rgba(54, 162, 235, 0.7)', 'rgba(255, 159, 64, 0.7)'],
                borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 159, 64, 1)']
            }]
        },
        options: {
            plugins: { title: { display: true, text: 'Total Sales Proportion' } }
        }
    });

    // --- Top Products Chart
    topProductsChart = new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: data.topProductNames,
            datasets: [{
                label: 'Units Sold',
                data: data.topProductSales,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { title: { display: true, text: 'Top 5 Most Sold Products' } },
            scales: { x: { beginAtZero: true } }
        }
    });
}

// Fetch initial data
function fetchData(year) {
    $.ajax({
        url: '../include/analyze.php',
        type: 'GET',
        data: { year },
        dataType: 'json',
        success: function(response) {
            renderCharts(response);
            $('#selectedYear').text(year);
        },
        error: function() {
            alert('Error loading data.');
        }
    });
}

// Initial fetch
fetchData($('#yearSelect').val());

// Update charts dynamically
$('#yearSelect').on('change', function() {
    fetchData($(this).val());
});
</script>

</body>
</html>
