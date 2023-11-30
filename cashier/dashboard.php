<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
$cashier_id = $_SESSION['cashier_id'] ?? null;

if (!isset($admin_id) && !isset($cashier_id)) {
   header('location: admin_login.php');
}

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

if (isset($cashier_id)) {
   $select_cashier = $conn->prepare("SELECT name FROM `cashier` WHERE id = ?");
   $select_cashier->execute([$cashier_id]);
   $fetch_cashier = $select_cashier->fetch(PDO::FETCH_ASSOC);
   $cashier_name = $fetch_cashier['name'];
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

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
    @media print {
        body {
            width: 2.25in;
            /* Adjust other styles as needed */
        }
    }
    </style>
</head>

<body>

    <?php include '../components/cashier_header.php' ?>

    <!-- placed orders section starts  -->

    <section class="placed-orders">

        <h1 class="heading">placed orders</h1>

        <div class="box-container">

            <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = 'pending'");
         $select_orders->execute();
         if ($select_orders->rowCount() > 0) {
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
         ?>
            <div class="box">
                <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
                <p> Mode : <span><?= $fetch_orders['address1']; ?></span> </p>
                <p> Number : <span><?= $fetch_orders['address']; ?></span> </p>
                <p> total products : <span><?= $fetch_orders['total_products']; ?></span> </p>
                <p> total price : <span>₱<?= $fetch_orders['total_price']; ?></span> </p>
                <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
                <form action="" method="POST" onsubmit="return validateForm(this);">
                    <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                    <select name="payment_status" class="drop-down" id="paymentStatus">
                        <option value="" selected disabled><?= $fetch_orders['payment_status']; ?></option>
                        <option value="pending">pending</option>
                        <option value="completed">completed</option>
                    </select>
                    <div class="flex-btn1">
                        <input type="submit" value="update" class="btn" name="update_payment">
                        <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn"
                            onclick="return confirm('delete this order?');">delete</a>
                        <a href="#" class="delete-btn"
                            onclick="printOrder(<?= $fetch_orders['id']; ?>, '<?= $fetch_orders['placed_on']; ?>', '<?= $fetch_orders['address1']; ?>', '<?= $fetch_orders['address']; ?>', '<?= $fetch_orders['total_products']; ?>', '<?= $fetch_orders['total_price']; ?>', '<?= $fetch_orders['method']; ?>', '<?= isset($cashier_name) ? $cashier_name : ''; ?>');">Print</a>

                    </div>
                </form>
            </div>

            <?php
            }
         } else {
            echo '<p class="empty">no orders placed yet!</p>';
         }
         ?>

        </div>

    </section>

    <!-- placed orders section ends -->

    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>
    <script>
    function printOrder(orderId, placedOn, mode, tableNumber, totalProducts, totalPrice, paymentMethod) {
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Kusina ni Pedro Receipt </title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; text-align: center; }');
        printWindow.document.write('h4{ margin: 0; }');
        printWindow.document.write('img { width: 205px; height: auto; margin: 25px; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');

        // Add the order details to the print window
        printWindow.document.write('<img src="../images/kusina_ni_pedro_logo.jpg" alt="Kusina ni Pedro Logo">');
        printWindow.document.write('<h1>Kusina ni Pedro</h1>');
        printWindow.document.write('<h4>Capitol Hills</h4>');
        printWindow.document.write('<h4>Brgy. Payompon, Mamburao</h4>');
        printWindow.document.write('<h4>Occidental Mindoro</h4>');

        printWindow.document.write(
            '<p>Employee: <?php echo isset($cashier_name) ? $cashier_name : ''; ?> </p>');

        printWindow.document.write('<p>--------------------------------------------</p>');

        var dineOrTakeOut = (tableNumber >= 1 && tableNumber <= 10) ? "Dine In" : "Take Out";
        printWindow.document.write('<h4>' + mode + '</h4>');
        printWindow.document.write('<p>--------------------------------------------</p>');

        printWindow.document.write('<p>' + totalProducts + '</p>');

        printWindow.document.write('<h1>Total: ₱' + totalPrice + '</h1>');
        printWindow.document.write('<p>Payment Method: ' + paymentMethod + '</p>');

        printWindow.document.write('<p>Place on: ' + placedOn + '</p>');

        printWindow.document.write('<h5> Thank you, Come Again! <3 </h5>');

        printWindow.document.write('</body></html>');
        printWindow.print();
        printWindow.close();
        return false; // Prevent the default behavior of the "a" tag
    }
    </script>
    <script>
    function validateForm(form) {
        var selectedValue = document.getElementById('paymentStatus').value;

        if (selectedValue === null || selectedValue === '') {
            alert('Please select a payment status.');
            return false; // Prevent form submission if no value is selected
        }

        return true; // Allow form submission if a value is selected
    }
    </script>

</body>

</html>