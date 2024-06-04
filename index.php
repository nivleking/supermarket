<?php
include 'connect.php';

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
    <?php
    include 'components/navbar.php';
    include 'components/sidebar.php';
    ?>

    <div class="flex">
        <?php include 'components/sidebar.php'; ?>

        <main class="flex-1 p-6 sm:ml-64 mt-12">
            <div class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col md:flex-row justify-between items-start">
                <h1 class="text-3xl font-bold text-start mb-4 md:mb-0">Welcome to Supermarket Analysis</h1>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mb-8 flex flex-col md:flex-row justify-between items-start">
                <p>Navigate to the sidebar menus to check our analysis on supermarket's data.</p>
            </div>

        </main>
    </div>

    <script>

    </script>
</body>

</html>