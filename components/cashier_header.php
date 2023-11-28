<?php
if (isset($message)) {
    foreach ($message as $message) {
        echo '
      <div class="message">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
    }
}
?>

<header class="header">

    <section class="flex">

        <a href="dashboard.php" class="logo">Cashier<span>Panel</span></a>

        <nav class="navbar">
            <a href="dashboard.php">Home</a>
            <a href="placed_orders.php">Orders</a>
        </nav>

        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <div class="profile">
            <?php
            // Fetch profile information for admin
            $select_admin_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
            $select_admin_profile->execute([$admin_id]);
            $fetch_admin_profile = $select_admin_profile->fetch(PDO::FETCH_ASSOC);

            // Fetch profile information for cashier
            $select_cashier_profile = $conn->prepare("SELECT * FROM `cashier` WHERE id = ?");
            $select_cashier_profile->execute([$cashier_id]);
            $fetch_cashier_profile = $select_cashier_profile->fetch(PDO::FETCH_ASSOC);

            // Check if the admin profile exists
            if ($fetch_admin_profile) {
                $fetch_profile = $fetch_admin_profile;
            } elseif ($fetch_cashier_profile) {
                $fetch_profile = $fetch_cashier_profile;
            } else {
                // Handle the case where none of the profiles are found
                // You may redirect or display an error message
                echo "Profile not found.";
            }
        ?>

            <p><?= $fetch_profile['name']; ?></p>
            <a href="../components/admin_logout.php" onclick="return confirm('logout from this website?');"
                class="delete-btn">logout</a>
        </div>

    </section>

</header>