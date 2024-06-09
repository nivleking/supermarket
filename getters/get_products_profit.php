<?php
require_once "../connect.php";

$transactions_bridge_products = $client->supermarket->transactions_bridge_products;

$transactions_bridge_products->createIndex(['order_id' => 1]);

if (isset($_POST['getDataButton'])) {
    $month = isset($_POST['month']) ? $_POST['month'] : null;
    $year = isset($_POST['year']) ? $_POST['year'] : null;

    $matchStage = [
        '_id' => ['$ne' => null],
    ];

    $groupStage = [
        '_id' => [
            'product_id' => '$product.product_id',
            'name' => '$product.product_name',
        ],
        'totalProfit' => ['$sum' => '$profit'],
    ];

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
        ['$sort' => ['totalProfit' => -1]],
        ['$limit' => 5]
    ];

    $pipelineTotalProfit = [
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
        ['$sort' => ['totalProfit' => -1]],
    ];

    try {
        $results = $transactions_bridge_products->aggregate($pipeline);
        $resultsArray = iterator_to_array($results);

        $resultsTotalProfit = $transactions_bridge_products->aggregate($pipelineTotalProfit);
        $resultsArrayTotalProfit = iterator_to_array($resultsTotalProfit);
        $allProfit = array_reduce($resultsArrayTotalProfit, function ($carry, $item) {
            return $carry + $item->totalProfit;
        }, 0);

        $response = [];

        foreach ($resultsArray as $result) {
            $responseItem = [
                'product_id' => $result->_id->product_id,
                'name' => $result->_id->name,
                'totalProfit' => $result->totalProfit,
            ];

            if (isset($result->month)) {
                $responseItem['month'] = $result->month;
            }

            if (isset($result->year)) {
                $responseItem['year'] = $result->year;
            }

            $response[] = $responseItem;
        }

        echo json_encode(['topProducts' => $response, 'totalProfit' => $allProfit]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
