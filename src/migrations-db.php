<?php
use Doctrine\DBAL\DriverManager;

return DriverManager::getConnection([
    'driver' => 'pdo_sqlite', 
    'path'   => __DIR__ . '/tmp/data/analytics-database.db'
]);