<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if (isset($_POST['submit'])) {
   // Retrieve form data
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
   $account_type = $_POST['account_type']; // Get the selected account type

   // Check if the table exists, and create it if it doesn't
   $check_table_query = "SHOW TABLES LIKE '$account_type'";
   $check_table = $conn->query($check_table_query);

   if ($check_table->rowCount() == 0) {
      // Create the table if it doesn't exist
      $create_table_query = "CREATE TABLE `$account_type` (
         id INT AUTO_INCREMENT PRIMARY KEY,
         name VARCHAR(255) NOT NULL,
         password VARCHAR(255) NOT NULL
      )";
      $conn->exec($create_table_query);
   }

   // Check if the username already exists
   $select_query = "SELECT * FROM `$account_type` WHERE name = ?";
   $select_admin = $conn->prepare($select_query);
   $select_admin->execute([$name]);

   if ($select_admin->rowCount() > 0) {
      $message[] = 'Username already exists!';
   } else {
      if ($pass != $cpass) {
         $message[] = 'Confirm password not matched!';
      } else {
         // Insert into the corresponding table based on the selected account type
         $insert_query = "INSERT INTO `$account_type` (name, password) VALUES (?, ?)";
         $insert_admin = $conn->prepare($insert_query);
         $insert_admin->execute([$name, $cpass]);

         $message[] = 'New ' . ucfirst($account_type) . ' registered!';
      }
   }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php' ?>

<!-- register admin section starts  -->

<section class="form-container">

<form action="" method="POST">
   <h3>Register new</h3>
   <input type="text" name="name" maxlength="20" required placeholder="Enter your username" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
   <input type="password" name="pass" maxlength="20" required placeholder="Enter your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
   <input type="password" name="cpass" maxlength="20" required placeholder="Confirm your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">

   <select name="account_type" id="account_type" class="box">
      <option value="">--Select Account--</option>
      <option value="user">User</option>
      <option value="cashier">Cashier</option>
      <option value="kitchen">Kitchen</option>
      <option value="admin">Admin</option>
   </select>

   <input type="submit" value="Register now" name="submit" class="btn">
</form>


</section>

<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>