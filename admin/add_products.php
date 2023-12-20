<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// Create the 'products' table if it doesn't exist
$create_table = $conn->query("CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `image` VARCHAR(255) NOT NULL
)");

// Create the 'product_ingredients' table if it doesn't exist
$create_product_ingredients_table = $conn->query("CREATE TABLE IF NOT EXISTS `product_ingredients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT,
    `ingredient_id` INT,
    `quantity` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`id`)
)");


if (isset($_POST['add_product'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $price = $_POST['price'];
    $price = filter_var($price, FILTER_SANITIZE_STRING);
    $category = $_POST['category'];
    $category = filter_var($category, FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_img/' . $image;

    $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
    $select_products->execute([$name]);

    if ($select_products->rowCount() > 0) {
        $message[] = 'Product name already exists!';
    } else {
        if ($image_size > 2000000) {
            $message[] = 'Image size is too large';
        } else {
            move_uploaded_file($image_tmp_name, $image_folder);

            $insert_product = $conn->prepare("INSERT INTO `products`(name, category, price, image) VALUES(?,?,?,?)");
            $insert_product->execute([$name, $category, $price, $image]);

            // Get the last inserted product ID
            $lastProductId = $conn->lastInsertId();

            // Insert selected ingredients with quantities into product_ingredients table
            if (isset($_POST['ingredients']) && is_array($_POST['ingredients']) && isset($_POST['ingredient_quantity']) && is_array($_POST['ingredient_quantity'])) {
                foreach ($_POST['ingredients'] as $ingredientId => $value) {
                    // Check if the checkbox is selected (value is '1')
                    if ($value == 1) {
                        $quantity = $_POST['ingredient_quantity'][$ingredientId];
                        $insert_product_ingredient = $conn->prepare("INSERT INTO `product_ingredients`(product_id, ingredient_id, quantity) VALUES (?, ?, ?)");
                        $insert_product_ingredient->execute([$lastProductId, $ingredientId, $quantity]);
                    }
                }
            }

            $message[] = 'New product added!';
        }
    }
}

// Fetch data (id, name, and unit) from the ingredients table
$stmt = $conn->query("SELECT id, name, unit FROM ingredients");
$ingredientsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Products</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/css/select2.min.css" rel="stylesheet" />

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">

    <style>
        .quantity {
            font-size: 1.5rem;
        }

        .ingredient-checkboxes {
            max-height: 200px;
            /* Set your desired maximum height */
            overflow-y: auto;
            /* Enable vertical scrolling */
            border: var(--border);
            border-radius: 3%;
            /* Optional: Add a border for visual clarity */
            padding: 5px;
            /* Optional: Add padding for better appearance */
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .ingredient-checkboxes label {
            display: block;
            /* Optional: Add space between checkboxes */
            border: solid 1px;
            border-radius: 3%;
            padding: 5px;
            font-size: 2rem;
            width: 100%;
        }

        .ingredient-checkboxes input {
            border: solid 1px;
        }

        .ingredient-checkboxes .quanti {
            padding: 10px;
        }
    </style>
</head>

<body>
    <?php include '../components/admin_header.php' ?>

    <!-- add products section starts  -->
    <section class="add-products">
        <form action="" method="POST" enctype="multipart/form-data">
            <h3>Add product</h3>
            <input type="text" required placeholder="Enter product name" name="name" maxlength="100" class="box">
            <input type="number" min="0" max="9999999999" required placeholder="Enter product price" name="price" onkeypress="if(this.value.length == 10) return false;" class="box">
            <select name="category" class="box" required>
                <option value="" disabled selected>Select category --</option>
                <option value="main dish">Main dish</option>
                <option value="fast food">Fast food</option>
                <option value="drinks">Drinks</option>
                <option value="desserts">Desserts</option>
            </select>

            <!-- Display ingredients as checkboxes with quantity inputs and units -->
            <div class="ingredient-checkboxes">
                <label for="ingredients">ingredients</label>
                <?php foreach ($ingredientsData as $ingredient) : ?>
                    <label>
                        <input type="checkbox" name="ingredients[<?php echo $ingredient['id']; ?>]" value="1" class="check">
                        <?php echo $ingredient['name']; ?>
                        <input type="number" name="ingredient_quantity[<?php echo $ingredient['id']; ?>]" placeholder="Quantity" min="0" step="0.1" class="quanti">
                        <?php echo $ingredient['unit']; ?>
                    </label>
                    <br>
                <?php endforeach; ?>
            </div>

            <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
            <input type="submit" value="Add product" name="add_product" class="btn">
        </form>
    </section>

    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for the ingredients selection
            $('.ingredients-select').select2({
                placeholder: 'Select ingredients',
                allowClear: true,
                multiple: true,
            });
        });
    </script>
</body>

</html>