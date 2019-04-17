<?php

declare(strict_types=1);

use MouseBattery\MouseBattery;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    echo 'Cannot find the vendor directory, have you executed composer install?' . PHP_EOL;
    echo 'See https://getcomposer.org to get Composer.' . PHP_EOL;
    exit(1);
}

try {
    $tapestry = new MouseBattery();
    $tapestry->run();
} catch (Exception $e) {
    echo 'Uncaught Exception ' . get_class($e) . ' with message: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
    exit(1);
}
