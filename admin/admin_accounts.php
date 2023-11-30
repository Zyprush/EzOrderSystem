<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['delete']) && isset($_GET['type'])) {
    $delete_id = $_GET['delete'];
    $account_type = $_GET['type'];

    $allowed_account_types = ['admin', 'user', 'cashier', 'kitchen'];

    // Check if the provided account type is valid
    if (in_array($account_type, $allowed_account_types)) {
        $delete_account = $conn->prepare("DELETE FROM `$account_type` WHERE id = ?");
        $delete_account->execute([$delete_id]);

        // Redirect back to the page after deletion
        header('location:admin_accounts.php');
        exit(); // Exit to prevent further execution
    }
}

if (isset($_GET['update']) && isset($_GET['type'])) {
    $update_id = $_GET['update'];
    $account_type = $_GET['type'];

    $allowed_account_types = ['admin', 'user', 'cashier', 'kitchen'];

    // Check if the provided account type is valid
    if (in_array($account_type, $allowed_account_types)) {
        // Redirect to the update profile page with the appropriate parameters
        header("location:update_profile.php?type=$account_type&id=$update_id");
        exit(); // Exit to prevent further execution
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Accounts</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- Admins accounts section starts  -->
    <section class="accounts">
        <h1 class="heading">Accounts</h1>

        <div class="box-container">
            <div class="box">
                <p>Register new admin</p>
                <a href="register_admin.php" class="option-btn">Register</a>
            </div>

            <?php
            $account_types = ['admin', 'cashier', 'kitchen'];

            foreach ($account_types as $account_type) {
                $select_account = $conn->prepare("SELECT * FROM `$account_type`");
                $select_account->execute();

                if ($select_account->rowCount() > 0) {
                    while ($fetch_accounts = $select_account->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <div class="box">
                <p><?= ucfirst($account_type); ?> ID: <span><?= $fetch_accounts['id']; ?></span></p>
                <p>Username: <span><?= $fetch_accounts['name']; ?></span></p>
                <div class="flex-btn">
                    <a href="admin_accounts.php?delete=<?= $fetch_accounts['id']; ?>&type=<?= $account_type; ?>"
                        class="delete-btn" onclick="return confirm('Delete this account?');">Delete</a>

                    <a href="admin_accounts.php?update=<?= $fetch_accounts['name']; ?>&type=<?= $account_type; ?>"
                        class="option-btn">Update</a>
                </div>
            </div>
            <?php
                    }
                } else {
                    echo "<p class='empty'>No $account_type accounts available</p>";
                }
            }
            ?>
        </div>
    </section>
    <!-- Admins accounts section ends -->

    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

</body>

</html>