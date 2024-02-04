<?php

namespace Jvuzzell\BankStatementProcessing\library\migrations;

use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Application;

$application = new Application('Doctrine Migrations');
$application->setAutoExit(false);

// Add commands
$application->addCommands([
    new MigrateCommand()
]);

// Execute migrations
$input = new ArrayInput([
    'command' => 'migrations:migrate',
    '--no-interaction' => true,
]);
$application->run($input, new ConsoleOutput());

echo "Database migrations applied.\n";
