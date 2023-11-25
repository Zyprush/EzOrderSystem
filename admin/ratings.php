<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_message = $conn->prepare("DELETE FROM `ratings` WHERE id = ?");
   $delete_message->execute([$delete_id]);
   header('location:ratings.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Ratings</title>

   <!-- Bootstrap CSS link -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

   <?php include '../components/admin_header.php' ?>

   <!-- ratings section starts  -->

   <section class="ratings">

      <h1 class="heading">Ratings</h1>

      <div class="box-container">

         <?php
         $defaultRatings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

         $select_ratings = $conn->prepare("SELECT rate, COUNT(*) as count FROM `ratings` GROUP BY rate");
         $select_ratings->execute();

         while ($fetch_ratings = $select_ratings->fetch(PDO::FETCH_ASSOC)) {
            $rate = $fetch_ratings['rate'];
            $count = $fetch_ratings['count'];
            $defaultRatings[$rate] = $count;
         }

         $totalRatings = array_sum($defaultRatings);

         foreach ($defaultRatings as $rating => $count) {
            $percentage = ($totalRatings > 0) ? ($count / $totalRatings) * 100 : 0;
            $starString = str_repeat("★", $rating) . str_repeat("☆", 5 - $rating); // Unicode stars
         ?>
            <div class="box">
               <p style="font-size: 2.5rem;"><?= $starString; ?>: <span><?= $count; ?> rated</span></p>
               <div class="progress">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentage; ?>%;" aria-valuenow="<?= $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
               </div>
            </div>
         <?php
         }
         ?>

      </div>

   </section>

   <!-- ratings section ends -->

   <!-- Bootstrap JS and Popper.js script links -->
   <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
   <!-- Bootstrap JS link -->
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

   <!-- custom js file link  -->
   <script src="../js/admin_script.js"></script>

</body>

</html>