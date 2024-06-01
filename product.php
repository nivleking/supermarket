<?php
require_once "connect.php";
$query = "MATCH (p:Product) RETURN p ORDER BY p.product_name";
$results = $clientNeo->run($query, [], 'default', 'Proyek');

if (isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    $analysisType = $_POST['analysis_type'] ?? 'quantity_sold';

    if ($analysisType == 'quantity_sold') {
        $query = "MATCH (p:Product {product_id: '$productId'})<-[:BUY]-(t:Transaction)
                MATCH (t)-[r:BUY]->(p2:Product)
                WHERE p2.product_id <> '$productId'
                RETURN p2.product_name AS product_name, SUM(r.quantity) AS total_quantity
                ORDER BY total_quantity DESC
                LIMIT 5;";
    } else if ($analysisType == 'market_basket') {
        $query = "MATCH (p:Product {product_id: '$productId'})
                MATCH (t:Transaction)-[:BUY]->(p)
                MATCH (t)-[:BUY]->(p2:Product)
                WHERE p2.product_id <> '$productId'
                RETURN p2.product_name AS product_name, COUNT(*) AS total_quantity
                ORDER BY total_quantity DESC
                LIMIT 5;";
    }

    $results = $clientNeo->run($query);

    $data = [];
    foreach ($results as $result) {
        $data[] = [
            'product_name' => $result->get('product_name'),
            'total_quantity' => $result->get('total_quantity')
        ];
    }
    echo json_encode($data);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket</title>
    <?php include 'components/headers.php'; ?>
    <style>
        .choices {
            width: 25% !important;
        }
    </style>
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
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Product Linkage Analysis</h1>
                <select id="selectProduct" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                    <option value="">Choose Product...</option>
                    <?php foreach ($results as $result) {
                        $node = $result->get('p');
                        echo "<option value='" . $node->getProperty('product_id') . "'>" . $node->getProperty('product_name') . "</option>";
                    } ?>
                </select>


            </div>
            <div class="grid grid-cols-1 mt-4 md:grid-cols-2 gap-4">

                <div class="bg-white shadow-md rounded-lg flex flex-col items-center md:col-span-1" style="height: 45rem;">
                    <h1 class="text-2xl text-gray-400 font-bold mt-4 text-center md:mb-0">Top 5 Related Product By Quantity Sold</h1>
                    <canvas id="chart1" class="mb-16 ml-4 mr-8"></canvas>
                </div>

                <div class="bg-white shadow-md rounded-lg flex flex-col items-center md:col-span-1" style="height: 45rem;">
                    <h1 class="text-2xl text-gray-400 font-bold text-center mb-4 mt-4 md:mb-0">Top 5 Related Product Based on Number of Transactions</h1>
                    <canvas id="chart2" class="mb-16 ml-4 mr-8"></canvas>
                </div>
            </div>


        </main>
    </div>

    <script>
        $(document).ready(function() {
            const ctx = document.getElementById('chart1').getContext('2d');
            let myChart;
            const element = $("#selectProduct").get(0);
            console.log(element);
            const choices = new Choices(element, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
                removeItemButton: true,
            });

            function updateChart(chartId, data) {
                var ctx = document.getElementById(chartId).getContext('2d');
                var label = chartId == "chart1" ? "Total Quantity" : "Frequency";
                if (window[chartId] && typeof window[chartId].destroy === 'function') {
                    window[chartId].destroy();
                }

                window[chartId] = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(item => {
                            const words = item.product_name.split(' ');
                            return words.length > 2 ? words.slice(0, 2).join(' ') + '...' : item.product_name;
                        }),
                        datasets: [{
                            label: label,
                            data: data.map(item => item.total_quantity),
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
                                        return data[tooltipItems[0].dataIndex].product_name;
                                    },
                                    label: function(tooltipItem) {
                                        return label + `: ${tooltipItem.raw}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }



            $("#selectProduct").change(function() {
                var productId = $(this).val();
                if (productId) {
                    Swal.fire({
                        title: 'Loading data...',
                        text: 'Please wait while we fetch the data.',
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    $.ajax({
                        type: 'POST',
                        data: {
                            product_id: productId,
                            analysis_type: 'quantity_sold'
                        },
                        dataType: 'json',
                        success: function(data) {
                            setTimeout(() => {
                                updateChart('chart1', data);
                                Swal.close();
                            }, 2000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('Error fetching data: ' + textStatus, errorThrown);
                            Swal.fire('Error!', 'Failed to fetch data: ' + textStatus, 'error');
                        }
                    });
                    $.ajax({
                        type: 'POST',
                        data: {
                            product_id: productId,
                            analysis_type: 'market_basket'
                        },
                        dataType: 'json',
                        success: function(data) {
                            setTimeout(() => {
                                updateChart('chart2', data);
                                Swal.close();
                            }, 2000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('Error fetching data: ' + textStatus, errorThrown);
                            Swal.fire('Error!', 'Failed to fetch data: ' + textStatus, 'error');
                        }
                    });
                }
            });

        })
    </script>


</body>

</html>