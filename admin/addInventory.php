<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
};

$create_table_ingredients = $conn->query("CREATE TABLE IF NOT EXISTS `ingredients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `quantity` DECIMAL(10, 2) NOT NULL DEFAULT 0.00, /* Change data type to DECIMAL */
    `unit` VARCHAR(50) NOT NULL
)");


// ... (Your existing code)

if (isset($_POST['add_ingredient'])) {
    $ingredient_name = $_POST['ingredient_name'];
    $ingredient_name = filter_var($ingredient_name, FILTER_SANITIZE_STRING);

    $ingredient_quantity = $_POST['ingredient_quantity'];
    $ingredient_quantity = filter_var($ingredient_quantity, FILTER_VALIDATE_INT);

    $ingredient_unit = $_POST['ingredient_unit'];
    $ingredient_unit = filter_var($ingredient_unit, FILTER_SANITIZE_STRING);

    // Check if the ingredient already exists (you can modify this check as needed)
    $select_ingredients = $conn->prepare("SELECT * FROM `ingredients` WHERE name = ?");
    $select_ingredients->execute([$ingredient_name]);

    if ($select_ingredients->rowCount() > 0) {
        $message[] = 'Ingredient already exists!';
    } else {
        $insert_ingredient = $conn->prepare("INSERT INTO `ingredients`(name, quantity, unit) VALUES(?,?,?)");
        $insert_ingredient->execute([$ingredient_name, $ingredient_quantity, $ingredient_unit]);

        $message[] = 'New ingredient added!';
    }
}

// ... (Rest of your existing code)


if (isset($_GET['delete'])) {

   $delete_id = $_GET['delete'];
   $delete_product = $conn->prepare("DELETE FROM `ingredients` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   header('location:addinventory.php');
}


// Fetch data from the 'ingredients' table
$stmt = $conn->prepare("SELECT name, quantity FROM ingredients");
$stmt->execute();
$ingredientData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert the PHP array to a JSON object to pass to JavaScript
$ingredientDataJSON = json_encode($ingredientData);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- DataTables JavaScript -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">


    <style>
    .stock-levels-container {
        border: var(--border);
        border-radius: 5px;
        padding: 25px;
        box-shadow: inset;
        margin: 30px;
        margin-top: 60px;
    }

    /* Target the table body and set font size */
    .display tbody td {
        font-size: 1.5rem;
        /* Adjust the font size as needed */
    }

    /* Target the table header and set font size */
    .display thead th {
        font-size: 1.5rem;
        /* Adjust the font size as needed */
    }

    /* Style for button links */
    .button-link {
        display: inline-block;
        padding: 8px 12px;
        text-decoration: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f0f0f0;
        color: #333;
        transition: background-color 0.3s, color 0.3s;
    }

    /* Hover effect for button links */
    .button-link:hover {
        background-color: #ddd;
        color: #000;
    }

    /* Style the dialog as needed */
    .dialog {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 1px solid #ccc;
        background: #fff;
        padding: 20px;
        z-index: 1000;
        /* Other styles for appearance */
    }

    .btn-add {
        margin-top: 1rem;
        display: inline-block;
        font-size: 2rem;
        padding: 1rem 3rem;
        cursor: pointer;
        text-transform: capitalize;
        transition: .2s linear;
    }

    .btn-primary {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        float: right;
        margin-bottom: 25px;
        /* Add other styles as needed */
    }
    </style>
</head>

<body>

    <?php include '../components/admin_header.php' ?>
    <h1 class="heading">Inventory stocks</h1>
    <div class="stock-levels-container">
        <!-- bar chart for stocks -->
        <canvas id="ingredientChart" width="800" height="200"></canvas>
    </div>
    <!-- Add a button to open the dialog -->
    <!-- add products section starts  -->
    <div class="stock-levels-container">
        <button id="openDialog" class="btn-primary right-side">Add Item</button>
        <h2>Ingredients and Raw Products Stock</h2>
        <table id="ingTable" class="display">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $stock_query = $conn->query("SELECT id, name, quantity, unit FROM ingredients");
                    while ($row = $stock_query->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                    <td>{$row['name']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['unit']}</td>
                    <td>
                        <a href='update_inventory.php?update={$row['id']}' class='button-link edit-link'>Edit</a>
                        <a href='addinventory.php?delete={$row['id']}' class='button-link delete-link' onclick='return confirm(\"Are you sure you want to delete this product?\")'>Delete</a>
                    </td>

                    </tr>";
        }
        ?>
            </tbody>
        </table>
    </div>



    <!-- Your form content -->
    <div id="productDialog" class="dialog" style="display: none;">
        <div class="col-md-6">
            <section class="add-products">
                <form action="" method="POST" enctype="multipart/form-data">
                    <h3>Add Item</h3>
                    <input type="text" required placeholder="Enter name" name="ingredient_name"
                        maxlength="100" class="box">
                    <input type="number" min="0" step="0.1" max="9999999999" required placeholder="Quantity"
                        name="ingredient_quantity" class="box">
                    <select name="ingredient_unit" class="box" required>
                        <option value="" disabled selected>Select unit</option>
                        <option value="kg">kg</option>
                        <option value="liters">liters</option>
                        <option value="grams">grams</option>
                        <option value="pieces">pieces</option>
                        <!-- Add more options as needed -->
                    </select>
                    <!-- Add more fields as needed -->
                    <input type="submit" value="Add" name="add_ingredient" class="btn">
                    <button id="cancelDialog" class="btn">Cancel</button>
                </form>
            </section>
        </div>
    </div>
    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

    <script>
    $(document).ready(function() {
        $('#ingTable').DataTable(); // Initialize DataTable
    });

    // Get the dialog, open button, and cancel button elements
    var productDialog = document.getElementById('productDialog');
    var openDialogBtn = document.getElementById('openDialog');
    var cancelDialogBtn = document.getElementById('cancelDialog');

    // Function to open the dialog
    openDialogBtn.addEventListener('click', function() {
        productDialog.style.display = 'block';
    });

    // Function to close the dialog
    cancelDialogBtn.addEventListener('click', function() {
        productDialog.style.display = 'none';
    });

    // Close the dialog when clicking outside the dialog content
    window.addEventListener('click', function(event) {
        if (event.target === productDialog) {
            productDialog.style.display = 'none';
        }
    });


    // Retrieve the PHP-generated JSON data
    const ingredientData = <?php echo $ingredientDataJSON; ?>;

    // Extract names and quantities from the fetched data
    const ingredientNames = ingredientData.map(item => item.name);
    const ingredientQuantities = ingredientData.map(item => item.quantity);

    // Get the canvas element
    const ctx = document.getElementById('ingredientChart').getContext('2d');

    // Create the bar chart using Chart.js
    const ingredientChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ingredientNames,
            datasets: [{
                label: 'Ingredient Quantities',
                data: ingredientQuantities,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>

</body>

</html>