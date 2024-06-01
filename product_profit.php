<?php
// 2014 starts at march
// 2017 ends with january only

require_once "connect.php";

$client = new MongoDB\Client("mongodb://localhost:27017");
$bridged_collections = $client->supermarket->transactions_bridge_products;
$transactions_collection = $client->supermarket->transactions;
$products_collection = $client->supermarket->products;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket</title>
    <?php include 'components/headers.php'; ?>
</head>

<body>
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Sidebar -->
    <div class="flex">
        <?php include 'components/sidebar.php'; ?>

        <main class="flex-1 p-6 sm:ml-64 mt-12">
            <!-- Title Box -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col md:flex-row justify-between items-start">
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Top 5 Products' Profit</h1>
            </div>

            <!-- Input Box -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8 justify-start items-start">
                <select id="month" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                    <option value="" disabled selected>Choose month</option>
                    <?php
                    $months = [
                        "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
                    ];
                    ?>
                    <?php foreach ($months as $month) {
                        echo "<option value='" . $month . "'>" . $month . "</option>";
                    } ?>
                </select>

                <select id="year" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                    <option value="" disabled selected>Choose year</option>
                    <?php
                    $years = [
                        "2014", "2015", "2016", "2017",
                    ];
                    ?>
                    <?php foreach ($years as $year) {
                        echo "<option value='" . $year . "'>" . $year . "</option>";
                    } ?>
                </select>
                <button id="getDataButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Get Data
                </button>
            </div>

            <div class="bg-white shadow-md rounded-lg flex flex-col items-center md:col-span-1" style="height: 45rem;">
                <h1 class="text-2xl text-gray-400 font-bold mt-4 text-center md:mb-0">Top 5 Products' Profit</h1>
                <canvas id="chart1" class="mb-16 ml-4 mr-8"></canvas>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            const ctx = document.getElementById('chart1').getContext('2d');
            let myChart;

            function updateChart(chartId, data) {
                var ctx = document.getElementById(chartId).getContext('2d');
                if (window[chartId] && typeof window[chartId].destroy === 'function') {
                    window[chartId].destroy();
                }

                window[chartId] = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.name),
                        datasets: [{
                            label: 'Total Products\' Profit',
                            data: data.map(item => item.totalProfit),
                            backgroundColor: '#19376D',
                            borderColor: '#19376D',
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
                                        return data[tooltipItems[0].dataIndex].name;
                                    },
                                    label: function(tooltipItem) {
                                        return 'Total Profit: ' + tooltipItem.raw;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            $("#getDataButton").click(function() {
                var month = $("#month").val();
                var year = $("#year").val();
                var monthMapping = {
                    "January": "01",
                    "February": "02",
                    "March": "03",
                    "April": "04",
                    "May": "05",
                    "June": "06",
                    "July": "07",
                    "August": "08",
                    "September": "09",
                    "October": "10",
                    "November": "11",
                    "December": "12"
                };

                month = monthMapping[month];
                console.log(month, year);

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'get_top_products_profit.php',
                    type: 'POST',
                    data: {
                        getDataButton: true,
                        month: month,
                        year: year
                    },
                    success: function(data) {
                        var parsedData = JSON.parse(data);
                        console.log(parsedData);
                        updateChart('chart1', parsedData);
                        Swal.close();
                    },
                    error: function(error) {
                        console.log(error);
                        Swal.close();
                    }
                });
            });
        })
    </script>
</body>

</html>