<?php
include '../components/connect.php';

// Fetch data (id and name) from the ingredients table
$stmt = $conn->query("SELECT id, name FROM ingredients");
$ingredientsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return data as JSON
echo json_encode($ingredientsData);
?>
