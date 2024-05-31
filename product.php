<?php 
require_once "connect.php";
$query = "MATCH (p:Product) RETURN p ORDER BY p.product_name";
$results = $clientNeo->run($query, [], 'default', 'Proyek');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket</title>
    <!-- CDN for jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <select id="selectProduct" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 md:ml-auto">
                    <option value="">Choose Product...</option>
                    <?php foreach ($results as $result) {
                        $node = $result->get('p');
                        echo "<option value='".$node->getProperty('product_id')."'>".$node->getProperty('product_name') ."</option>";
                    }?>
                </select>


            </div>
            <div class="grid grid-cols-1 mt-4 md:grid-cols-2 gap-4">
                <div class="bg-white shadow-md rounded-lg p-6 flex flex-col justify-center items-center md:col-span-1" style="height: 36rem;">
                    <canvas id="chart1"></canvas>
                </div>

                <div class="bg-white shadow-md rounded-lg p-6 flex flex-col justify-center items-center md:col-span-1" style="height: 36rem;">
                    <canvas id="chart2"></canvas>
                </div>
            </div>


        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script>
        $(document).ready(function(){
            const element = $("#selectProduct").get(0);
            console.log(element);
            const choices = new Choices(element, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
                removeItemButton: true,
            });
        })
    </script>


</body>

</html>
