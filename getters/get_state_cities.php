<?php
require_once "../connect.php";

$bridged_collections = $client->supermarket->transactions_bridge_products;
$transactions_collection = $client->supermarket->transactions_with_customers;
$customers_collection = $client->supermarket->customers;

$bridged_collections->createIndex(['order_id' => 1]);
$transactions_collection->createIndex(['order_id' => 1]);
$customers_collection->createIndex(['customer_id' => 1]);

if (isset($_POST['getDataButton'])) {
    $state = $_POST['state'];

    if ($state == "all_states") {
        $matchStage = ['$match' => ['state' => ['$exists' => true]]];
    } else {
        $matchStage = ['$match' => ['state' => $state]];
    }

    $pipeline = [
        ['$lookup' => [
            'from' => 'transactions_bridge_products',
            'localField' => 'order_id',
            'foreignField' => 'order_id',
            'as' => 'products'
        ]],
        ['$unwind' => '$products'],
        ['$lookup' => [
            'from' => 'customers',
            'localField' => 'customer_id',
            'foreignField' => 'customer_id',
            'as' => 'customer'
        ]],
        ['$unwind' => '$customer'],
        ['$addFields' => [
            'numericSales' => ['$toDouble' => ['$replaceOne' => ['input' => ['$toString' => '$products.sales'], 'find' => ',', 'replacement' => '.']]],
            'state' => '$customer.state',
        ]],
        $matchStage,
        ['$group' => [
            '_id' => ['state' => '$customer.state', 'city' => '$customer.city'],
            'count' => ['$sum' => 1],
            'totalQuantity' => ['$sum' => '$products.quantity'],
            'avgQuantity' => ['$avg' => '$products.quantity'],
            'maxQuantity' => ['$max' => '$products.quantity'],
            'minQuantity' => ['$min' => '$products.quantity'],
            'totalSales' => ['$sum' => '$numericSales'],
            'avgSales' => ['$avg' => '$numericSales'],
            'maxSales' => ['$max' => '$numericSales'],
            'minSales' => ['$min' => '$numericSales']
        ]],
        ['$project' => [
            'state' => '$_id.state',
            'city' => '$_id.city',
            'count' => 1,
            'totalQuantity' => 1,
            'avgQuantity' => 1,
            'maxQuantity' => 1,
            'minQuantity' => 1,
            'totalSales' => 1,
            'avgSales' => 1,
            'maxSales' => 1,
            'minSales' => 1
        ]]
    ];

    try {
        $result = $transactions_collection->aggregate($pipeline);
        $resultArray = iterator_to_array($result);

        $response = [];

        foreach ($resultArray as $res) {
            $response[] = [
                'state' => $res['state'],
                'city' => $res['city'],
                'count' => $res['count'],
                'totalQuantity' => $res['totalQuantity'],
                'avgQuantity' => $res['avgQuantity'],
                'maxQuantity' => $res['maxQuantity'],
                'minQuantity' => $res['minQuantity'],
                'totalSales' => $res['totalSales'],
                'avgSales' => $res['avgSales'],
                'maxSales' => $res['maxSales'],
                'minSales' => $res['minSales']
            ];
        }
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}
