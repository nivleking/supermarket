<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="space-y-2 font-medium">
            <li>
                <a href="index.php" class="flex items-center p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'text-black' : 'text-gray-300'; ?> rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="w-5 h-5 <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'text-black' : 'text-gray-500'; ?> transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                        <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z" />
                        <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z" />
                    </svg>
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="product_top_sold.php" class="flex items-center p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'product_top_sold.php') ? 'text-black' : 'text-gray-300'; ?> rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5  <?php echo (basename($_SERVER['PHP_SELF']) == 'product_top_sold.php') ? 'text-black' : 'text-gray-500'; ?>  transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                        <path d="M326.3 218.8c0 20.5-16.7 37.2-37.2 37.2h-70.3v-74.4h70.3c20.5 0 37.2 16.7 37.2 37.2zM504 256c0 137-111 248-248 248S8 393 8 256 119 8 256 8s248 111 248 248zm-128.1-37.2c0-47.9-38.9-86.8-86.8-86.8H169.2v248h49.6v-74.4h70.3c47.9 0 86.8-38.9 86.8-86.8z" />
                    </svg>
                    <span class="ms-3">Products Sold</span>
                </a>
            </li>
            <li>
                <a href="product_profit.php" class="flex items-center p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'product_profit.php') ? 'text-black' : 'text-gray-300'; ?> rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 <?php echo (basename($_SERVER['PHP_SELF']) == 'product_profit.php') ? 'text-black' : 'text-gray-500'; ?>  transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor">
                        <path d="M64 64C28.7 64 0 92.7 0 128V384c0 35.3 28.7 64 64 64H512c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H64zm64 320H64V320c35.3 0 64 28.7 64 64zM64 192V128h64c0 35.3-28.7 64-64 64zM448 384c0-35.3 28.7-64 64-64v64H448zm64-192c-35.3 0-64-28.7-64-64h64v64zM288 160a96 96 0 1 1 0 192 96 96 0 1 1 0-192z" />
                    </svg>
                    <span class="ms-3">Products' Profit</span>
                </a>
            </li>
            <li>
                <a href="unprofit.php" class="flex items-center p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'unprofit.php') ? 'text-black' : 'text-gray-300'; ?> rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 <?php echo (basename($_SERVER['PHP_SELF']) == 'unprofit.php') ? 'text-black' : 'text-gray-500'; ?> transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm0 3c5.514 0 10 4.486 10 10s-4.486 10-10 10S2 18.514 2 13 6.486 3 12 3zm0 4c-.552 0-1 .448-1 1v4c0 .552.448 1 1 1s1-.448 1-1V8c0-.552-.448-1-1-1zm0 12c-1.654 0-3-1.346-3-3s1.346-3 3-3 3 1.346 3 3-1.346 3-3 3zm0-5c-.552 0-1 .448-1 1v4c0 .552.448 1 1 1s1-.448 1-1v-4c0-.552-.448-1-1-1z" />
                    </svg>
                    <span class="ms-3">Loss-Making Product</span>
                </a>
            </li>

            <li>
                <a href="order.php" class="flex items-center p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'order.php') ? 'text-black' : 'text-gray-300'; ?> rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 <?php echo (basename($_SERVER['PHP_SELF']) == 'order.php') ? 'text-black' : 'text-gray-500'; ?>  transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                        <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z" />
                    </svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Response Analysis</span>
                </a>
            </li>
            <li>
                <a href="product.php" class="flex items-center p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'product.php') ? 'text-black' : 'text-gray-300'; ?> rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 <?php echo (basename($_SERVER['PHP_SELF']) == 'product.php') ? 'text-black' : 'text-gray-500'; ?>  transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                        <path d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h13.84a2 2 0 0 0 1.994-2.153L17 5.923ZM7 9a1 1 0 0 1-2 0V7h2v2Zm0-5a2 2 0 1 1 4 0v1H7V4Zm6 5a1 1 0 1 1-2 0V7h2v2Z" />
                    </svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Product Linkage</span>
                </a>
            </li>
            <li>
                <a href="state.php" class="flex items-center p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'state.php') ? 'text-black' : 'text-gray-300'; ?> rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 <?php echo (basename($_SERVER['PHP_SELF']) == 'state.php') ? 'text-black' : 'text-gray-500'; ?>  transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor">
                        <path d="M64 64C28.7 64 0 92.7 0 128V384c0 35.3 28.7 64 64 64H512c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H64zm64 320H64V320c35.3 0 64 28.7 64 64zM64 192V128h64c0 35.3-28.7 64-64 64zM448 384c0-35.3 28.7-64 64-64v64H448zm64-192c-35.3 0-64-28.7-64-64h64v64zM288 160a96 96 0 1 1 0 192 96 96 0 1 1 0-192z" />
                    </svg>
                    <span class="ms-3">State Analysis</span>
                </a>
            </li>
            <li>
                <a href="region.php" class="flex items-center p-2 <?php echo (basename($_SERVER['PHP_SELF']) == 'region.php') ? 'text-black' : 'text-gray-300'; ?> rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <svg class="flex-shrink-0 w-5 h-5 <?php echo (basename($_SERVER['PHP_SELF']) == 'region.php') ? 'text-black' : 'text-gray-500'; ?>  transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5z" />
                    </svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Region Sales & Profit</span>
                </a>
            </li>

        </ul>
    </div>
</aside>