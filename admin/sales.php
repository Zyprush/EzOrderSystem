<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

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

// Fetch the top 3 products based on sales quantity with 'completed' payment status
$get_top_products = $conn->query("SELECT total_products, SUM(quantity_sold) AS total_quantity_sold 
    FROM orders 
    WHERE payment_status = 'completed'
    GROUP BY total_products 
    ORDER BY total_quantity_sold DESC 
    LIMIT 3");

$top_products = [];
if ($get_top_products->rowCount() > 0) {
    while ($row = $get_top_products->fetch(PDO::FETCH_ASSOC)) {
        // Extracting product name from total_products field
        $productDetails = explode('(', $row['total_products']);
        $productName = trim($productDetails[0]); // Extracting the product name

        // Fetch the image for the product from the 'products' table
        $get_product_image = $conn->prepare("SELECT image FROM products WHERE name = ?");
        $get_product_image->execute([$productName]);
        $imageRow = $get_product_image->fetch(PDO::FETCH_ASSOC);
        $productImage = $imageRow ? $imageRow['image'] : ''; // Get the image path

        $top_products[] = [
            'name' => $productName,
            'quantity_sold' => $row['total_quantity_sold'],
            'image' => $productImage // Store the image path
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

    <!-- Add Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>
    /* Custom CSS styles for sales summary */
    .sales-summary-container {
        border: var(--border);
        border-radius: 5px;
        padding: 20px;
        margin-top: 20px;
    }

    .sales-summary-title {
        font-size: 2.5em;
        font-weight: bold;
        margin-bottom: 15px;
    }

    .sales-summary {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .total-box,
    .daily-sales,
    .weekly-sales {
        width: calc(33.33% - 20px);
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 15px;
        box-sizing: border-box;
        margin-bottom: 20px;
        border-radius: 5px;
    }

    /* Adjust font size */
    .total-box h3,
    .daily-sales h3,
    .weekly-sales h3 {
        font-size: 2.2em;
        margin-bottom: 10px;
    }

    /* Styles for the top products container */
    .top-products-container {
        margin-top: 20px;
        margin-bottom: 20px;
    }

    /* Flexbox styles for product cards */
    .product-cards {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        /* Adjust the gap between cards */
    }

    .product-card {
        flex: 0 0 calc(33.33% - 20px);
        /* Set width for each card */
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    /* Media query for responsive layout */
    @media (max-width: 768px) {
        .product-card {
            flex: 0 0 calc(50% - 20px);
            /* Adjust width for smaller screens */
        }
    }

    .title-top-products {
        font-size: 2.5rem;
        margin-bottom: 15px;
    }

    /* Product card container */
    .product-card {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out;
    }

    .product-card:hover {
        transform: translateY(-5px);
        /* Optional: Apply a subtle hover effect */
    }

    /* Product image */
    .product-image {
        width: 100%;
        max-height: 300px;
        /* Set max height for images */
        overflow: hidden;
        /* Hide overflow if images exceed max height */
        border-radius: 8px;
        margin-bottom: 10px;
        /* Optional: Add margin bottom */
    }

    .product-image img {
        width: 100%;
        height: auto;
        object-fit: cover;
        /* Maintain aspect ratio and cover the container */
        transition: transform 0.3s ease-in-out;
    }

    .product-image:hover img {
        transform: scale(1.1);
        /* Optional: Apply a zoom effect on image hover */
    }

    /* Product details */
    .product-details {
        text-align: center;
    }

    .product-name {
        margin-bottom: 5px;
        font-size: 1.2rem;
        font-weight: bold;
        /* Optional: Make the product name bold */
    }

    .product-sold {
        font-size: 1rem;
        color: #666;
        /* Optional: Adjust the color */
    }

    .sales-container {
        margin: 5px;
        border-radius: 5px;
        border: solid 2px;
        font-size: 1.5rem;
        padding: 20px;
    }

    .sales-charts {
        padding: 20px;
        width: 100%;
        /* Set a fixed width for chart containers */
        height: 400px;
        /* Set a fixed height for chart containers */
    }
    </style>
</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- admin dashboard section starts  -->

    <section class="dashboard">

        <h1 class="heading">Sales</h1>

        <div class="top-products-container">
            <h2 class="title-top-products">Top 3 Products by Sales Quantity</h2>
            <div class="product-cards">
                <?php foreach ($top_products as $product) : ?>
                <div class="product-card">
                    <?php if (!empty($product['image'])) : ?>
                    <div class="product-image">
                        <img src="../uploaded_img/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                    </div>
                    <?php endif; ?>
                    <div class="product-details">
                        <p class="product-name"><?= $product['name'] ?></p>
                        <p class="product-sold">Sold: <?= $product['quantity_sold'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Sales View Selection -->
        <div class="sales-container">
            <label for="salesView">Select Sales View:</label>
            <select id="salesView" onchange="changeSalesView()">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>

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

        </div>
        

    </section>

    <!-- admin dashboard section ends -->
    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

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