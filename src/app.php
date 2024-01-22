<?php

/**
 * Purpose: This app (this file) exists to create a singular CSV for describing transactions
 *          from every file in the (project_directory)/input/statements/ folder
 */

use Jvuzzell\BankStatementProcessing\library\controllers\StatementProcessorParentClass;
use Jvuzzell\BankStatementProcessing\library\services\BankStatementProcessorFactory;

// Usage
$directoryPath = STATEMENT_DIR;
$statementManifest = createStatementManifest($directoryPath);
$transactions = [];

$filename = TMP_DIR . "test_report.csv";
$handle = fopen($filename, 'w');

// Add column headers
$transactionReportHeader = StatementProcessorParentClass::getReportHeader();

fputcsv($handle, $transactionReportHeader);

foreach($statementManifest as $statement) {   
    $statementMeta = parseFilename($statement);  
    $statementFileExt = $statementMeta['fileType'];
    $accountNumbers = $statementMeta['accountNumbers']; 
    $date = $statementMeta['month'] . '-' . $statementMeta['year'];

    $statementFilepath = STATEMENT_DIR . $statement;
    
    $statementProcessor = BankStatementProcessorFactory::create(
                                $statementMeta['bankName'], 
                                $statementFilepath, 
                                $statementFileExt, 
                                $statementMeta
                            );
     
    $transactions = array_merge($transactions, $statementProcessor->getReportTransactions());
}

// Add report content
foreach($transactions as $transaction) {
    fputcsv($handle, $transaction);
}

fclose($handle);

// Step 1 -
//  - ^^ Extract transactions from each statement 
//  - Add each series of transaction to a manifest 
//  - Validate each series against their pronounced balance 
//  - Flag series that do not match their balance 
//  - ^ Add all transaction series to a two dimensional data structure (row by column)

// Step 2 - 
// - Gather and scan statements from 2024
// - Troubleshoot

// Step 3 - 
// - Scan two dimensional structure for patterns of recurring transactions
// - ^ Create a database of recurring patterns
// - Mark rows in two dimensional structure as recurring or independent

// Step 4 - 
// - ^ Create a database that categorizes different types of transactions by vendor type
// - Categorize rows in the two dimensional structure as you best you can

// Step 5 - 
// - Output two dimensional structure as a CSV for consumption by other applications

// $transactions[$accountNumber][$date] = $statementProcessor->getTransactions();
// $transactions = $statementProcessor->getTransactions(); 
// foreach ($transactions as $transaction) { 
//     fputcsv($handle, $transaction);
// }