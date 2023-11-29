<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
};

if (isset($_POST['update_payment'])) {

   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_status->execute([$payment_status, $order_id]);
   $message[] = 'payment status updated!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

// Get today's date
$currentDate = date('Y-m-d');

// Check if 'show_all' button is clicked
if (isset($_GET['show_all'])) {
    $select_orders = $conn->prepare("SELECT * FROM `orders`");
    $select_orders->execute();
} else {
    $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE DATE(placed_on) = ?");
    $select_orders->execute([$currentDate]);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>placed orders</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">

    <!-- jQuery -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js">
    </script>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

    <style>
    .orders-table {
        border: var(--border);
        border-radius: 5px;
        padding: 25px;
    }

    /* Target the table body and set font size */
    #ordersDataTable tbody td {
        font-size: 1.5rem;
        /* Adjust the font size as needed */
    }

    /* Target the table header and set font size */
    #ordersDataTable thead th {
        font-size: 16px;
        /* Adjust the font size as needed */
    }
    </style>

</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- placed orders section starts  -->

    <section class="placed-orders">
        <h1 class="heading">Placed Orders</h1>

        <div class="box-container">

            <?php
            if ($select_orders->rowCount() > 0) {
                while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <div class="box">
                <!-- Display order details -->
                <p>Placed on: <span><?= $fetch_orders['placed_on']; ?></span></p>
                <p>Table Number: <span><?= $fetch_orders['address']; ?></span></p>
                <p>Total Products: <span><?= $fetch_orders['total_products']; ?></span></p>
                <p>Total Price: <span>₱<?= $fetch_orders['total_price']; ?></span></p>
                <p>Payment Method: <span><?= $fetch_orders['method']; ?></span></p>
                <form action="" method="POST">
                    <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                    <select name="payment_status" class="drop-down">
                        <option value="" selected disabled><?= $fetch_orders['payment_status']; ?></option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                    <div class="flex-btn">
                        <input type="submit" value="Update" class="btn" name="update_payment">
                        <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn"
                            onclick="return confirm('Delete this order?');">Delete</a>
                    </div>
                </form>
            </div>
            <?php
                }
            } else {
                echo '<p class="empty">No orders placed today!</p>';
            }
            ?>
        </div>
    </section>

    <section class="orders-table">
        <h2>All Orders</h2>
        <table id="ordersDataTable" class="display">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Placed On</th>
                    <th>Table Number</th>
                    <th>Total Products</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $all_orders = $conn->prepare("SELECT * FROM `orders`");
                $all_orders->execute();

                if ($all_orders->rowCount() > 0) {
                    while ($order_row = $all_orders->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$order_row['id']}</td>";
                        echo "<td>{$order_row['placed_on']}</td>";
                        echo "<td>{$order_row['address']}</td>";
                        echo "<td>{$order_row['total_products']}</td>";
                        echo "<td>₱{$order_row['total_price']}</td>";
                        echo "<td>{$order_row['method']}</td>";
                        echo "<td>{$order_row['payment_status']}</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </section>

    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

    <script>
    $(document).ready(function() {
        $('#ordersDataTable').DataTable();
    });
    </script>

</body>

</html>