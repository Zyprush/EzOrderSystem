<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

include 'components/add_cart.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>EzOrder | Home</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">

</head>

<body>
   <div class="category">
      <img src="images/logo.png" alt="logo" height="400" style="display: block; margin-left: auto; margin-right: auto; margin-top: 50px;">
   </div>

   <section class="category">

      <h1 style="font-size: 5rem; text-align: center; margin-bottom: 2rem; ">
         EzOrder - Restaurant Kiosk System</h1>

      <div class="box-container">

         <a href="kiosk.php" class="box">
            <img src="images/kiosk-icon.png" alt="">
            <h3>Kiosk</h3>
         </a>

         <a href="cashier/dashboard.php" class="box">
            <img src="images/cashier-icon.png" alt="">
            <h3>Cashier</h3>
         </a>

         <a href="kitchen/dashboard.php" class="box">
            <img src="images/kitchen-icon.png" alt="">
            <h3>Kitchen</h3>
         </a>

         <a href="admin/dashboard.php" class="box">
            <img src="images/icon-user.png" alt="">
            <h3>Admin</h3>
         </a>

      </div>

   </section>

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>

</html>