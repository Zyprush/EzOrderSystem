    <?php

    include '../components/connect.php';

    session_start();

    $admin_id = $_SESSION['admin_id'];

    if (!isset($admin_id)) {
        header('location:admin_login.php');
    };

    if (isset($_GET['delete'])) {
        $delete_id = $_GET['delete'];

        // Delete associated product ingredients first
        $delete_product_ingredients = $conn->prepare("DELETE FROM `product_ingredients` WHERE product_id = ?");
        $delete_product_ingredients->execute([$delete_id]);

        // Fetch the product image before deleting the product
        $fetch_delete_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $fetch_delete_image->execute([$delete_id]);
        $image_data = $fetch_delete_image->fetch(PDO::FETCH_ASSOC);

        if ($image_data && isset($image_data['image'])) {
            // Delete the image associated with the product
            unlink('../uploaded_img/' . $image_data['image']);
        }

        // Now delete the product
        $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
        $delete_product->execute([$delete_id]);

        // Delete related cart entries (if necessary)
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
        $delete_cart->execute([$delete_id]);

        header('location:products.php');
        exit(); // Ensure script stops here after redirect
    }

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>products</title>

        <!-- font awesome cdn link  -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

        <!-- custom css file link  -->
        <link rel="stylesheet" href="../css/admin_style.css">

        <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">


        <style>
            .quantity {
                font-size: 1.5rem;
            }

            .container {
                margin-bottom: 70px;
                padding: 20px;
            }
        </style>
    </head>

    <body>

        <?php include '../components/admin_header.php' ?>

        <!-- add products section starts  -->
        <div class="container">
            <a href="add_products.php" class="btn" style="float: right; width: 20%;">Add product</a>
        </div>

        <!-- add products section ends -->

        <!-- show products section starts  -->

        <section class="show-products" style="padding-top: 0;">
            <div class="box-container">
                <?php
                $show_products = $conn->prepare("SELECT * FROM `products`");
                $show_products->execute();
                if ($show_products->rowCount() > 0) {
                    while ($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)) {
                ?>
                        <div class="box">
                            <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                            <div class="flex">
                                <div class="price"><span>₱</span><?= $fetch_products['price']; ?><span>/-</span></div>
                                <div class="category"><?= $fetch_products['category']; ?></div>
                            </div>
                            <div class="name"><?= $fetch_products['name']; ?></div>

                            <!-- Fetching ingredients associated with the product -->
                            <div class="ingredients">Ingredients:</div>
                            <ul>
                                <?php
                                $product_id = $fetch_products['id'];
                                $productIngredientsQuery = $conn->prepare("SELECT * FROM `product_ingredients` WHERE product_id = ?");
                                $productIngredientsQuery->execute([$product_id]);
                                while ($productIngredient = $productIngredientsQuery->fetch(PDO::FETCH_ASSOC)) {
                                    $ingredient_id = $productIngredient['ingredient_id'];
                                    $ingredientQuery = $conn->prepare("SELECT * FROM `ingredients` WHERE id = ?");
                                    $ingredientQuery->execute([$ingredient_id]);
                                    $ingredient = $ingredientQuery->fetch(PDO::FETCH_ASSOC);
                                ?>
                                    <li><?= $ingredient['name']; ?> - <?= $productIngredient['quantity']; ?></li>
                                <?php } ?>
                            </ul>

                            <!-- Update and delete buttons -->
                            <div class="flex-btn">
                                <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
                                <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<p class="empty">no products added yet!</p>';
                }
                ?>
            </div>
        </section>


        <!-- show products section ends -->

        <!-- custom js file link  -->
        <script src="../js/admin_script.js"></script>

    </body>

    </html>