<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
$kitchen_id = $_SESSION['kitchen_id'] ?? null;

if (!isset($admin_id) && !isset($kitchen_id)) {
   header('location: admin_login.php');
}

if (isset($_GET['delete'])) {

    $delete_id = $_GET['delete'];
    $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
    $delete_product_image->execute([$delete_id]);
    $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
    unlink('../uploaded_img/' . $fetch_delete_image['image']);
    $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
    $delete_product->execute([$delete_id]);
    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
    $delete_cart->execute([$delete_id]);
    header('location:products.php');
 }

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
    <title>Inventory</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- DataTables JavaScript -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
    .stock-levels-container {
        border: var(--border);
        border-radius: 5px;
        padding: 25px;
    }

    /* Target the table body and set font size */
    #stockTable tbody td {
        font-size: 1.5rem;
        /* Adjust the font size as needed */
    }

    /* Target the table header and set font size */
    #stockTable thead th {
        font-size: 16px;
        /* Adjust the font size as needed */
    }

    /* Style for button links */
    .button-link {
        display: inline-block;
        padding: 8px 12px;
        text-decoration: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f0f0f0;
        color: #333;
        transition: background-color 0.3s, color 0.3s;
    }

    /* Hover effect for button links */
    .button-link:hover {
        background-color: #ddd;
        color: #000;
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

    <?php include '../components/kitchen_header.php' ?>

    <!-- admin dashboard section starts  -->

    <section class="dashboard">
        <h1 class="heading">Inventory</h1>

        <div class="top-products-container">
            <h2 class="title-top-products">Top 3 Products SOLD</h2>
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




        <div class="stock-levels-container">
            <h2>Current Stock Levels</h2>

            <table id="stockTable" class="display">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
        $stock_query = $conn->query("SELECT id, name, quantity_available FROM products");
        while ($row = $stock_query->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                    <td>{$row['name']}</td>
                    <td>{$row['quantity_available']}</td>
                    <td>
                        <a href='update_product.php?update={$row['id']}' class='button-link edit-link'>Edit</a>
                        <a href='inventory.php?delete={$row['id']}' class='button-link delete-link' onclick='return confirm(\"Are you sure you want to delete this product?\")'>Delete</a>
                    </td>

                </tr>";
        }
        ?>
                </tbody>
            </table>

        </div>


    </section>

    <!-- admin dashboard section ends -->
    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>
    <script>
    $(document).ready(function() {
        $('#stockTable').DataTable(); // Initialize DataTable
    });
    </script>
</body>

</html>