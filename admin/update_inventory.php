<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if (isset($_POST['update'])) {

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $quantity = $_POST['quantity'];
   $quantity = filter_var($quantity, FILTER_SANITIZE_STRING);
   $unit = $_POST['unit'];

   $update_product = $conn->prepare("UPDATE `ingredients` SET name = ?, quantity = ?, unit = ? WHERE id = ?");
   $update_product->execute([$name, $quantity, $unit, $pid]);

   $message[] = 'product updated!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>update Inventory</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- update product section starts  -->

    <section class="update-product">

        <h1 class="heading">Update Inventory</h1>

        <?php
      $update_id = $_GET['update'];
      $show_products = $conn->prepare("SELECT * FROM `ingredients` WHERE id = ?");
      $show_products->execute([$update_id]);
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
   ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
            <span>update name</span>
            <input type="text" required placeholder="enter item name" name="name" maxlength="100" class="box"
                value="<?= $fetch_products['name']; ?>">
            <span>update quantity</span>
            <input type="number" min="0" max="9999999999" required placeholder="enter item quantity" name="quantity"
                onkeypress="if(this.value.length == 10) return false;" class="box"
                value="<?= $fetch_products['quantity']; ?>">
            <span>update unit</span>
            <select name="unit" class="box" required>
                <option selected value="<?= $fetch_products['unit']; ?>"><?= $fetch_products['unit']; ?>
                </option>
                <option value="kg">kg</option>
                <option value="liters">liters</option>
                <option value="grams">grams</option>
                <option value="pieces">pieces</option>
            </select>
            <div class="flex-btn">
                <input type="submit" value="update" class="btn" name="update">
                <a href="products.php" class="option-btn">go back</a>
            </div>
        </form>
        <?php
         }
      }else{
         echo '<p class="empty">no items added yet!</p>';
      }
   ?>

    </section>

    <!-- update product section ends -->
    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

</body>

</html>