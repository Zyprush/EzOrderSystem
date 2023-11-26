<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:kiosk.php');
};

if (isset($_POST['send'])) {

    $rate = $_POST['rate'];
    $rate = filter_var($rate, FILTER_SANITIZE_STRING);

    $insert_rating = $conn->prepare("INSERT INTO `ratings`(rate) VALUES(?)");
    $insert_rating->execute([$rate]);

    $rating[] = 'sent message successfully!';

    header('Location: kiosk.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzOrder | Thanks!</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
    <style>
        .rating:not(:checked)>input {
            position: absolute;
            appearance: none;
        }

        .rating:not(:checked)>label {
            float: right;
            cursor: pointer;
            font-size: 90px;
            color: #666;
        }

        .rating:not(:checked)>label:before {
            content: '★';
        }

        .rating>input:checked+label:hover,
        .rating>input:checked+label:hover~label,
        .rating>input:checked~label:hover,
        .rating>input:checked~label:hover~label,
        .rating>label:hover~input:checked~label {
            color: #e58e09;
        }

        .rating:not(:checked)>label:hover,
        .rating:not(:checked)>label:hover~label {
            color: #ff9e0b;
        }

        .rating>input:checked~label {
            color: #ffa723;
        }

        button {
            background: none;
            border: none;
            color: #fed330;
            /* Blue color, you can change it to your preferred color */

            cursor: pointer;
            font-size: 2.5rem;
        }

        button:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- header section starts  -->
    <?php include 'components/kiosk_header.php'; ?>
    <!-- header section ends -->

    <div class="heading" style="height: 100vh;">
        <h3>Salamat po! </h3>
        <p>Please wait for your Order.</p>
        <form action="" method="post">
            <div class="rating">
                <input value="5" name="rate" id="star5" type="radio">
                <label title="text" for="star5"></label>
                <input value="4" name="rate" id="star4" type="radio">
                <label title="text" for="star4"></label>
                <input value="3" name="rate" id="star3" type="radio">
                <label title="text" for="star3"></label>
                <input value="2" name="rate" id="star2" type="radio">
                <label title="text" for="star2"></label>
                <input value="1" name="rate" id="star1" type="radio">
                <label title="text" for="star1"></label>
            </div>
            <p>
            </p>
            <p style="text-align: center;">
                <button type="submit" name="send">Rate Us</button>
                <span>|</span>
                <a href="kiosk.php">Home</a>
            </p>
        </form>
    </div>



    <!-- footer section starts  -->
    <?php include 'components/footer.php'; ?>
    <!-- footer section ends -->

    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>