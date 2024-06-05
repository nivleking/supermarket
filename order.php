<?php
require_once "connect.php";


$transaksi = $client->supermarket->transactions;
$ship_modes = $transaksi->distinct("ship_mode");
$region= $transaksi->distinct("region");

function getTotalTransactions($shipMode, $region, $transaksi)
{
    $filter=[];
    if ($shipMode == "all" && ($region =="all" || $region == "")) {
        // echo "1";
        $count = $transaksi->countDocuments();
    }
    else if($shipMode != "all" && ($region =="all" || $region =="") ){
        $filter = ['ship_mode' => $shipMode];
        $count = $transaksi->countDocuments($filter);
        // echo $count;
    }
    else if($shipMode == "all" && $region != "all"){
        // echo "3";
        $filter = ['region' => $region];
        $count = $transaksi->countDocuments($filter);
    }
    else {
        // echo "4";
        $filter= ['ship_mode' => $shipMode, 'region' => $region];
        $count = $transaksi->countDocuments($filter);
    }
    // echo "gagal";


    return $count;
}




if (isset($_POST['ship_mode']) && $_POST['ship_mode'] != '' && $_POST['ship_mode'] != null) {
    // print_r($_POST);
    $selectedMode = $_POST['ship_mode'];
    $selectedRegion= $_POST['region'];
    // echo $selectedMode;

    $responseTimePipeline=[];

    // Aggregation pipeline
    $responseTimePipeline = [
        [
            '$project' => [
                'ship_mode' => 1,
                'region' => 1,
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
    

    if ($selectedMode !== 'all' && $selectedMode !== '') {
        // echo "oke";
        $responseTimePipeline[] = ['$match' => ['ship_mode' => $selectedMode]];
    }

    if($selectedRegion !== 'all' && $selectedRegion !== ''){
        $responseTimePipeline[] = ['$match' => ['region' => $selectedRegion]];
    }
    $groupId = [];

    if ($selectedMode !== 'all' && $selectedMode !== '') {
        $groupId['ship_mode'] = '$ship_mode';
    }

    if ($selectedRegion !== 'all' && $selectedRegion !== '') {
        $groupId['region'] = '$region';
    }

    
    if (empty($groupId)) {
        $groupId = null;  
    }

    

    


    $responseTimePipeline[] = [
        '$group' => [
            
            '_id' => $groupId,
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

    // print_r($responseTimePipeline);

    // print_r($responseTimePipeline);

    try {
        $responseTimes = $transaksi->aggregate($responseTimePipeline);
        $responseTimesArray = iterator_to_array($responseTimes);
        // var_dump($responseTimesArray);
        $response = [
            'max_diff_days' => $responseTimesArray[0]->max_diff_days ?? 0,
            'avg_diff_days' => round($responseTimesArray[0]->avg_diff_days ?? 0, 2),
            'min_diff_days' => $responseTimesArray[0]->min_diff_days ?? 0,
            'countMax' => $responseTimesArray[0]->countMax ?? 0,
            'countMin' => $responseTimesArray[0]->countMin ?? 0,
            'total' => getTotalTransactions($selectedMode, $selectedRegion, $transaksi)
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
    <style>
    .tooltip {
        position: absolute;
        z-index: 10;
        left: 105%; 
        top: 0;
        background-color: white;
        border: 1px solid #d1d5db;
        padding: 8px;
        border-radius: 8px;
        width: 200px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: none;
    }
</style>

    <?php include 'components/headers.php'; ?>
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
                <div>
                    <select id="ship_mode" aria-label="Select Ship Mode" class="mr-4 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                        <option value="" disabled selected>Select Ship Mode</option>
                        <option value="all">All Ship Mode</option>
                        <?php foreach ($ship_modes as $mode) : ?>
                            <option value="<?= $mode ?>"><?= $mode ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="region" aria-label="Select Region" class="mr-4 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                        <option value="" disabled selected>Select Region</option>
                        <option value="all">All Region</option>
                        <?php foreach ($region as $reg) : ?>
                            <option value="<?= $reg ?>"><?= $reg ?></option>
                        <?php endforeach; ?>
                    </select>   
                </div>

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

            $('#ship_mode, #region').change(function() {
                var selectedMode = $("#ship_mode").val();
                var selectedRegion= $("#region").val();

                // console.log(selectedMode);

                if (selectedMode == '' || selectedMode == null) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Please Select Ship Mode',
                        text: 'You need to select a ship mode to show data.',
                        timer: 3000, 
                        showConfirmButton: false
                    });
                    return; 
                }

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    type: 'POST',
                    data: {
                        ship_mode: selectedMode,
                        region: selectedRegion
                    },
                    success: function(response) {
                        // console.log(response);
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