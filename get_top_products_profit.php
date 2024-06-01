<?php
require_once "connect.php";

$client = new MongoDB\Client("mongodb://localhost:27017");
$bridged_collections = $client->supermarket->transactions_bridge_products;
$transactions_collection = $client->supermarket->transactions;
$products_collection = $client->supermarket->products;

if (isset($_POST['getDataButton'])) {

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
            'year' => $year
        ]],
        ['$group' => [
            '_id' => [
                'name' => '$product.product_name',
            ],
            'totalProfit' => ['$sum' => '$profit'],
        ]],
        ['$sort' => ['totalProfit' => -1]],
        ['$limit' => 5]
    ];

    try {
        $results = $bridged_collections->aggregate($pipeline);
        $resultsArray = iterator_to_array($results);

        $response = [];

        foreach ($resultsArray as $result) {
            $response[] = [
                'name' => $result->_id->name,
                'totalProfit' => $result->totalProfit,
            ];
        }

        echo json_encode($response);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
