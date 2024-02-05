<?php

use Doctrine\DBAL\DriverManager;
use Jvuzzell\BankStatementProcessing\library\controllers\CacheStats; 

// Path to your SQLite database
$pathToDatabase = TMP_DATA . 'analytics-database.db';

$connectionParams = [
    'driver' => 'pdo_sqlite', 
    'path'   => $pathToDatabase
];

$dbConnection = DriverManager::getConnection($connectionParams);

$cacheStats = new CacheStats($dbConnection);
$cacheStats->routeToStatsService();
