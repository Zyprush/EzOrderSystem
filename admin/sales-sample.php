<?php
include '../components/connect.php';

// Fetch monthly sales data from 'orders' table for completed orders
$select_monthly_sales = $conn->prepare("SELECT MONTH(placed_on) AS month, SUM(total_price) AS monthly_total 
    FROM `orders` WHERE payment_status = 'completed' GROUP BY MONTH(placed_on)");
$select_monthly_sales->execute();

// Fetch daily sales data from 'orders' table for completed orders
$select_daily_sales = $conn->prepare("SELECT DAYNAME(placed_on) AS day_name, SUM(total_price) AS daily_total 
    FROM `orders` WHERE payment_status = 'completed' GROUP BY DAYNAME(placed_on)");
$select_daily_sales->execute();

// Fetch weekly sales data from 'orders' table for completed orders
$select_weekly_sales = $conn->prepare("SELECT FLOOR(DATEDIFF(placed_on, (SELECT MIN(placed_on) FROM orders)) / 7) AS week_number, SUM(total_price) AS weekly_total 
    FROM `orders` WHERE payment_status = 'completed' GROUP BY week_number");
$select_weekly_sales->execute();

// Fetch yearly sales data from 'orders' table for completed orders
$select_yearly_sales = $conn->prepare("SELECT YEAR(placed_on) AS year, SUM(total_price) AS yearly_total 
    FROM `orders` WHERE payment_status = 'completed' GROUP BY YEAR(placed_on)");
$select_yearly_sales->execute();

// Initialize arrays to store labels (months, days, weeks, and years) and sales data
$months = [];
$sales = [];
$days = [];
$dailySales = [];
$weeks = [];
$weeklySales = [];
$years = [];
$yearlySales = [];

// Loop through the fetched monthly data and populate the arrays
while ($row = $select_monthly_sales->fetch(PDO::FETCH_ASSOC)) {
    // Get month name from its number
    $monthName = date('M', mktime(0, 0, 0, $row['month'], 1));
    
    // Store month name and sales data in arrays
    $months[] = $monthName;
    $sales[] = $row['monthly_total'];
}

// Loop through the fetched daily data and populate the arrays
while ($row = $select_daily_sales->fetch(PDO::FETCH_ASSOC)) {
    // Get day name
    $dayName = $row['day_name'];
    
    // Store day name and daily sales data in arrays
    $days[] = $dayName;
    $dailySales[] = $row['daily_total'];
}

// Loop through the fetched weekly data and populate the arrays
while ($row = $select_weekly_sales->fetch(PDO::FETCH_ASSOC)) {
    // Get week number
    $weekNumber = $row['week_number'];
    
    // Store week number and weekly sales data in arrays
    $weeks[] = "Week " . ($weekNumber + 1);
    $weeklySales[] = $row['weekly_total'];
}

// Loop through the fetched yearly data and populate the arrays
while ($row = $select_yearly_sales->fetch(PDO::FETCH_ASSOC)) {
    // Get year
    $year = $row['year'];
    
    // Store year and yearly sales data in arrays
    $years[] = $year;
    $yearlySales[] = $row['yearly_total'];
}

// Prepare the data structure for Chart.js for monthly sales
$monthlySalesData = [
    'labels' => $months,
    'datasets' => [
        [
            'label' => 'Monthly Sales',
            'data' => $sales,
            'borderColor' => 'rgba(75, 192, 192, 1)',
            'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
            'borderWidth' => 1
        ]
    ]
];

// Prepare the data structure for Chart.js for daily sales
$dailySalesData = [
    'labels' => $days,
    'datasets' => [
        [
            'label' => 'Daily Sales',
            'data' => $dailySales,
            'borderColor' => 'rgba(192, 75, 192, 1)',
            'backgroundColor' => 'rgba(192, 75, 192, 0.2)',
            'borderWidth' => 1
        ]
    ]
];

// Prepare the data structure for Chart.js for weekly sales
$weeklySalesData = [
    'labels' => $weeks,
    'datasets' => [
        [
            'label' => 'Weekly Sales',
            'data' => $weeklySales,
            'borderColor' => 'rgba(192, 192, 75, 1)',
            'backgroundColor' => 'rgba(192, 192, 75, 0.2)',
            'borderWidth' => 1
        ]
    ]
];

// Prepare the data structure for Chart.js for yearly sales
$yearlySalesData = [
    'labels' => $years,
    'datasets' => [
        [
            'label' => 'Yearly Sales',
            'data' => $yearlySales,
            'borderColor' => 'rgba(75, 192, 75, 1)',
            'backgroundColor' => 'rgba(75, 192, 75, 0.2)',
            'borderWidth' => 1
        ]
    ]
];

// Convert data to JSON for use in JavaScript
$monthlySalesDataJSON = json_encode($monthlySalesData);
$dailySalesDataJSON = json_encode($dailySalesData);
$weeklySalesDataJSON = json_encode($weeklySalesData);
$yearlySalesDataJSON = json_encode($yearlySalesData);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Add Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <title>Sales</title>

    <style>
    .sales-container {
        margin: 20px;
    }

    .sales-charts {
        border-radius: 5px;
        border: solid 2px;
        padding: 20px;
        width: 96%;
        /* Set a fixed width for chart containers */
        height: auto;
        /* Set a fixed height for chart containers */
    }
    </style>
</head>

<body>
    <!-- Sales View Selection -->
    <div class="sales-container">
        <label for="salesView">Select Sales View:</label>
        <select id="salesView" onchange="changeSalesView()">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
        </select>
    </div>

    <!-- Daily Sales Chart -->
    <div class="sales-charts" id="dailyChartContainer" style="display: block; ">
        <canvas id="dailySalesChart" width="800" height="400"></canvas>
    </div>

    <!-- Weekly Sales Chart -->
    <div class="sales-charts" id="weeklyChartContainer" style="display: none;">
        <canvas id="weeklySalesChart" width="800" height="400"></canvas>
    </div>

    <!-- Monthly Sales Chart -->
    <div class="sales-charts" id="monthlyChartContainer" style="display: none;">
        <canvas id="monthlySalesChart" width="800" height="400"></canvas>
    </div>

    <!-- Yearly Sales Chart -->
    <div class="sales-charts" id="yearlyChartContainer" style="display: none;">
        <canvas id="yearlySalesChart" width="800" height="400"></canvas>
    </div>

    <script>
    function changeSalesView() {
        var salesView = document.getElementById('salesView').value;

        // Hide all chart containers
        document.getElementById('dailyChartContainer').style.display = 'none';
        document.getElementById('weeklyChartContainer').style.display = 'none';
        document.getElementById('monthlyChartContainer').style.display = 'none';
        document.getElementById('yearlyChartContainer').style.display = 'none';

        // Display the selected chart container based on the chosen sales view
        document.getElementById(salesView + 'ChartContainer').style.display = 'block';
    }


    // Monthly Sales Data
    const monthlySalesData = <?= $monthlySalesDataJSON; ?>;
    const monthlySalesChartCanvas = document.getElementById('monthlySalesChart').getContext('2d');
    const monthlySalesChart = new Chart(monthlySalesChartCanvas, {
        type: 'line',
        data: monthlySalesData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Daily Sales Data
    const dailySalesData = <?= $dailySalesDataJSON; ?>;
    const dailySalesChartCanvas = document.getElementById('dailySalesChart').getContext('2d');
    const dailySalesChart = new Chart(dailySalesChartCanvas, {
        type: 'line',
        data: dailySalesData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Weekly Sales Data
    const weeklySalesData = <?= $weeklySalesDataJSON; ?>;
    const weeklySalesChartCanvas = document.getElementById('weeklySalesChart').getContext('2d');
    const weeklySalesChart = new Chart(weeklySalesChartCanvas, {
        type: 'line',
        data: weeklySalesData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Yearly Sales Data
    const yearlySalesData = <?= $yearlySalesDataJSON; ?>;
    const yearlySalesChartCanvas = document.getElementById('yearlySalesChart').getContext('2d');
    const yearlySalesChart = new Chart(yearlySalesChartCanvas, {
        type: 'line',
        data: yearlySalesData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
</body>

</html>