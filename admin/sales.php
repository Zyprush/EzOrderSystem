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

// Initialize arrays to store labels (months) and sales data
$months = [];
$sales = [];

// Loop through the fetched data and populate the arrays
while ($row = $select_monthly_sales->fetch(PDO::FETCH_ASSOC)) {
    // Get month name from its number
    $monthName = date('M', mktime(0, 0, 0, $row['month'], 1));
    
    // Store month name and sales data in arrays
    $months[] = $monthName;
    $sales[] = $row['monthly_total'];
}

// Prepare the data structure for Chart.js
$salesData = [
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

// Convert $salesData to JSON for use in JavaScript
$salesDataJSON = json_encode($salesData);

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

    .sales-charts {
        border: var(--border);
        border-radius: 5px;
        padding: 20px;
        margin-top: 20px;
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
    </style>
</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- admin dashboard section starts  -->

    <section class="dashboard">

        <h1 class="heading">Sales</h1>


        <div class="sales-summary-container">
            <div class="sales-summary-title">Sales Summary</div>
            <div class="sales-summary">
                <?php
            // Calculate total sales
            $select_total_sales = $conn->prepare("SELECT SUM(total_price) AS total_sales FROM orders WHERE payment_status = 'completed'");
            $select_total_sales->execute();
            $total_sales_data = $select_total_sales->fetch(PDO::FETCH_ASSOC);
            $total_sales = $total_sales_data['total_sales'] ?? 0;
    
            // Calculate today's sales
            $select_today_sales = $conn->prepare("SELECT SUM(total_price) AS today_sales FROM orders WHERE DATE(placed_on) = CURDATE() AND payment_status = 'completed'");
            $select_today_sales->execute();
            $today_sales_data = $select_today_sales->fetch(PDO::FETCH_ASSOC);
            $today_sales = $today_sales_data['today_sales'] ?? 0;
    
            // Calculate weekly sales (past 7 days)
            $select_weekly_sales = $conn->prepare("SELECT SUM(total_price) AS weekly_sales FROM orders WHERE DATE(placed_on) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND payment_status = 'completed'");
            $select_weekly_sales->execute();
            $weekly_sales_data = $select_weekly_sales->fetch(PDO::FETCH_ASSOC);
            $weekly_sales = $weekly_sales_data['weekly_sales'] ?? 0;
            ?>
                <div class="total-box">
                    <h3>Grand Total Sales: <span>₱</span><?= number_format($total_sales, 2); ?><span>.00</span></h3>
                </div>
                <div class="daily-sales">
                    <h3>Daily Sales (Today): <span>₱</span><?= number_format($today_sales, 2); ?><span>.00</span></h3>
                </div>
                <div class="weekly-sales">
                    <h3>Weekly Sales (Past 7 Days):
                        <span>₱</span><?= number_format($weekly_sales, 2); ?><span>.00</span>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Sales Charts/Graphs section -->
        <div class="sales-charts">
            <canvas id="salesChart" width="800" height="400"></canvas>
        </div>

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
        
    </section>

    <!-- admin dashboard section ends -->
    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

    <script>
    // Dummy sales data for demonstration
    const salesData = <?= $salesDataJSON; ?>;

    // Get the canvas element
    const salesChartCanvas = document.getElementById('salesChart').getContext('2d');

    // Create the line chart
    const salesChart = new Chart(salesChartCanvas, {
        type: 'line',
        data: salesData,
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