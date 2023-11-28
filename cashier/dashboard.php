<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
$cashier_id = $_SESSION['cashier_id'] ?? null;

if (!isset($admin_id) && !isset($cashier_id)) {
   header('location: admin_login.php');
}

// Calculate today's date in YYYY-MM-DD format
$today_date = date('Y-m-d');

// Query to retrieve completed orders placed today and sum up their total prices
$select_today_completed_orders = $conn->prepare("SELECT SUM(total_price) AS today_completed_total_sale FROM `orders` WHERE DATE(placed_on) = ? AND payment_status = 'completed'");
$select_today_completed_orders->execute([$today_date]);
$fetch_today_completed_orders = $select_today_completed_orders->fetch(PDO::FETCH_ASSOC);
$today_completed_total_sale = $fetch_today_completed_orders['today_completed_total_sale'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboard</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

    <style>
    .total-box {
        border: var(--border);
        padding: 2rem;
        margin-top: 15px;
        font-size: 2.5rem;
        background-color: var(--white);
        
    }
    </style>

</head>

<body>

    <?php include '../components/cashier_header.php' ?>

    <!-- admin dashboard section starts  -->

    <section class="dashboard">

        <h1 class="heading">dashboard</h1>

        <div class="box-container">

            <div class="box">
                <?php
            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_pendings->execute(['pending']);
            while ($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)) {
               $total_pendings += $fetch_pendings['total_price'];
            }
            ?>
                <h3><span>₱</span><?= $total_pendings; ?><span>/-</span></h3>
                <p>total pendings</p>
                <a href="placed_orders.php" class="btn">see orders</a>
            </div>

            <div class="box">
                <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['completed']);
            while ($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)) {
               $total_completes += $fetch_completes['total_price'];
            }
            ?>
                <h3><span>₱</span><?= $total_completes; ?><span>/-</span></h3>
                <p>total completes</p>
                <a href="placed_orders.php" class="btn">see orders</a>
            </div>

            <div class="box">
                <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            $numbers_of_orders = $select_orders->rowCount();
            ?>
                <h3><?= $numbers_of_orders; ?></h3>
                <p>total orders</p>
                <a href="placed_orders.php" class="btn">see orders</a>
            </div>

            <div class="box">
                <?php
            $select_messages = $conn->prepare("SELECT * FROM `ratings`");
            $select_messages->execute();
            $numbers_of_messages = $select_messages->rowCount();
            ?>
                <h3><?= $numbers_of_messages; ?></h3>
                <p>Ratings</p>
                <a href="ratings.php" class="btn">See Ratings</a>
            </div>

        </div>

        <div class="total-box">
            <h3><span>₱</span><?= $today_completed_total_sale ?? '0'; ?><span>/-</span></h3>
            <p>Today's Total Sale - <?= date('F j, Y'); ?></p>
        </div>

    </section>

    <!-- admin dashboard section ends -->

    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

</body>

</html>