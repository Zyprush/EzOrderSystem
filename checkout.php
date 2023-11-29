<?php

include 'components/connect.php';

session_start();

$user_id = '1';

$payment = 'pending';

// Check if the orders table exists, if not, create it
$table_check = $conn->query("SHOW TABLES LIKE 'orders'");
if ($table_check->rowCount() == 0) {
   $create_orders_table = $conn->query("CREATE TABLE orders (
      id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      user_id INT(11),
      method VARCHAR(50),
      address VARCHAR(100),
      total_products TEXT,
      total_price DECIMAL(10,2),
      quantity_sold INT(11), /* Add new column for quantity sold */
      payment_status TEXT,
      placed_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   )");
}

if (isset($_POST['submit'])) {

   // Retrieve order details
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = $_POST['address'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   // Fetch cart items
   $cart_items = [];
   $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $select_cart->execute([$user_id]);
   if ($select_cart->rowCount() > 0) {
      while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
         // Retrieve cart item details
         $cart_items[] = $fetch_cart;
      }
   }

   if (!empty($cart_items)) {
      $conn->beginTransaction();

      try {
         // Loop through cart items and update quantity_available in the products table
         foreach ($cart_items as $item) {
            $update_quantity = $conn->prepare("UPDATE `products` SET quantity_available = quantity_available - ? WHERE id = ?");
            $update_quantity->execute([$item['quantity'], $item['pid']]);
         }

         // Sum up the total quantity sold
         $total_quantity_sold = array_sum(array_column($cart_items, 'quantity'));

         // Insert order into the orders table along with the quantity sold
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, method, address, total_products, total_price, quantity_sold, payment_status) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $method, $address, $total_products, $total_price, $total_quantity_sold, $payment]);

         // Clear the cart for the user
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $conn->commit();
         $message[] = 'Order placed successfully!';
         header('Location: kiosk.php');
      } catch (PDOException $e) {
         $conn->rollBack();
         $message[] = 'An error occurred. Please try again later.';
      }
   } else {
      $message[] = 'Your cart is empty';
   }
}

?>

<!-- rest of the HTML remains the same -->


<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Order</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
</head>

<body>

   <!-- header section starts  -->
   <?php include 'components/kiosk_header.php'; ?>
   <!-- header section ends -->

   <div class="heading">
      <h3>ORDER/S</h3>
      <p><a href="kiosk.php">home</a> <span> / Order/s</span></p>
   </div>

   <section class="checkout">

      <h1 class="title">order summary</h1>

      <form action="" method="post">

         <div class="cart-items">
            <h3>cart items</h3>
            <?php
            $grand_total = 0;
            $cart_items[] = '';
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
               while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                  $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ') - ';
                  $total_products = implode($cart_items);
                  $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
            ?>
                  <p><span class="name"><?= $fetch_cart['name']; ?></span><span class="price">₱<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?></span></p>
            <?php
               }
            } else {
               echo '<p class="empty">your cart is empty!</p>';
            }
            ?>
            <p class="grand-total"><span class="name">grand total :</span><span class="price">₱<?= $grand_total; ?></span></p>
            <a href="cart.php" class="btn">veiw cart</a>
         </div>

         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
         <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
         <input type="hidden" name="payment_status" value="pending">

         <div class="user-info">
            <select name="address" id="#" class="box" required>
               <option value="" disabled selected>Select Table</option>
               <option value="Take Out">Take Out</option>
               <option value="1">1</option>
               <option value="2">2</option>
               <option value="3">3</option>
               <option value="4">4</option>
               <option value="5">5</option>
               <option value="6">6</option>
               <option value="7">7</option>
               <option value="8">8</option>
               <option value="9">9</option>
               <option value="10">10</option>
            </select>
            <select name="method" class="box" required>
               <option value="" disabled selected>select payment method --</option>
               <option value="Cash">Cash</option>
               <option value="Gcash">Gcash</option>
               <option value="Maya">Maya</option>
               <option value="Other">Other</option>
            </select>
            <input type="submit" value="place order" class="btn 
            <?php //if ($fetch_profile['address'] == '') {
            //echo 'disabled';
            //} 
            ?>" style="width:100%; background:var(--red); color:var(--white);" name="submit">
         </div>

      </form>

   </section>

   <!-- footer section starts  -->
   <?php include 'components/footer.php'; ?>
   <!-- footer section ends -->

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>

</html>