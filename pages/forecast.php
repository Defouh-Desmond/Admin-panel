<?php
require_once '../include/header.php';

$year = date('Y');
?>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Sales Forecast - <?php echo $year; ?></h1>
            </div>
        </div>

        <!-- Forecast Panels -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-lg-4">
                <div class="panel panel-primary text-center">
                    <div class="panel-heading">Yearly Forecast Total</div>
                    <div class="panel-body">
                        <h3 id="yearlyTotal">XAF 0</h3>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel panel-green text-center">
                    <div class="panel-heading">6-Month Forecast Total</div>
                    <div class="panel-body">
                        <h3 id="sixMonthTotal">XAF 0</h3>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel panel-yellow text-center">
                    <div class="panel-heading">3-Month Forecast Total</div>
                    <div class="panel-body">
                        <h3 id="threeMonthTotal">XAF 0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yearly Forecast Chart -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-primary">
                    <div class="panel-heading"><i class="fa fa-line-chart"></i> Yearly Forecast</div>
                    <div class="panel-body">
                        <canvas id="yearlyChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6-month & 3-month Forecast Charts -->
        <div class="row">
            <div class="col-lg-6">
                <div class="panel panel-green">
                    <div class="panel-heading">6-Month Forecast</div>
                    <div class="panel-body">
                        <canvas id="sixMonthChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel panel-yellow">
                    <div class="panel-heading">3-Month Forecast</div>
                    <div class="panel-body">
                        <canvas id="threeMonthChart" height="120"></canvas>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$.getJSON('../include/forecast.php', { year: <?php echo $year; ?> }, function(data) {
    const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

    // Calculate totals
    const yearlyTotal = data.yearlyForecast.reduce((a,b)=>a+b,0);
    const sixMonthTotal = data.sixMonthForecast.reduce((a,b)=>a+b,0);
    const threeMonthTotal = data.threeMonthForecast.reduce((a,b)=>a+b,0);

    // Update panels
    $('#yearlyTotal').text('XAF ' + yearlyTotal.toLocaleString());
    $('#sixMonthTotal').text('XAF ' + sixMonthTotal.toLocaleString());
    $('#threeMonthTotal').text('XAF ' + threeMonthTotal.toLocaleString());

    // Yearly Chart
    new Chart(document.getElementById('yearlyChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                { label: 'Online Sales Forecast', data: data.yearlyForecast, borderColor: 'rgba(54,162,235,1)', fill: false },
                { label: 'In-Shop Sales Actual', data: data.shopSales, borderColor: 'rgba(255,159,64,1)', fill: false }
            ]
        }
    });

    // 6-Month Chart
    new Chart(document.getElementById('sixMonthChart'), {
        type: 'bar',
        data: { 
            labels: months.slice(0,6), 
            datasets: [{ label:'Online Forecast', data: data.sixMonthForecast, backgroundColor:'rgba(75,192,192,0.7)' }] 
        }
    });

    // 3-Month Chart
    new Chart(document.getElementById('threeMonthChart'), {
        type: 'bar',
        data: { 
            labels: months.slice(0,3), 
            datasets: [{ label:'Online Forecast', data: data.threeMonthForecast, backgroundColor:'rgba(255,206,86,0.7)' }] 
        }
    });
});
</script>

</body>
</html>
