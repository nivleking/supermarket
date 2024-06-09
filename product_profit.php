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
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Top 5 Products' Profit</h1>
            </div>

            <div class="grid grid-cols-2 mt-8 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-6">
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-6">
                        <div class="bg-white shadow-md rounded-lg p-6 flex flex-col items-center md:col-span-1">
                            <div id="totalProfit5Top" class="text-4xl font-bold text-center text-gray-800">-</div>
                            <div class="text-center text-gray-500">Total Top 5 Profit</div>
                        </div>
                        <div class="bg-white shadow-md rounded-lg p-6 flex flex-col items-center md:col-span-1">
                            <div id="totalProfitAll" class="text-4xl font-bold text-center text-gray-800">-</div>
                            <div class="text-center text-gray-500">Total All Profit</div>
                        </div>
                    </div>
                    <div class="bg-white shadow-md rounded-lg p-6 justify-start items-start">
                        <select id="month" multiple>
                            <option value="" disabled>Choose month</option>
                            <option value="01">01 - January</option>
                            <option value="02">02 - February</option>
                            <option value="03">03 - March</option>
                            <option value="04">04 - April</option>
                            <option value="05">05 - May</option>
                            <option value="06">06 - June</option>
                            <option value="07">07 - July</option>
                            <option value="08">08 - August</option>
                            <option value="09">09 - September</option>
                            <option value="10">10 - October</option>
                            <option value="11">11 - November</option>
                            <option value="12">12 - December</option>
                        </select>

                        <select id="year" multiple>
                            <option value="" disabled>Choose year</option>
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
                </div>
                <div class="bg-white shadow-md rounded-lg flex flex-col items-center md:col-span-1" style="height: 46rem;">
                    <canvas id="chart1" class="mt-4 p-8"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script src="components/functions.js"></script>
    <script>
        $(document).ready(function() {
            const monthChoices = new Choices('#month', {
                removeItemButton: true,
                searchEnabled: false,
                allowHTML: true,
            });

            const yearChoices = new Choices('#year', {
                removeItemButton: true,
                searchEnabled: false,
                allowHTML: true,
            });

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
                            title: {
                                display: true,
                                text: 'Top 5 Products\' Profit',
                                font: {
                                    size: 14
                                }
                            },
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
                                        return 'Total Profit: ' + formatNumber(tooltipItem.raw);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            $("#getDataButton").click(function() {
                var months = $("#month").val();
                var years = $("#year").val();

                console.log(months, years);

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'getters/get_products_profit.php',
                    type: 'POST',
                    data: {
                        getDataButton: true,
                        month: months,
                        year: years
                    },
                    success: function(data) {
                        console.log(data);
                        
                        var parsedData = JSON.parse(data);
                        console.log(parsedData);

                        var topProducts = parsedData.topProducts;
                        var total5Profit = 0;
                        topProducts.forEach(product => {
                            total5Profit += product.totalProfit;
                        });
                        $('#totalProfit5Top').text(formatNumber(total5Profit));
                        updateChart('chart1', topProducts);

                        var totalProfit = parsedData.totalProfit;
                        $('#totalProfitAll').text(formatNumber(totalProfit));

                        var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                        var title = "Profit for " + monthNames[months - 1] + ", " + years;
                        $('#title').text(title);
                        Swal.close();
                    },
                    error: function(error) {
                        console.error('Error fetching data: ' + textStatus, errorThrown);
                        Swal.fire('Error!', 'Failed to fetch data: ' + textStatus, 'error');
                    }
                });
            });
        })
    </script>
</body>

</html>