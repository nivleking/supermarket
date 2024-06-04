<?php
require_once "connect.php";

$transaksi = $client->supermarket->transactions;
// Select Region
$regions = $transaksi->distinct("region");

if (isset($_POST['regions'])) {
    $selectedRegion = $_POST['regions'];
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'transactions_bridge_products',
                'localField' => 'order_id',
                'foreignField' => 'order_id',
                'as' => 'bridge'
            ]
        ],
        [
            '$unwind' => '$bridge'
        ],
        [
            '$lookup' => [
                'from' => 'products',
                'localField' => 'bridge.product_id',
                'foreignField' => 'product_id',
                'as' => 'product'
            ]
        ],
        [
            '$unwind' => '$product'
        ]
    ];

    // Tambahkan filter region jika bukan 'ar'
    if ($selectedRegion !== 'ar') {
        $pipeline[] = [
            '$match' => [
                'region' => $selectedRegion
            ]
        ];
    }

    $pipeline = array_merge($pipeline, [
        [
            '$group' => [
                '_id' => [
                    'product_id' => '$bridge.product_id',
                    'product_name' => '$product.product_name'
                ],
                'totalLoss' => ['$sum' => '$bridge.profit']
            ]
        ],
        [
            '$match' => [
                'totalLoss' => ['$lt' => 0]
            ]
        ],
        [
            '$sort' => ['totalLoss' => 1]
        ],
        [
            '$limit' => 5
        ],
        [
            '$project' => [
                '_id' => 0,
                'product_name' => '$_id.product_name',
                'totalLoss' => 1
            ]
        ]
    ]);

    $result = $transaksi->aggregate($pipeline)->toArray();
    $response = [];

    foreach ($result as $item) {
        $response[] = [
            'product_name' => $item->product_name ?? '',
            'totalLoss' => $item->totalLoss ?? 0
        ];
    }

    echo json_encode($response);
    exit();
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
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Loss-Making Product</h1>
                <div class="flex flex-col md:flex-row items-start md:items-center">
                    <select id="regions" aria-label="Select Region" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                        <option value="" disabled selected>Select Region</option>
                        <option value="ar">All Regions</option>
                        <?php foreach ($regions as $region) : ?>
                            <option value="<?= $region ?>"><?= $region ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 mt-8 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-md rounded-lg p-6 flex flex-col justify-center items-center md:col-span-2" style="height: 100%;">
                    <canvas id="barChart"></canvas>
                </div>

                <div class="flex flex-col gap-6">
                    <!-- Min Sales and Min Profit side by side -->
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                            <div class="text-center text-gray-500">List of Product</div>
                            <div class="text-4xl font-bold text-center text-gray-800" id="minProfit">-</div>
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

            function updateChart(data) {
                if (myChart) {
                    myChart.destroy();
                }

                myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.product_name),
                        datasets: [{
                            label: 'Total Products\' Loss',
                            data: data.map(item => item.totalLoss),
                            backgroundColor: 'red',
                            borderColor: 'red',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    title: function(tooltipItems) {
                                        return data[tooltipItems[0].dataIndex].product_name;
                                    },
                                    label: function(tooltipItem) {
                                        return 'Total Loss: ' + tooltipItem.raw;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            function loadProducts() {
                var selectedRegion = $('#regions').val();
                console.log(selectedRegion);

                $.ajax({
                    type: 'POST',
                    url: 'unprofit.php',
                    data: {
                        regions: selectedRegion
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        console.log(data);
                        updateChart(data);
                    }
                })
            }

            $("#regions").on('change', loadProducts);
        });
    </script>
</body>

</html>
