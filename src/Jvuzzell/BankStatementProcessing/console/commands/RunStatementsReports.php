<?php

/**
 * Purpose: This app (this file) exists to create a singular CSV for describing transactions
 *          from every file in the (project_directory)/input/statements/ folder
 */

use Jvuzzell\BankStatementProcessing\library\controllers\StatementProcessorParentClass;
use Jvuzzell\BankStatementProcessing\library\controllers\TrendDetection;
use Jvuzzell\BankStatementProcessing\library\services\BankStatementProcessorFactory;

/**
 * Compile Transaction Report
 */

$statementManifest = manifestDirContent(STATEMENT_DIR); 
$searchTermsManifest = manifestDirContent(SEARCH_TERMS_DIR);
$transactions = [];

$transactionReportFilename = TMP_DIR . "transactions_report.csv";
$transactionReportCsv = fopen($transactionReportFilename, 'w');

$transactionReportHeader = StatementProcessorParentClass::getReportHeader();

fputcsv($transactionReportCsv, $transactionReportHeader);

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


foreach($transactions as $transaction) {
    fputcsv($transactionReportCsv, $transaction);
}

fclose($transactionReportCsv);

/**
 * Compile Trend Report
 */

$recurringTrendsReportFilename = TMP_DIR . "recurring-expenses_report.csv";
$recurringTrendsReportCsv = fopen($recurringTrendsReportFilename, 'w');

$trendDetection = new TrendDetection($transactions, $searchTermsManifest, SEARCH_TERMS_DIR);

$recurringExpenseReport = $trendDetection->reportRecurringExpenses();

$trendReportHeader = TrendDetection::getReportHeader();

fputcsv($recurringTrendsReportCsv, $trendReportHeader);

foreach($recurringExpenseReport as $transaction) {
    fputcsv($recurringTrendsReportCsv, $transaction);
}

fclose($recurringTrendsReportCsv); 