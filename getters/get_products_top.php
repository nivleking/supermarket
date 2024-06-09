<?php
require_once "../connect.php";

$bridged_collections = $client->supermarket->transactions_bridge_products;
$transactions_collection = $client->supermarket->transactions;
$products_collection = $client->supermarket->products;

$bridged_collections->createIndex(['order_id' => 1]);
$transactions_collection->createIndex(['order_id' => 1]);
$products_collection->createIndex(['product_id' => 1]);

if (isset($_POST['getDataButton'])) {
    $category = isset($_POST['category']) ? $_POST['category'] : null;
    $sub_category = isset($_POST['sub_category']) ? $_POST['sub_category'] : null;
    $month = isset($_POST['month']) ? $_POST['month'] : null;
    $year = isset($_POST['year']) ? $_POST['year'] : null;

    $matchStage = [
        '_id' => ['$ne' => null],
    ];

    $groupStage = [
        '_id' => [
            'product_id' => '$product.product_id',
            'name' => '$product.product_name',
            // 'month' => '$month',
            // 'year' => '$year',
        ],
        'totalQuantity' => ['$sum' => '$quantity'],
    ];

    if ($category != null) {
        $matchStage['product.category'] = $category;
        $groupStage['_id']['category'] = '$product.category';
    }

    if ($sub_category != null) {
        $matchStage['product.sub_category'] = $sub_category;
        $groupStage['_id']['sub_category'] = '$product.sub_category';
    }

    if ($month != null) {
        $matchStage['month'] = ['$in' => $month];
        $groupStage['month'] = ['$first' => '$month'];
    }

    if ($year != null) {
        $matchStage['year'] = ['$in' => $year];
        $groupStage['year'] = ['$first' => '$year'];
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
        ['$group' => $groupStage],
        ['$sort' => ['totalQuantity' => -1]],
        ['$limit' => 5]
    ];

    $pipelineTotalQuantity = [
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
        ['$group' => $groupStage],
        ['$sort' => ['totalQuantity' => -1]],
    ];

    try {
        $results = $bridged_collections->aggregate($pipeline);
        $resultsArray = iterator_to_array($results);

        $resultsTotalQuantity = $bridged_collections->aggregate($pipelineTotalQuantity);
        $resultsArrayTotalQuantity = iterator_to_array($resultsTotalQuantity);
        $allQuantity = array_reduce($resultsArrayTotalQuantity, function ($carry, $item) {
            return $carry + $item->totalQuantity;
        }, 0);

        $response = [];

        foreach ($resultsArray as $result) {
            $responseItem = [
                'name' => $result->_id->name,
                'product_id' => $result->_id->product_id,
                // 'month' => $result->_id->month,
                // 'year' => $result->_id->year,
                'totalQuantity' => $result->totalQuantity,
            ];

            if (isset($result->_id->category)) {
                $responseItem['category'] = $result->_id->category;
            }

            if (isset($result->_id->sub_category)) {
                $responseItem['sub_category'] = $result->_id->sub_category;
            }

            if (isset($result->month)) {
                $responseItem['month'] = $result->month;
            }

            if (isset($result->year)) {
                $responseItem['year'] = $result->year;
            }

            $response[] = $responseItem;
        }
        echo json_encode(['topProducts' => $response, 'totalQuantity' => $allQuantity]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}
