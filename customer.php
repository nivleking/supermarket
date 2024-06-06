<?php
require_once "connect.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket</title>
    <?php include 'components/headers.php'; ?>
</head>

<body class="bg-gray-100">
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Sidebar -->
    <div class="flex">
        <?php include 'components/sidebar.php'; ?>

        <main class="flex-1 p-6 sm:ml-64 mt-12">
            <!-- Title Box -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col md:flex-row justify-between items-start">
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Customer Segmentation on Total Transactions</h1>

                <select id="segment" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto" required>
                    <option value="all_segments" disabled selected>Choose segment</option>
                    <option value="all_segments">All segments</option>
                    <?php
                    $segments = $client->supermarket->customers->distinct("segment");

                    foreach ($segments as $segment) {
                        echo "<option value='" . $segment . "'>" . $segment . "</option>";
                    }
                    ?>
                </select>

                <button id="getDataButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 ms-2 rounded">
                    Get Data
                </button>
            </div>

            <div class="grid grid-cols-1 mt-8 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-md rounded-lg p-6 flex flex-col justify-center items-center md:col-span-2" style="height: 36rem;">
                    <canvas id="chart1" class="mb-16 ml-4 mr-8"></canvas>
                </div>

                <div class="flex flex-col gap-6">
                    <div class="bg-white shadow-md rounded-lg p-6 h-44 flex flex-col justify-center items-center">
                        <div class="text-4xl font-bold text-center text-gray-800" id="totalConsumer">-</div>
                        <div class="text-center text-gray-500" id="labelTotal">Total Transactions in Consumer</div>
                    </div>
                    <div class="bg-white shadow-md rounded-lg p-6 h-44 flex flex-col justify-center items-center">
                        <div class="text-4xl font-bold text-center text-gray-800" id="totalCorporate">-</div>
                        <div class="text-center text-gray-500">Total Transactions in Corporate</div>
                    </div>
                    <div class="bg-white shadow-md rounded-lg p-6 h-44 flex flex-col justify-center items-center">
                        <div class="text-4xl font-bold text-center text-gray-800" id="totalHomeOffice">-</div>
                        <div class="text-center text-gray-500">Total Transactions in Home Office</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            $('#getDataButton').click(function() {
                var segment = $('#segment').val();
                console.log(segment);

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'getters/get_customer_segmentation.php',
                    type: 'POST',
                    data: {
                        getDataButton: true,
                        segment: segment
                    },
                    success: function(response) {
                        console.log(response);
                        var data = JSON.parse(response);
                        Swal.close();

                        updateChart('chart1', data);
                        var totalConsumer = data.filter(item => item.segment === 'Consumer').reduce((total, item) => total + item.total_transactions, 0);
                        var totalCorporate = data.filter(item => item.segment === 'Corporate').reduce((total, item) => total + item.total_transactions, 0);
                        var totalHomeOffice = data.filter(item => item.segment === 'Home Office').reduce((total, item) => total + item.total_transactions, 0);

                        $('#totalConsumer').text(totalConsumer);
                        $('#totalCorporate').text(totalCorporate);
                        $('#totalHomeOffice').text(totalHomeOffice);
                    },
                    error: function(response) {
                        console.log(response);
                        Swal.close();
                    }
                });
            });
        });

        function updateChart(chartId, data) {
            var ctx = document.getElementById(chartId).getContext('2d');
            if (window[chartId] && typeof window[chartId].destroy === 'function') {
                window[chartId].destroy();
            }

            window[chartId] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => item.segment),
                    datasets: [{
                        label: 'Total Transactions',
                        data: data.map(item => item.total_transactions),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'x',
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
                                    return data[tooltipItems[0].dataIndex].segment;
                                },
                                label: function(tooltipItem) {
                                    return 'Total Transactions: ' + tooltipItem.raw;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>

</body>

</html>