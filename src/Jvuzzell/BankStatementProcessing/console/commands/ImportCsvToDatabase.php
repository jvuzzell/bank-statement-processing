<?php

use Jvuzzell\BankStatementProcessing\library\services\commands\ImportCsvToDatabase;

// Instantiate and run the import
$importer = new ImportCsvToDatabase();
$importer->importCsv(TMP_REPORTS . 'recurring-expenses_report.csv', 'recurring_expenses');
$importer->importCsv(TMP_REPORTS . 'transactions_report.csv', 'all_transactions');
$importer->importTagsFromCsv(SEARCH_TERMS_DIR . 'transaction-categories.csv', 'tags_taxonomy');
