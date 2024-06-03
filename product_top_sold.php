<?php
// 2014 starts at march
// 2017 ends with january only

require_once "connect.php";

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
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Top 5 Products Sold based On Category and Sub-Category</h1>
            </div>

            <!-- Input Box -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8 justify-start items-start">
                <select id="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                    <option value="" disabled selected>Choose category</option>
                    <?php
                    $categories = $products_collection->distinct('category');
                    ?>

                    <?php foreach ($categories as $category) {
                        echo "<option value='" . $category . "'>" . $category . "</option>";
                    } ?>
                </select>

                <select id="sub_category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                    <option value="" disabled selected>Choose sub-category</option>
                    <?php


                    ?>
                </select>

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

            <div class="chart-container grid grid-cols-2 gap-4">
                <div class="bg-white shadow-md rounded-lg flex flex-col items-center md:col-span-1" style="height: 45rem;">
                    <h1 class="text-2xl text-gray-400 font-bold mt-4 text-center md:mb-0">Top 5 Products Sold based On Category and Sub-Category</h1>
                    <canvas id="chart1" class="mb-16 ml-4 mr-8"></canvas>
                </div>
                <div class="bg-white shadow-md rounded-lg flex flex-col items-center md:col-span-1" style="height: 45rem;">
                    <h1 class="text-2xl text-gray-400 font-bold mt-4 text-center md:mb-0">Product's Statistics based on Category and Sub-Category</h1>
                    <canvas id="chart2" class="mb-16 ml-4 mr-8"></canvas>
                </div>
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
                            label: 'Total Products Sold',
                            data: data.map(item => item.totalQuantity),
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
                                        return 'Total Quantity Sold: ' + tooltipItem.raw;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            $("#category").change(function() {
                var category = $(this).val();
                console.log(category);
                $.ajax({
                    url: 'get_sub_categories.php',
                    type: 'POST',
                    data: {
                        category: category
                    },
                    success: function(data) {
                        $('#sub_category').html(data);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $("#getDataButton").click(function() {
                var category = $("#category").val();
                var sub_category = $("#sub_category").val();
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
                console.log(category, sub_category, month, year);

                $.ajax({
                    url: 'get_top_products_sold.php',
                    type: 'POST',
                    data: {
                        getDataButton: true,
                        category: category,
                        sub_category: sub_category,
                        month: month,
                        year: year
                    },
                    success: function(data) {
                        var parsedData = JSON.parse(data);
                        console.log(parsedData);
                        updateChart('chart1', parsedData);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            function transformData(data) {
                let transformedData = [];
                let categories = [...new Set(data.map(item => item.category))];

                categories.forEach(category => {
                    let categoryData = {
                        label: category,
                        parent: "US Superstore",
                        children: []
                    };

                    let subCategories = data.filter(item => item.category === category);

                    subCategories.forEach(subCategory => {
                        categoryData.children.push({
                            label: subCategory.sub_category,
                            parent: category,
                            value: subCategory.totalSales
                        });
                    });

                    transformedData.push(categoryData);
                });

                let root = {
                    label: "US Superstore",
                    parent: "",
                    children: transformedData
                };

                return root;
            }

            function updateChartSunburst(chartId, data) {
                var data2 = [{
                    type: "sunburst",
                    labels: data.children.flatMap(item => [item.label, ...item.children.map(child => child.label)]),
                    parents: data.children.flatMap(item => [item.parent, ...item.children.map(child => child.parent)]),
                    values: data.children.flatMap(item => [null, ...item.children.map(child => child.value)]),
                    outsidetextfont: {
                        size: 20,
                        color: "#377eb8"
                    },
                    leaf: {
                        opacity: 0.8
                    },
                    marker: {
                        line: {
                            width: 2
                        }
                    },
                }];

                var layout = {
                    margin: {
                        l: 0,
                        r: 0,
                        b: 0,
                        t: 0
                    },
                    sunburstcolorway: ["#636efa", "#ef553b", "#00cc96"],
                };

                Plotly.newPlot(chartId, data2, layout);
            }

            $("#getDataButton").click(function() {
                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'get_products_based.php',
                    type: 'POST',
                    data: {
                        getDataButton: true
                    },
                    success: function(data) {
                        var parsedData = JSON.parse(data);
                        var transformedData = transformData(parsedData);
                        console.log(transformedData);
                        updateChartSunburst('chart2', transformedData.children);
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