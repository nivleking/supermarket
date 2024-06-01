<?php
require_once "connect.php";

$client = new MongoDB\Client("mongodb://localhost:27017");
$bridged_collections = $client->supermarket->transactions_bridge_products;
$transactions_collection = $client->supermarket->transactions;
$products_collection = $client->supermarket->products;

if (isset($_POST['getDataButton'])) {
    $category = $_POST['category'];
    $sub_category = $_POST['sub_category'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    $pipeline = [
        ['$lookup' => [
            'from' => "products",
            'localField' => "product_id",
            'foreignField' => "product_id",
            'as' => "product"
        ]],
        ['$unwind' => '$product'],
        ['$lookup' => [
            'from' => "transactions",
            'localField' => "order_id",
            'foreignField' => "order_id",
            'as' => "transaction"
        ]],
        ['$unwind' => '$transaction'],
        ['$addFields' => [
            'month' => ['$substr' => ['$transaction.order_date', 3, 2]],
            'year' => ['$substr' => ['$transaction.order_date', 6, 4]]
        ]],
        ['$match' => [
            'month' => $month,
            'year' => $year,
            'product.category' => $category,
            'product.sub_category' => $sub_category
        ]],
        ['$group' => [
            '_id' => [
                'name' => '$product.product_name',
                'category' => '$product.category',
                'sub_category' => '$product.sub_category'
            ],
            'totalSales' => ['$sum' => '$quantity'],
            'month' => ['$first' => '$month'],
            'year' => ['$first' => '$year']
        ]],
        ['$sort' => ['totalSales' => -1]],
        ['$limit' => 5]
    ];

    try {
        $results = $bridged_collections->aggregate($pipeline);
        $resultsArray = iterator_to_array($results);

        $response = [];

        foreach ($resultsArray as $result) {
            $response[] = [
                'name' => $result->_id->name,
                'category' => $result->_id->category,
                'sub_category' => $result->_id->sub_category,
                'totalSales' => $result->totalSales,
                'month' => $result->month,
                'year' => $result->year
            ];
        }
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}
