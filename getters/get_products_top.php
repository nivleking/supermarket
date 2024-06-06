<?php
require_once "../connect.php";

$bridged_collections = $client->supermarket->transactions_bridge_products;
$transactions_collection = $client->supermarket->transactions;
$products_collection = $client->supermarket->products;

$bridged_collections->createIndex(['order_id' => 1]);
$transactions_collection->createIndex(['order_id' => 1]);
$products_collection->createIndex(['product_id' => 1]);

$categories = $products_collection->distinct('category');

if (isset($_POST['getDataButton'])) {
    $category = $_POST['category'];
    $sub_category = $_POST['sub_category'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    $matchStage = [
        'month' => ['$in' => $month],
        'year' => ['$in' => $year],
        'product.category' => $category
    ];

    if ($sub_category != null) {
        $matchStage['product.sub_category'] = $sub_category;
    }

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
        ['$match' => $matchStage],
        ['$group' => [
            '_id' => [
                'name' => '$product.product_name',
                'category' => '$product.category',
                'sub_category' => '$product.sub_category'
            ],
            'totalQuantity' => ['$sum' => '$quantity'],
            'totalSales' => ['$sum' => '$sales'],
            'month' => ['$first' => '$month'],
            'year' => ['$first' => '$year']
        ]],
        ['$sort' => ['totalQuantity' => -1]],
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
                'totalQuantity' => $result->totalQuantity,
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
