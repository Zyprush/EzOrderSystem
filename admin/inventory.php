<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
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

    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
    

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
    </style>

</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- admin dashboard section starts  -->

    <section class="dashboard">
        <h1 class="heading">Inventory</h1>

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