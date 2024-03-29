<?php

require_once __DIR__ . '/../vendor/autoload.php';

require('config.php');

// Function to determine which command script to run (simplified example)
function getCommandName() {
    global $argv;
    // Simple argument example: php console.php migrate
    return $argv[1] ?? 'help'; // Default to 'help'
}

function displayHelp($commandScripts) {
    echo "Available commands:\n\n";
    foreach ($commandScripts as $command => $details) {
        echo sprintf("  %s\n    Description: %s\n    Usage: %s\n\n",
            $command,
            $details['description'],
            $details['usage']
        );
    }
}

$commandName = getCommandName();

// Mapping command names to their respective script files
$commandScripts = [
    'Migrate' => [
        'description' => 'Executes all outstanding migrations.',
        'usage'       => 'php console.php Migrate',
    ],
    'RunStatementsReports' => [
        'description' => 'Runs reports on bank statements to aggregate every transactions into a single report and targeted trends into another.', 
        'usage'       => 'php console.php RunStatementReports'
    ], 
    'PostProcessReports' => [
        'description' => '(Optional) An opportunity to adjust the transaction and trend reports generated by RunStatementReports.', 
        'usage'       => 'php console.php PostProcessReports'
    ], 
    'ImportCsvToDatabase' => [
        'description' => 'Imports data from CSV files into the database tables.',
        'usage'       => 'php console.php ImportCsvToDatabase',
    ], 
    'GenerateStats' => [
        'description' => 'Generates and caches statistics as CSV files.',
        'usage'       => 'php console.php GenerateStats',
    ]
    // Add more commands as needed
];

if ($commandName === 'help') {
    displayHelp($commandScripts);
} elseif (isset($commandScripts[$commandName])) {
    require BANK_CMD_DIR . $commandName . '.php';
} else {
    echo "Unknown command. Use 'php console.php help' to list all available commands.\n";
}