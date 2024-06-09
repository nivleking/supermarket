<?php
require_once "connect.php";

$products_collection = $client->supermarket->products;
$categories = $products_collection->distinct('category');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket</title>
    <?php include 'components/headers.php'; ?>

    <style>

    </style>
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
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Top 5 Products Sold based On Category and Sub-Category</h1>
            </div>

            <div class="grid grid-cols-2 mt-8 md:grid-cols-2 gap-6">
                <div class="bg-white shadow-md rounded-lg flex flex-col items-center md:col-span-1" style="height: 46rem;">
                    <canvas id="chart1" class="mt-4 p-8"></canvas>
                </div>
                <div class="flex flex-col gap-6">
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-6">
                        <div class="bg-white shadow-md rounded-lg p-6 flex flex-col items-center md:col-span-1">
                            <div id="totalQuantityTop5" class="text-4xl font-bold text-center text-gray-800">-</div>
                            <div class="text-center text-gray-500">Total Top 5 Quantity</div>
                        </div>
                        <div class="bg-white shadow-md rounded-lg p-6 flex flex-col items-center md:col-span-1">
                            <div id="totalQuantityAll" class="text-4xl font-bold text-center text-gray-800">-</div>
                            <div class="text-center text-gray-500">Total All Quantity</div>
                        </div>
                    </div>
                    <div class="bg-white shadow-md rounded-lg p-6 justify-start items-start">
                        <select id="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto" required>
                            <option value="" selected>Choose category</option>
                            <?php foreach ($categories as $category) {
                                echo "<option value='" . $category . "'>" . $category . "</option>";
                            } ?>
                        </select>

                        <select id="sub_category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto mb-6">
                            <option value="" selected>Choose sub-category</option>
                        </select>

                        <select id="month" multiple>
                            <option value="">Choose month</option>

                            <?php
                            $months = [
                                "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12",
                            ];
                            ?>
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
                            <option value="">Choose year</option>
                            <?php
                            $years = [
                                "2014", "2015", "2016", "2017",
                            ];
                            ?>
                            <?php foreach ($years as $year) {
                                echo "<option value='" . $year . "'>" . $year . "</option>";
                            } ?>
                        </select>


                        <button id="getDataButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-8">
                            Get Data
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            function updateTitle(category, subCategory) {
                var title = "Top 5 Products Sold based On Category and Sub-Category";
                if (category && subCategory) {
                    title = "Top 5 Products Sold in " + category + " - " + subCategory;
                } else if (category) {
                    title = "Top 5 Products Sold in " + category;
                }
                $("#title").text(title);
            }

            const monthChoices = new Choices('#month', {
                removeItemButton: true,
                searchEnabled: false,
                allowHTML: true,
                shouldSort: true,
                resetScrollPosition: true,
            });

            const yearChoices = new Choices('#year', {
                removeItemButton: true,
                searchEnabled: false,
                allowHTML: true,
                shouldSort: true,
                resetScrollPosition: true,

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
                            label: 'Total Products Sold',
                            data: data.map(item => item.totalQuantity),
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
                            title: {
                                display: true,
                                text: 'Top 5 Products Sold based On Category and Sub-Category',
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
                    url: 'getters/get_sub_categories.php',
                    type: 'POST',
                    data: {
                        category: category
                    },
                    success: function(data) {
                        $('#sub_category').html('<option value="" selected>Choose sub-category</option>' + data);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $("#sub_category").change(function() {
                var category = $("#category").val();
                var subCategory = $("#sub_category").val();
                updateTitle(category, subCategory);
            });

            $("#getDataButton").click(function() {
                var category = $("#category").val();
                var sub_category = $("#sub_category").val();
                var month = $("#month").val();
                var year = $("#year").val();

                console.log(category, sub_category, month, year);

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'getters/get_products_top.php',
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
                        var topProducts = parsedData.topProducts;
                        var total5Quantity = 0;
                        for (var i = 0; i < topProducts.length; i++) {
                            total5Quantity += topProducts[i].totalQuantity;
                        }
                        $("#totalQuantityTop5").text(total5Quantity);

                        var totalQuantity = parsedData.totalQuantity;
                        updateChart('chart1', topProducts);
                        $("#totalQuantityAll").text(totalQuantity);
                        updateTitle(category, sub_category);
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