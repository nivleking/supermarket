<?php 
require_once "connect.php";

$transaksi = $client->supermarket->transactions;
$ship_modes = $transaksi->distinct("ship_mode");

function getTotalTransactions($shipMode, $transaksi) {
    if ($shipMode == "all") {
        $count = $transaksi->countDocuments();
    } else {
        $filter = ['ship_mode' => $shipMode];
        $count = $transaksi->countDocuments($filter);
    }
    return $count;
}



if (isset($_POST['ship_mode'])) {
    $selectedMode = $_POST['ship_mode'];

    // Aggregation pipeline
    $responseTimePipeline = [
        [
            '$project' => [
                'ship_mode' => 1,
                'order_date' => ['$dateFromString' => ['dateString' => '$order_date', 'format' => '%d/%m/%Y']],
                'ship_date' => ['$dateFromString' => ['dateString' => '$ship_date', 'format' => '%d/%m/%Y']],
                'diff_days' => [
                    '$dateDiff' => [
                        'startDate' => ['$dateFromString' => ['dateString' => '$order_date', 'format' => '%d/%m/%Y']],
                        'endDate' => ['$dateFromString' => ['dateString' => '$ship_date', 'format' => '%d/%m/%Y']],
                        'unit' => 'day'
                    ]
                ]
            ]
        ]
    ];

    if ($selectedMode !== 'all') {
        $responseTimePipeline[] = ['$match' => ['ship_mode' => $selectedMode]];
    }

    $responseTimePipeline[] = [
        '$group' => [
            '_id' => ($selectedMode === 'all' ? null : '$ship_mode'),
            'max_diff_days' => ['$max' => '$diff_days'],
            'min_diff_days' => ['$min' => '$diff_days'],
            'avg_diff_days' => ['$avg' => '$diff_days'],
            'transactions' => ['$push' => '$$ROOT']
        ]
    ];

    $responseTimePipeline[] = [
        '$project' => [
            'max_diff_days' => 1,
            'min_diff_days' => 1,
            'avg_diff_days' => 1,
            'countMax' => [
                '$size' => [
                    '$filter' => [
                        'input' => '$transactions',
                        'cond' => ['$eq' => ['$$this.diff_days', '$max_diff_days']]
                    ]
                ]
            ],
            'countMin' => [
                '$size' => [
                    '$filter' => [
                        'input' => '$transactions',
                        'cond' => ['$eq' => ['$$this.diff_days', '$min_diff_days']]
                    ]
                ]
            ]
        ]
    ];

    try {
        $responseTimes = $transaksi->aggregate($responseTimePipeline);
        $responseTimesArray = iterator_to_array($responseTimes);
        $response = [
            'max_diff_days' => $responseTimesArray[0]->max_diff_days ?? 0,
            'avg_diff_days' => round($responseTimesArray[0]->avg_diff_days ?? 0, 2),
            'min_diff_days' => $responseTimesArray[0]->min_diff_days ?? 0,
            'countMax' => $responseTimesArray[0]->countMax ?? 0,
            'countMin' => $responseTimesArray[0]->countMin ?? 0,
            'total' => getTotalTransactions($selectedMode, $transaksi)
        ];
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;    
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
</head>

<body class="bg-gray-100">

    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Sidebar -->
    <div class="flex">
        <?php include 'components/sidebar.php'; ?>

        <!-- Main content -->
        <main class="flex-1 p-6 sm:ml-64 mt-12">
            <!-- Title Box -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col md:flex-row justify-between items-start">
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Statistic Report for Response Time</h1>
                <select id="ship_mode" aria-label="Select Ship Mode" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                    <option value="" disabled selected>Select Ship Mode</option>
                    <option value="all">All Ship Mode</option>
                    <?php foreach($ship_modes as $mode): ?>
                        <option value="<?= $mode ?>"><?= $mode ?></option>
                    <?php endforeach; ?>
                </select>

            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                    <div class="text-4xl font-bold text-center text-gray-800" id="avgResponseTime">-</div>
                    <div class="text-center text-gray-500">Average Response Time</div>
                </div>
                <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                    <div class="text-4xl font-bold text-center text-gray-800" id="maxResponseTime">-</div>
                    <div class="text-center text-gray-500">Max Response Time</div>
                </div>
                <div class="bg-white shadow-md rounded-lg p-6 h-60 flex flex-col justify-center items-center">
                    <div class="text-4xl font-bold text-center text-gray-800" id="minResponseTime">-</div>
                    <div class="text-center text-gray-500">Min Response Time</div>
                </div>
            </div>

            <div class="grid grid-cols-1 mt-8 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-md rounded-lg p-6 flex flex-col justify-center items-center md:col-span-2" style="height: 36rem;">
                    <canvas id="barChart"></canvas>
                </div>
                
                <div class="flex flex-col gap-6">
                    <div class="bg-white shadow-md rounded-lg p-6 h-44 flex flex-col justify-center items-center">
                        <div class="text-4xl font-bold text-center text-gray-800" id="total">-</div>
                        <div class="text-center text-gray-500" id="labelTotal">Total</div>
                    </div>
                    <div class="bg-white shadow-md rounded-lg p-6 h-44 flex flex-col justify-center items-center">
                        <div class="text-4xl font-bold text-center text-gray-800" id="countMax">-</div>
                        <div class="text-center text-gray-500">Count Max</div>
                    </div>
                    <div class="bg-white shadow-md rounded-lg p-6 h-44 flex flex-col justify-center items-center">
                        <div class="text-4xl font-bold text-center text-gray-800" id="countMin">-</div>
                        <div class="text-center text-gray-500">Count Min</div>
                    </div>
                </div>
            </div>


        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script>
        $(document).ready(function(){
            var ctx = $('#barChart').get(0).getContext('2d');
            var myChart; 

            function updateChart(data) {
                if (myChart) {
                    myChart.destroy();
                }
                myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Count Max', 'Count Min'],
                        datasets: [{
                            label: 'Counts of Transactions',
                            data: [data.countMax, data.countMin],
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(153, 102, 255, 0.2)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)', 
                                'rgba(153, 102, 255, 1)' 
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Max and Min Count',
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

            $('#ship_mode').change(function(){
                var selectedMode = $(this).val();
                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    type: 'POST',
                    data: {ship_mode: selectedMode},
                    success: function(response) {
                        var data = JSON.parse(response);
                        console.log(data);
                        setTimeout(function() {
                            $("#labelTotal").text("Total " + (selectedMode === 'all' ? 'All Ship Modes' : selectedMode));
                            $("#total").text(data.total);
                            $('#maxResponseTime').text(data.max_diff_days);
                            $('#avgResponseTime').text(parseFloat(data.avg_diff_days).toFixed(2)); 
                            $('#minResponseTime').text(data.min_diff_days);
                            $('#countMax').text(data.countMax);
                            $('#countMin').text(data.countMin); 
                            updateChart(data);
                            Swal.close();
                        }, 1500);
                    },
                    error: function() {
                        Swal.fire('Error', 'Error fetching data', 'error');
                    }
                });
            });
        })
    </script>


</body>

</html>
