<?php

use Jvuzzell\BankStatementProcessing\library\services\BankStatementProcessorFactory;

// Usage
$directoryPath = STATEMENT_DIR;
$statementManifest = createStatementManifest($directoryPath);


$filename = TMP_DIR . "output.csv";
$handle = fopen($filename, 'w');

// Optional: Add headers to CSV
fputcsv($handle, array('Transaction Date', 'Description', 'Credit/Debit', 'Amount'));

foreach($statementManifest as $statement) {   
    $bankFormat = parseFilename($statement); 
    $accountNumbers = $bankFormat['accountNumbers']; 
    $date = $bankFormat['month'] . '-' . $bankFormat['year'];
    $statementProcessor = BankStatementProcessorFactory::create($bankFormat['bankName'], STATEMENT_DIR . $statement);
    $statementProcessor->extractTransactions();

    // $transactions[$accountNumber][$date] = $statementProcessor->getTransactions();
    $transactions = $statementProcessor->getTransactions(); 
    foreach ($transactions as $transaction) { 
        fputcsv($handle, $transaction);
    }
}

fclose($handle);