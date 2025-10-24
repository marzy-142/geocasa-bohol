<?php

echo "Testing Enhanced Broker Features...\n";

// Test if the controller file exists and is readable
if (file_exists('app/Http/Controllers/Broker/EnhancedTransactionController.php')) {
    echo "✓ EnhancedTransactionController.php exists\n";
} else {
    echo "✗ EnhancedTransactionController.php not found\n";
}

// Test if the Vue components exist
$vueFiles = [
    'resources/js/Pages/Broker/EnhancedTransactions.vue',
    'resources/js/Pages/Broker/EnhancedTransactionCreate.vue', 
    'resources/js/Pages/Broker/EnhancedTransactionShow.vue',
    'resources/js/Pages/Broker/Analytics.vue'
];

foreach ($vueFiles as $file) {
    if (file_exists($file)) {
        echo "✓ " . basename($file) . " exists\n";
    } else {
        echo "✗ " . basename($file) . " not found\n";
    }
}

// Test if routes are properly defined
$routeFile = 'routes/web.php';
if (file_exists($routeFile)) {
    $content = file_get_contents($routeFile);
    if (strpos($content, 'EnhancedTransactionController') !== false) {
        echo "✓ Enhanced broker routes defined\n";
    } else {
        echo "✗ Enhanced broker routes not found\n";
    }
}

// Test if the controller can be loaded
try {
    require_once 'vendor/autoload.php';
    $controller = new App\Http\Controllers\Broker\EnhancedTransactionController();
    echo "✓ Controller can be instantiated\n";
} catch (Exception $e) {
    echo "✗ Controller error: " . $e->getMessage() . "\n";
}

// Test if the documentation exists
if (file_exists('BROKER_UX_ENHANCEMENTS.md')) {
    echo "✓ Documentation file exists\n";
} else {
    echo "✗ Documentation file not found\n";
}

echo "\nTesting completed!\n";
