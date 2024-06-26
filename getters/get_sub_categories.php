<?php
require_once "../connect.php";
$products_collection = $client->supermarket->products;

if (isset($_POST['category'])) {
    $sub_categories_furnitures = $products_collection->distinct("sub_category", ["category" => "Furniture"]);
    $sub_categories_officesupplies = $products_collection->distinct("sub_category", ["category" => "Office Supplies"]);
    $sub_categories_technology = $products_collection->distinct("sub_category", ["category" => "Technology"]);

    $category = $_POST['category'];
    if ($category == "Furniture") {
        $sub_categories = $sub_categories_furnitures;
    } elseif ($category == "Office Supplies") {
        $sub_categories = $sub_categories_officesupplies;
    } elseif ($category == "Technology") {
        $sub_categories = $sub_categories_technology;
    }
    // echo "<option value='category_only'>All sub-categories of $category</option>";
    foreach ($sub_categories as $sub_category) {
        echo "<option value='" . $sub_category . "'>" . $sub_category . "</option>";
    }
    exit();
}
