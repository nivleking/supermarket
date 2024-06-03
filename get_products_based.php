<?php
require_once "connect.php";

$bridged_collections = $client->supermarket->transactions_bridge_products;
$transactions_collection = $client->supermarket->transactions;
$products_collection = $client->supermarket->products;

if (isset($_POST['getDataButton'])) {
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'products',
                'localField' => 'product_id',
                'foreignField' => 'product_id',
                'as' => 'product'
            ]
        ],
        [
            '$unwind' => '$product'
        ],
        [
            '$lookup' => [
                'from' => 'transactions',
                'localField' => 'order_id',
                'foreignField' => 'order_id',
                'as' => 'transaction'
            ]
        ],
        [
            '$unwind' => '$transaction'
        ],
        [
            '$group' => [
                '_id' => [
                    'category' => '$product.category',
                    'sub_category' => '$product.sub_category'
                ],
                'totalQuantity' => ['$sum' => '$quantity'],
                'totalSales' => ['$sum' => '$sales']
            ]
        ],
        [
            '$sort' => ['totalSales' => -1]
        ],
    ];

    try {
        $results = $bridged_collections->aggregate($pipeline);
        $resultsArray = iterator_to_array($results);

        $response = [];

        foreach ($resultsArray as $result) {
            $response[] = [
                'category' => $result->_id->category,
                'sub_category' => $result->_id->sub_category,
                'totalQuantity' => $result->totalQuantity,
                'totalSales' => $result->totalSales
            ];
        }
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}
