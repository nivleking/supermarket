<?php
require "../connect.php";

$transactions_with_customers_collection = $client->supermarket->transactions_with_customers;
$transactions_with_customers_collection->createIndex(['order_id' => 1]);

if (isset($_POST["getDataButton"])) {
    $segment = $_POST["segment"];

    $pipeline = [
        ['$lookup' => [
            'from' => "customers",
            'localField' => "customer_id",
            'foreignField' => "customer_id",
            'as' => "customer_details"
        ]],
        ['$unwind' => '$customer_details'],
        ['$addFields' => [
            'segment' => '$customer_details.segment'
        ]]
    ];

    if ($segment != "all_segments") {
        $pipeline[] = ['$match' => ['segment' => $segment]];
    }

    $pipeline[] = ['$group' => [
        '_id' => '$customer_details.segment',
        'total_transactions' => ['$sum' => 1]
    ]];

    $pipeline[] = ['$sort' => ['total_transactions' => -1]];

    try {
        $results = $transactions_with_customers_collection->aggregate($pipeline);
        $resultsArray = iterator_to_array($results);

        $response = [];

        foreach ($resultsArray as $result) {
            $response[] = [
                'segment' => $result->_id,
                'total_transactions' => $result->total_transactions
            ];
        }

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
