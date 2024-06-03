<?php
require_once "connect.php";


$transaksi = $client->supermarket->transactions;
// Select Region
$regions = $transaksi->distinct("region");

// Select Years
$yearPipeline = [
    [
        '$project' => [
            'year' => ['$year' => ['$dateFromString' => ['dateString' => '$order_date', 'format' => '%d/%m/%Y']]]
        ]
    ],
    [
        '$group' => [
            '_id' => '$year'
        ]
    ],
    [
        '$sort' => [
            '_id' => 1
        ]
    ]
];
$result = $transaksi->aggregate($yearPipeline);

$years = [];
foreach ($result as $document) {
    $years[] = $document['_id'];
}

if (isset($_POST['regions'])) {
    $filter = [];

    if ($_POST['regions'] == 'ar') {
        $pipeline = [
            [
                '$lookup' => [
                    'from' => 'transactions_bridge_products',
                    'localField' => 'order_id',
                    'foreignField' => 'order_id',
                    'as' => 'details'
                ]
            ],
            ['$unwind' => '$details'],
            [
                '$project' => [
                    'order_date' => ['$dateFromString' => ['dateString' => '$order_date', 'format' => '%d/%m/%Y']],
                    'sales' => '$details.sales',
                    'profit' => '$details.profit'
                ]
            ],
            [
                '$group' => [
                    '_id' => ['$year' => '$order_date'],
                    'total_sales' => ['$sum' => '$sales'],
                    'total_profit' => ['$sum' => '$profit']
                ]
            ],
            [
                '$group' => [
                    '_id' => null,
                    'years' => [
                        '$push' => [
                            'year' => '$_id',
                            'total_sales' => '$total_sales',
                            'total_profit' => '$total_profit'
                        ]
                    ],
                    'max_sales' => ['$max' => '$total_sales'],
                    'min_sales' => ['$min' => '$total_sales'],
                    'avg_sales' => ['$avg' => '$total_sales'],
                    'max_profit' => ['$max' => '$total_profit'],
                    'min_profit' => ['$min' => '$total_profit'],
                    'avg_profit' => ['$avg' => '$total_profit']
                ]
            ],
            [
                '$project' => [
                    'years' => 1,
                    'max_sales' => 1,
                    'min_sales' => 1,
                    'avg_sales' => 1,
                    'max_profit' => 1,
                    'min_profit' => 1,
                    'avg_profit' => 1,
                    'total_sales' => ['$sum' => '$years.total_sales'],
                    'total_profit' => ['$sum' => '$years.total_profit']
                ]
            ]
        ];
        $result = $transaksi->aggregate($pipeline)->toArray();
        $response = [
            'avg_sales' => $result[0]->avg_sales ?? 0,
            'avg_profit' => $result[0]->avg_profit ?? 0,
            'max_sales' => $result[0]->max_sales ?? 0,
            'max_profit' => $result[0]->max_profit ?? 0,
            'min_sales' => $result[0]->min_sales ?? 0,
            'min_profit' => $result[0]->min_profit ?? 0,
            'total_sales' => $result[0]->total_sales ?? 0,
            'total_profit' => $result[0]->total_profit ?? 0,
            'years' => $result[0]->years ?? [],
        ];

        echo json_encode($response);
        exit();
    } else {
        $filter['region'] = $_POST['regions'];
        $pipeline = [
            [
                '$lookup' => [
                    'from' => 'transactions_bridge_products',
                    'localField' => 'order_id',
                    'foreignField' => 'order_id',
                    'as' => 'details'
                ]
            ],
            [
                '$unwind' => '$details'
            ],
            [
                '$project' => [
                    'region' => 1,
                    'order_date' => ['$dateFromString' => ['dateString' => '$order_date', 'format' => '%d/%m/%Y']],
                    'sales' => '$details.sales',
                    'profit' => '$details.profit'
                ]
            ],
            [
                '$match' => $filter
            ],
            [
                '$group' => [
                    '_id' => ['$year' => '$order_date'],
                    'total_sales' => ['$sum' => '$sales'],
                    'total_profit' => ['$sum' => '$profit']
                ]
            ],
            [
                '$group' => [
                    '_id' => null,
                    'years' => [
                        '$push' => [
                            'year' => '$_id',
                            'total_sales' => '$total_sales',
                            'total_profit' => '$total_profit'
                        ]
                    ],
                    'max_sales' => ['$max' => '$total_sales'],
                    'min_sales' => ['$min' => '$total_sales'],
                    'avg_sales' => ['$avg' => '$total_sales'],
                    'max_profit' => ['$max' => '$total_profit'],
                    'min_profit' => ['$min' => '$total_profit'],
                    'avg_profit' => ['$avg' => '$total_profit']
                ]
            ],
            [
                '$project' => [
                    'years' => 1,
                    'max_sales' => 1,
                    'min_sales' => 1,
                    'avg_sales' => 1,
                    'max_profit' => 1,
                    'min_profit' => 1,
                    'avg_profit' => 1,
                    'total_sales' => ['$sum' => '$years.total_sales'],
                    'total_profit' => ['$sum' => '$years.total_profit']
                ]
            ]
        ];

        $result = $transaksi->aggregate($pipeline)->toArray();
        $response = [
            'avg_sales' => $result[0]->avg_sales ?? 0,
            'avg_profit' => $result[0]->avg_profit ?? 0,
            'max_sales' => $result[0]->max_sales ?? 0,
            'max_profit' => $result[0]->max_profit ?? 0,
            'min_sales' => $result[0]->min_sales ?? 0,
            'min_profit' => $result[0]->min_profit ?? 0,
            'total_sales' => $result[0]->total_sales ?? 0,
            'total_profit' => $result[0]->total_profit ?? 0,
            'years' => $result[0]->years ?? [],
        ];

        echo json_encode($response);
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket</title>
    <!-- CDN for jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
</head>

<body class="bg-gray-100">

    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Sidebar -->
    <div class="flex">
        <?php include 'components/sidebar.php'; ?>

        <!-- Main content -->
        <main class="flex-1 p-6 sm:ml-64 mt-12">
            <div class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col md:flex-row justify-between items-start">
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Region Sales and Profit</h1>
                <div class="flex flex-col md:flex-row items-start md:items-center">
                    <select id="regions" aria-label="Select Region" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                        <option value="" disabled selected>Select Region</option>
                        <option value="ar">All Regions</option>
                        <?php foreach ($regions as $region) : ?>
                            <option value="<?= $region ?>"><?= $region ?></option>
                        <?php endforeach; ?>
                    </select>
                    <!-- <select id="years" aria-label="Select Years" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-4 mt-4 md:mt-0">
                        <option value="" disabled selected>Select Year</option>
                        <option value="ay">All Years</option>
                        <?php foreach ($years as $year) : ?>
                            <option value="<?= $year ?>"><?= $year ?></option>
                        <?php endforeach; ?>
                    </select> -->
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                    <div class="text-4xl font-bold text-center text-gray-800" id="totalSales">-</div>
                    <div class="text-center text-gray-500">Total Sales</div>
                </div>
                <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                    <div class="text-4xl font-bold text-center text-gray-800" id="totalProfit">-</div>
                    <div class="text-center text-gray-500">Total Profit</div>
                </div>
            </div>

            <div class="grid grid-cols-1 mt-8 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-md rounded-lg p-6 flex flex-col justify-center items-center md:col-span-2" style="height: 100%;">
                    <canvas id="barChart"></canvas>
                </div>

                <div class="flex flex-col gap-6">
                    <!-- Min Sales and Min Profit side by side -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                            <div class="text-4xl font-bold text-center text-gray-800" id="minSales">-</div>
                            <div class="text-center text-gray-500">Min Sales</div>
                        </div>
                        <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                            <div class="text-4xl font-bold text-center text-gray-800" id="minProfit">-</div>
                            <div class="text-center text-gray-500">Min Profit</div>
                        </div>
                    </div>

                    <!-- Max Sales and Max Profit side by side -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                            <div class="text-4xl font-bold text-center text-gray-800" id="maxSales">-</div>
                            <div class="text-center text-gray-500">Max Sales</div>
                        </div>
                        <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                            <div class="text-4xl font-bold text-center text-gray-800" id="maxProfit">-</div>
                            <div class="text-center text-gray-500">Max Profit</div>
                        </div>
                    </div>

                    <!-- Average Sales and Average Profit side by side -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                            <div class="text-4xl font-bold text-center text-gray-800" id="avgSales">-</div>
                            <div class="text-center text-gray-500">Average Sales</div>
                        </div>
                        <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                            <div class="text-4xl font-bold text-center text-gray-800" id="avgProfit">-</div>
                            <div class="text-center text-gray-500">Average Profit</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script>
        $(document).ready(function() {
            var ctx = $('#barChart').get(0).getContext('2d');
            var myChart;

            function loadSupermarket() {
                var selectedRegion = $('#regions').val();

                console.log(selectedRegion);

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: 'region.php',
                    data: {
                        regions: selectedRegion,
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        console.log(data);

                        $('#totalSales').text(data.total_sales);
                        $('#totalProfit').text(data.total_profit);
                        $('#maxSales').text(data.max_sales);
                        $('#minSales').text(data.min_sales);
                        $('#avgSales').text(data.avg_sales);
                        $('#maxProfit').text(data.max_profit);
                        $('#minProfit').text(data.min_profit);
                        $('#avgProfit').text(data.avg_profit);

                        updateChart(data.years);
                        Swal.close();
                    },
                    error: function() {
                        Swal.fire('Error', 'Error fetching data', 'error');
                    }
                });
            }

            function updateChart(years) {
                if (myChart) {
                    myChart.destroy();
                }
                myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: years.map(yearData => yearData.year),
                        datasets: [{
                                label: 'Sales',
                                data: years.map(yearData => yearData.total_sales),
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Profit',
                                data: years.map(yearData => yearData.total_profit),
                                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            x: {
                                beginAtZero: true,
                                stacked: false
                            },
                            y: {
                                beginAtZero: true,
                                stacked: false
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Sales and Profit by Year',
                                font: {
                                    size: 18
                                },
                                padding: {
                                    top: 10,
                                    bottom: 30
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        }
                    }
                });
            }

            $("#regions").on('change', loadSupermarket);
        });
    </script>


</body>

</html>