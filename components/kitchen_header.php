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

        <a href="dashboard.php" class="logo">Kitchen<span>Panel</span></a>

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

            // Fetch profile information for kitchen
            $select_kitchen_profile = $conn->prepare("SELECT * FROM `kitchen` WHERE id = ?");
            $select_kitchen_profile->execute([$kitchen_id]);
            $fetch_kitchen_profile = $select_kitchen_profile->fetch(PDO::FETCH_ASSOC);

            // Check if the admin profile exists
            if ($fetch_admin_profile) {
                $fetch_profile = $fetch_admin_profile;
            } elseif ($fetch_kitchen_profile) {
                $fetch_profile = $fetch_kitchen_profile;
            } else {
                // Handle the case where neither admin nor kitchen profiles are found
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