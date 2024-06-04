<?php
require_once "connect.php";
$transactions_collection = $client->supermarket->transactions;

$states = $transactions_collection->distinct('state');
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
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">State Analysis on Cities' Sales</h1>
            </div>

            <!-- Input Box -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8 justify-start items-start" style="display: flex; align-items: center;">
                <select id="state" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto" style="margin-right: 12px;">
                    <option value="all_states" selected>All states</option>

                    <?php foreach ($states as $state) {
                        echo "<option value='" . $state . "'>" . $state . "</option>";
                    } ?>
                </select>

                <button id="getDataButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Get Data
                </button>
            </div>
            <div id="chart1"></div>
        </main>
    </div>

    <script>
        let colorscale = ['#636EFA', '#EF553B', '#00CC96', '#AB63FA', '#FFA15A', '#19D3F3', '#FF6692', '#B6E880', '#FF97FF', '#FECB52'];

        let stateColors = {
            'California': '#636EFA',
            'Washington': '#EF553B',
            'Texas': '#00CC96',
            'New York': '#AB63FA',
            'Florida': '#FFA15A',
            'Georgia': '#19D3F3',
            'Illinois': '#FF6692',
            'Pennsylvania': '#B6E880',
            'Ohio': '#FF97FF',
            'Michigan': '#FECB52'
        };

        function updateChart(chartId, data) {
            let labels = [];
            let parents = [];
            let values = [];
            let totalQuantity = [];
            let avgQuantity = [];
            let maxQuantity = [];
            let minQuantity = [];
            let totalSales = [];
            let avgSales = [];
            let maxSales = [];
            let minSales = [];

            data.forEach(function(city) {
                labels.push(city.city);
                parents.push(city.state);
                values.push(city.totalSales);
                totalQuantity.push(city.totalQuantity);
                avgQuantity.push(city.avgQuantity);
                maxQuantity.push(city.maxQuantity);
                minQuantity.push(city.minQuantity);
                totalSales.push(city.totalSales);
                avgSales.push(city.avgSales);
                maxSales.push(city.maxSales);
                minSales.push(city.minSales);
            });

            let customData = labels.map((_, i) => ({
                totalQuantity: totalQuantity[i],
                avgQuantity: avgQuantity[i],
                maxQuantity: maxQuantity[i],
                minQuantity: minQuantity[i],
                totalSales: totalSales[i],
                avgSales: avgSales[i],
                maxSales: maxSales[i],
                minSales: minSales[i]
            }));

            let data2 = [{
                type: "treemap",
                labels: labels,
                parents: parents,
                marker: {
                    colors: colorscale
                },
                values: values,
                textinfo: "label+value",
                customdata: customData,
                hovertemplate: '<b>%{label}</b><br>' +
                    'Total Sales: %{value}<br>' +
                    // 'Total Quantity : %{customdata.totalQuantity}<br>' +
                    // 'Total Sales: %{customdata.totalSales}<br>' +
                    // 'Max Quantity: %{customdata.maxQuantity}<br>' +
                    'Max Sales: %{customdata.maxSales}<br>' +
                    // 'Min Quantity: %{customdata.minQuantity}<br>' +
                    'Min Sales: %{customdata.minSales}<br>' +
                    // 'Average Quantity: %{customdata.avgQuantity}<br>' +
                    'Average Sales: %{customdata.avgSales}<br>' +
                    '<extra></extra>'

            }];

            let layout = {
                width: 1500,
                height: 600,
                title: 'State Analysis in ' + data[0].state,
            };

            Plotly.react(chartId, data2, layout);
        }

        function updateChartAllStates(chartId, data) {
            let transformedData = [];
            let labels = ['US States'];
            let parents = [''];
            let values = [0];
            let totalQuantities = [0];
            let avgQuantities = [0];
            let maxQuantities = [0];
            let minQuantities = [0];
            let totalSales = [0];
            let avgSales = [0];
            let maxSales = [0];
            let minSales = [0];

            data.forEach(function(item) {
                let state = transformedData.find(state => state.state === item.state);
                if (!state) {
                    state = {
                        state: item.state,
                        count: 0,
                        totalSales: 0,
                        totalQuantity: 0,
                        cities: [],
                        maxQuantity: 0,
                        minQuantity: 0,
                        maxSales: 0,
                        minSales: 0,
                        count: 0
                    };
                    transformedData.push(state);
                    labels.push(state.state);
                    parents.push('US States');
                }
                state.totalSales += item.totalSales;
                state.totalQuantity += item.totalQuantity;
                state.count += item.count;

                values[labels.indexOf(state.state)] = state.totalSales;

                totalQuantities[labels.indexOf(state.state)] = state.totalQuantity;
                totalSales[labels.indexOf(state.state)] = item.totalSales;

                state.maxQuantity = Math.max(state.maxQuantity, item.maxQuantity);
                state.minQuantity = Math.min(state.minQuantity, item.minQuantity);

                maxQuantities[labels.indexOf(state.state)] = state.maxQuantity;

                if (state.minQuantity == 0) {
                    state.minQuantity = item.minQuantity;
                }
                minQuantities[labels.indexOf(state.state)] = state.minQuantity;

                state.maxSales = Math.max(state.maxSales, item.maxSales);

                if (state.minSales == 0) {
                    state.minSales = item.minSales;
                }
                state.minSales = Math.min(state.minSales, item.minSales);

                maxSales[labels.indexOf(state.state)] = state.maxSales;
                minSales[labels.indexOf(state.state)] = state.minSales;

                avgSales[labels.indexOf(state.state)] = item.avgSales;

                let city = state.cities.find(city => city.city === item.city);
                if (!city) {
                    city = {
                        city: item.city,
                        sales: 0,
                        quantity: 0,
                        avgQuantity: 0,
                        maxQuantity: 0,
                        minQuantity: 0,
                        avgSales: 0,
                        maxSales: 0,
                        minSales: 0,
                        count: 0
                    };
                    state.cities.push(city);
                    labels.push(city.city);
                    parents.push(state.state);
                }
                city.sales += item.totalSales;
                city.quantity += item.totalQuantity;
                city.count += item.count;

                values[labels.indexOf(city.city)] = city.sales;
                totalQuantities[labels.indexOf(city.city)] = city.quantity;
                totalSales[labels.indexOf(city.city)] = item.totalSales;

                avgQuantities[labels.indexOf(city.city)] = item.avgQuantity;
                maxQuantities[labels.indexOf(city.city)] = item.maxQuantity;
                minQuantities[labels.indexOf(city.city)] = item.minQuantity;

                avgSales[labels.indexOf(city.city)] = item.avgSales;
                maxSales[labels.indexOf(city.city)] = item.maxSales;
                minSales[labels.indexOf(city.city)] = item.minSales;
            });

            let customData = labels.map((_, i) => ({
                totalQuantity: totalQuantities[i],
                avgQuantity: avgQuantities[i],
                maxQuantity: maxQuantities[i],
                minQuantity: minQuantities[i],
                totalSales: totalSales[i],
                avgSales: avgSales[i],
                maxSales: maxSales[i],
                minSales: minSales[i]
            }));

            let data2 = [{
                type: "treemap",
                labels: labels,
                parents: parents,
                marker: {
                    colors: parents.map(parent => stateColors[parent])
                },
                values: values,
                textinfo: "label+text+value",
                customdata: customData,
                hovertemplate: '<b>%{label}</b><br>' +
                    'Total Sales: %{value}<br>' +
                    // 'Total Quantity: %{customdata.totalQuantity}<br>' +
                    // 'Total Sales: %{customdata.totalSales}<br>' +
                    // 'Max Quantity: %{customdata.maxQuantity}<br>' +
                    'Max Sales: %{customdata.maxSales}<br>' +
                    // 'Min Quantity: %{customdata.minQuantity}<br>' +
                    'Min Sales: %{customdata.minSales}<br>' +
                    // 'Average Quantity: %{customdata.avgQuantity}<br>' +
                    'Average Sales: %{customdata.avgSales}<br>' +
                    '<extra></extra>'
            }];

            let layout = {
                width: 1500,
                height: 600,
                title: 'US States Analysis on its Cities\' Sales',
            };

            Plotly.react(chartId, data2, layout);
        }

        $(document).ready(function() {
            $('#getDataButton').click(function() {
                let state = $('#state').val()

                console.log(state)

                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while we fetch the data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'getters/get_state_cities.php',
                    type: 'POST',
                    data: {
                        getDataButton: true,
                        state: state
                    },
                    success: function(data) {
                        var parsedData = JSON.parse(data)
                        console.log(parsedData)
                        if (state == "all_states") {
                            updateChartAllStates('chart1', parsedData)
                        } else {
                            updateChart('chart1', parsedData)
                        }
                        Swal.close();
                    },
                    error: function(error) {
                        console.log(error)
                        Swal.close();
                    }
                })
            })
        })
    </script>
</body>

</html>