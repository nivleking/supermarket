<?php
require "../connect.php";

$transactions_with_customers_collection = $client->supermarket->transactions_with_customers;
$transactions_with_customers_collection->createIndex(['order_id' => 1]);

if (isset($_POST["getDataButton"])) {
    $state = $_POST["state"];

    if ($state == "all_states") {
        $pipeline = [
            [
                '$group' => [
                    '_id' => [
                        'state' => '$state',
                    ],
                    'total_transactions' => [
                        '$sum' => 1
                    ]
                ]
            ],
            [
                '$sort' => [
                    'total_transactions' => -1
                ]
            ]
        ];
    } else {
        $pipeline = [
            [
                '$match' => [
                    'state' => $state
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        'state' => '$state',
                        'city' => '$city'
                    ],
                    'total_transactions' => [
                        '$sum' => 1
                    ]
                ]
            ],
            [
                '$sort' => [
                    'total_transactions' => -1
                ]
            ]
        ];
    }

    try {
        $results = $transactions_with_customers_collection->aggregate($pipeline);
        $resultsArray = iterator_to_array($results);

        $response = [];

        foreach ($resultsArray as $result) {
            if ($state == "all_states") {
                $response[] = [
                    'state' => $result->_id->state,
                    'total_transactions' => $result->total_transactions
                ];
            } else {
                $response[] = [
                    'state' => $result->_id->state,
                    'city' => $result->_id->city,
                    'total_transactions' => $result->total_transactions
                ];
            }
        }

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
