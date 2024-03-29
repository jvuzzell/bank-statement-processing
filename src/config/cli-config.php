<?php

require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

$config = new PhpFile(__DIR__ . '/../migrations.php'); // Or use one of the Doctrine\Migrations\Configuration\Configuration\* loaders

$conn = DriverManager::getConnection([
    'driver' => 'pdo_sqlite', 
    'path'   => __DIR__ . '/tmp/data/analytics-database.db'
]);

return DependencyFactory::fromConnection($config, new ExistingConnection($conn));