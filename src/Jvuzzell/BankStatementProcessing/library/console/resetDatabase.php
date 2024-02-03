<?php

namespace Jvuzzell\BankStatementProcessing\library\migrations;
require_once __DIR__ . '/../../../../../vendor/autoload.php';

use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Application;

$application = new Application('Doctrine Migrations');
$application->setAutoExit(false);

// Add commands
$application->addCommands([
    new MigrateCommand(),
    new RollupCommand(),
]);

// Status
$input = new ArrayInput(['command' => 'migrations:status']);
$application->run($input, new ConsoleOutput());

// Roll up to squash migrations history (optional)
$input = new ArrayInput(['command' => 'migrations:rollup']);
$application->run($input, new ConsoleOutput());

// Execute migrations
$input = new ArrayInput([
    'command' => 'migrations:migrate',
    '--no-interaction' => true,
]);
$application->run($input, new ConsoleOutput());

echo "Database has been reset and migrations re-applied.\n";
