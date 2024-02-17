<?php

function loadReplacementMap($filename) {
    $map = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $map[$data[0]] = $data[1];
        }
        fclose($handle);
    }
    return $map;
}

function replaceTermsInCsv($sourceCsv, $replacementMapCsv, $outputCsv) {
    $replacementMap = loadReplacementMap($replacementMapCsv);
    $transactionAmountColumnIndex = null; // To store the index of the transaction_amount column

    if (($inputHandle = fopen($sourceCsv, "r")) !== FALSE) {
        $outputHandle = fopen($outputCsv, "w");

        // Read the header row and write it to the output as is, while identifying the transaction_amount column index
        if (($header = fgetcsv($inputHandle)) !== FALSE) {
            foreach ($header as $index => $columnName) {
                if ($columnName == 'Transaction Amount') {
                    $transactionAmountColumnIndex = $index;
                }
            }
            fputcsv($outputHandle, $header);
        }

        while (($data = fgetcsv($inputHandle)) !== FALSE) {
            foreach ($data as $key => $value) {
                if ($key === $transactionAmountColumnIndex) {
                    $data[$key] = str_replace(',','',$value); // Remove comma from US currency expression
                } 
                // Check if the value contains any of the keys in the replacement map and replace
                foreach ($replacementMap as $searchTerm => $replaceTerm) {
                    if (strpos($value, $searchTerm) !== false) {
                        $data[$key] = str_replace($searchTerm, $replaceTerm, $value);
                        break; // Assuming only one replacement per field
                    }
                }
                
            }
            fputcsv($outputHandle, $data);
        }

        fclose($inputHandle);
        fclose($outputHandle);
    }
}


// Usage
$searchTermTranslations = SEARCH_TERMS_DIR . 'translations.csv';

$recurringTrendsReportFilename = TMP_DIR . "recurring-expenses_report.csv";
$transactionReportFilename = TMP_DIR . "transactions_report.csv"; 

$outputFilename = TMP_DIR . "reports/transactions_report.csv";
replaceTermsInCsv($transactionReportFilename, $searchTermTranslations, $outputFilename, 'transaction');

$outputFilename = TMP_DIR . "reports/recurring-expenses_report.csv";
replaceTermsInCsv($recurringTrendsReportFilename, $searchTermTranslations, $outputFilename, 'trend');