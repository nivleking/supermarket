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
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">State's Total Transactions</h1>

                <select id="state" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto" required>
                    <option value="" disabled selected>Choose state</option>
                    <option value="all_states">All states</option>
                    <?php
                    $states = $client->supermarket->transactions->distinct("state");

                    foreach ($states as $state) {
                        echo "<option value='" . $state . "'>" . $state . "</option>";
                    }
                    ?>
                </select>

                <button id="getDataButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 ms-2 rounded">
                    Get Data
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-md rounded-lg p-6 h-30 justify-center items-center">
                    <div class="text-4xl font-bold text-center text-gray-800" id="totalTransactions">-</div>
                    <div class="text-center text-gray-500">Total Transactions</div>
                </div>
                <div class="bg-white shadow-md rounded-lg p-6 h-30 flex flex-col justify-center items-center">
                    <div class="text-4xl font-bold text-center text-gray-800" id="maxTransactions">-</div>
                    <div class="text-center text-gray-500">Max Transactions</div>
                </div>
                <div class="bg-white shadow-md rounded-lg p-6 h-30 flex flex-col justify-center items-center">
                    <div class="text-4xl font-bold text-center text-gray-800" id="minTransactions">-</div>
                    <div class="text-center text-gray-500">Min Transaction</div>
                </div>
            </div>

            <div class="mt-8 bg-white shadow-md rounded-lg p-6 flex flex-col justify-center items-center md:col-span-2" style="height: 45rem;">
                <canvas id="myChart" class=""></canvas>
            </div>

            <div id="dataTable" class="mt-8"></div>
        </main>
    </div>

    <script>
        let myChart = null;

        function createChart(data) {
            const state = $('#state').val();

            let labels, totalTransactions;
            if (state === 'all_states') {
                labels = data.map(item => item.state);
                totalTransactions = data.map(item => item.total_transactions);
            } else {
                labels = data.map(item => item.city);
                totalTransactions = data.map(item => item.total_transactions);
            }

            const ctx = document.getElementById('myChart').getContext('2d');

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Transactions',
                        data: totalTransactions,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: state === 'all_states' ? 'Total Transactions by State' : 'Total Transactions by City in ' + state
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        $('#getDataButton').click(function() {
            const state = $('#state').val();

            $.ajax({
                url: 'getters/get_state_cities.php',
                type: 'POST',
                data: {
                    getDataButton: true,
                    state: state
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    createChart(data);

                    const totalTransactions = data.reduce((total, item) => total + item.total_transactions, 0);
                    const maxTransactions = Math.max(...data.map(item => item.total_transactions));
                    const minTransactions = Math.min(...data.map(item => item.total_transactions));

                    const maxPercentage = ((maxTransactions / totalTransactions) * 100).toFixed(2);
                    const minPercentage = ((minTransactions / totalTransactions) * 100).toFixed(2);

                    $('#totalTransactions').text(totalTransactions);
                    $('#maxTransactions').text(`${maxTransactions} (${maxPercentage}%)`);
                    $('#minTransactions').text(`${minTransactions} (${minPercentage}%)`);

                    let tableHtml = '<div class="relative overflow-x-auto"><table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 divide-y divide-gray-200"><thead class="text-xs text-gray-100 uppercase bg-gray-300 dark:bg-gray-100 dark:text-gray-400"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Total Transactions</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">Percentage Contribution</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                    data.forEach(item => {
                        const name = state === 'all_states' ? item.state : item.city;
                        const transactions = item.total_transactions;
                        const percentage = ((transactions / totalTransactions) * 100).toFixed(2);
                        tableHtml += `<tr class="hover:bg-gray-100"><td class="px-6 py-4 whitespace-nowrap text-gray-600">${name}</td><td class="px-6 py-4 whitespace-nowrap text-gray-600">${transactions}</td><td class="px-6 py-4 whitespace-nowrap text-gray-600">${percentage}%</td></tr>`;
                    });
                    tableHtml += '</tbody></table></div>';

                    $('#dataTable').html(tableHtml);
                    $('#dataTable table').DataTable({
                        order: [
                            [2, 'desc']
                        ],
                        "pageLength": 5,
                        "paging": true,
                        "searching": true
                    });
                },
                error: function(response) {
                    console.error('Error fetching data:', response);
                }
            });
        });
    </script>
</body>

</html>