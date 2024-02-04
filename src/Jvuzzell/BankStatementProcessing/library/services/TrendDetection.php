<?php 

namespace Jvuzzell\BankStatementProcessing\library\services;

class TrendDetection {
    private array $transactions = []; 
    private array $repeatingTransactionTerms = [];
    private array $search_exceptions = [];
    private array $search_whitelist = [];
    private array $search_blacklist = [];
    private string $debugOutputFilename = 'trend_log.json';
    private string $debugOutputFilename2 = 'trend_log2.json';

    const TREND_REPORT_HEADER = array( 
        'Recurring Transaction Type',
        'Transaction Report ID',
        'Bank',               // 'CaptialOne'
        'Account Number',     // '1234'
        'Account Type',       // 'Credit', 'Checking', 'Savings'
        'Statement Period',   // 'MM/YY' 
        // 'Valid Statement',    // 'Yes', 'No' 
        'Account Owner',      // 'LastName, FirstName'
        'Transaction Date',   // 'MM/YY'
        'Transaction Type',   // 'Credit', 'Debit'
        'Transaction Desc',   // 'Text here'
        'Transaction Amount'  // '00.00'
    );

    public function __construct(array $transactions, array $searchTermsDirectoryContent, string $search_terms_dir)
    { 
        $this->transactions = $transactions;

        foreach($searchTermsDirectoryContent as $index => $filename) { 
            $handle = fopen($search_terms_dir . $filename, 'r');

            while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
                switch($filename) {
                    case 'common-prepositions.csv': 
                        $this->search_exceptions = array_merge($this->search_exceptions, $data);
                        break;
                    case 'transaction-exceptions.csv':
                        $this->search_exceptions = array_merge($this->search_exceptions, $data);
                        break;
                    case 'transaction-whitelist.csv': 
                        $this->search_whitelist[] = $data;
                        break;
                }
            }
        } 
    }
    
    public static function getReportHeader()
    {
        return self::TREND_REPORT_HEADER;
    }

    public function reportRecurringExpenses() { 
        $commonTerms = $this->createTermManifest($this->transactions, $this->search_exceptions); 
        file_put_contents(TMP_DIR . $this->debugOutputFilename2, json_encode($commonTerms, JSON_PRETTY_PRINT)); 
        $consolidatedTerms = $this->consolidateManifest($commonTerms, $this->transactions, $this->search_whitelist);
        file_put_contents(TMP_DIR . $this->debugOutputFilename, json_encode($consolidatedTerms, JSON_PRETTY_PRINT)); 
        $flattenedTrends = $this->flattenTrendForCsv($consolidatedTerms);

        // file_put_contents(TMP_DIR . $this->debugOutputFilename, json_encode($flattenedTrends, JSON_PRETTY_PRINT)); 

        return $flattenedTrends;
    } 

    function createTermManifest(array $transactions, array $exceptions = []) : array
    {
        $termManifest = [];
    
        foreach ($transactions as $index => $transaction) {
            if (isset($transaction[7])) {
                $terms = preg_split('/\s+/', $transaction[7]);
    
                foreach ($terms as $term) {
                    $lowerCaseTerm = strtolower($term);
    
                    // Check for monetary values and date patterns
                    if (preg_match('/^\d{1,3}(,\d{3})*\.\d{2}$/', $lowerCaseTerm) || 
                        preg_match('/^\d{1,2}\/\d{1,2}$/', $lowerCaseTerm) || 
                        preg_match('/\d{1,2}-[a-z]{3}$/i', $lowerCaseTerm) || 
                        preg_match('/^\d{1,2}-\d{1,2}$/', $lowerCaseTerm)) {
                        continue;
                    }
    
                    // Check for exceptions (including prepositions)
                    if (in_array($lowerCaseTerm, array_map('strtolower', $exceptions))) {
                        continue;
                    }

                    if (!isset($termManifest[$lowerCaseTerm])) {
                        $termManifest[$lowerCaseTerm] = ['count' => 1, 'indices' => [$index]];
                    } else {
                        $termManifest[$lowerCaseTerm]['count']++;
                        $termManifest[$lowerCaseTerm]['indices'][] = $index;
                    }
                }
            }
        }
    
        // Sort the terms by frequency
        uasort($termManifest, function ($a, $b) {
            return $b['count'] - $a['count'];
        });
    
        return $termManifest;
    }

    private function consolidateManifest(array $manifest, array $transactions, array $whitelist): array {
        $consolidatedManifest = []; 
        
        foreach ($manifest as $term => $data) {
            $whitelistTerm = $this->findWhitelistTerm($term, $whitelist);

            // Skip terms not in the whitelist
            if ($whitelistTerm === null) { 
                continue;
            }

            // Create a key from the sorted indices array
            $indicesKey = implode('-', $data['indices']);
            
            if (!isset($consolidatedManifest[$indicesKey])) { 
                $transactionMeta = [];
                foreach($data['indices'] as $index) { 
                    $transactionMeta[] = [
                        'Transaction Report ID' => $index, 
                        'Bank'                 => $transactions[$index][0],
                        'Account Number'       => $transactions[$index][1],
                        'Account Type'         => $transactions[$index][2],
                        'Statement Period'     => $transactions[$index][3],
                        'Account Owner'        => $transactions[$index][4],
                        'Transaction Date'     => $transactions[$index][5],
                        'Transaction Type'     => $transactions[$index][6],
                        'Transaction Desc'     => $transactions[$index][7],
                        'Transaction Amount'   => $transactions[$index][8],
                    ];
                }
                $consolidatedManifest[$indicesKey] = [
                    'terms' => [$whitelistTerm],
                    'indices' => $transactionMeta
                ]; 
            } else {
                if (!in_array($whitelistTerm, $consolidatedManifest[$indicesKey]['terms'])) {
                    $consolidatedManifest[$indicesKey]['terms'][] = $whitelistTerm;
                }
            }
        }
    
        return $consolidatedManifest;
    }
    
    private function findWhitelistTerm(string $term, array $whitelist): ?string {
        foreach ($whitelist as $whitelistEntry) {
            foreach ($whitelistEntry as $whitelistTerm) { 
                if (strpos($term, strtolower($whitelistTerm)) !== false) {
                    return strtolower($whitelistEntry[0]);
                }
            }
        }
        return null;
    }
    

    private function flattenTrendForCsv(array $data) : array
    {
        $flattened = [];

        foreach ($data as $key => $value) {
            $terms = $value['terms'];
            foreach ($value['indices'] as $indexInfo) {
                $flattenedItem = [
                    $terms[0], // Terms as a string
                    $indexInfo['Transaction Report ID'],   // Index
                    $indexInfo['Bank'],
                    $indexInfo['Account Number'],
                    $indexInfo['Account Type'],
                    $indexInfo['Statement Period'],
                    $indexInfo['Account Owner'],
                    $indexInfo['Transaction Date'],
                    $indexInfo['Transaction Type'],
                    $indexInfo['Transaction Desc'],
                    $indexInfo['Transaction Amount']

                ];
                $flattened[] = $flattenedItem;
            }
        }
    
        return $flattened;
    }

}