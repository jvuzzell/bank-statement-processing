<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\statements;

use SimpleXMLElement;
use DateTime; 

class CapitalOneConsolidatedXmlProcessor extends StatementProcessorParentClass { 
    private $xmlDocument;
    private array $accountIdentifiers = [
        '360 Performance Savings...', 
        '360 Checking...'
    ];
    
    private string $debugOutputFilename = 'capitalone_consolidated_log.json';

    public function __construct(array $statementMeta, string $filePath) 
    {
        parent::__construct($statementMeta);
        $this->xmlDocument = simplexml_load_file($filePath);
        $this->extractTransactions();
    }

    public function extractTransactions() 
    { 
        $xmlTables = []; 
        $xmlTables = $this->findTableElements(
            $this->xmlDocument->children()->Document
        );
        
        $acctBalances = $this->getAccountBalances($xmlTables, $this->accountNumbers);  
        $this->transactions = $this->getAcctTransactions($xmlTables, $acctBalances);

        // Set account type
        foreach($acctBalances as $acctNumber => $balance) { 
            $this->transactions[$acctNumber]['accountType'] = $balance['acctType'];
        }

        file_put_contents(TMP_DIR . $this->debugOutputFilename, json_encode($this->transactions, JSON_PRETTY_PRINT));
    } 

    private function getAcctTransactions(array $xmlTables, array $accountBalances) : array
    {
        $acctTransactions = []; 

        foreach($xmlTables as $xmlTable) { 
            $result = $this->isContentInXml(
                $xmlTable, 
                'Closing Balance'
            );    

            if($result) { 
                $transactions = [];
                $balance = 0.00;

                foreach($xmlTable as $xmlKey => $columns) {    
                    if(
                        $columns->TD[2] === null ||
                        $columns->TD[2] == 'Opening Balance' ||
                        preg_match('/Interest Rate Change/', $columns->TD[2])
                    ) {
                        continue;
                    }

                    if($columns->TD[2] == 'Closing Balance') {
                        $balance = (float)str_replace('$', '', $columns->TD[5]); 
                        continue;
                    }
                     
                    if($columns->TD[4] !== null) {
                        $transactionAmount = (float)str_replace(['$',' ', '-', '+'], '', $columns->TD[4]);
                    }
                    
                    $transactionAmount = number_format($transactionAmount, 2, '.', '');
                    
                    $transactions[] = [
                        'trans_date'      => $this->reformatDate((string)$columns->TD[1]), 
                        'description'     => (string)$columns->TD[2],
                        'credit_or_debit' => lcfirst((string)$columns->TD[3]),
                        'amount'          => $transactionAmount
                    ];
                }

                foreach($accountBalances as $acctNumber => $acctBalance) {
                    if($acctBalance['balance'] === $balance) {
                        $acctTransactions[$acctNumber] = [
                            'transactions' => $transactions, 
                            'balance'      => $balance
                        ];
                    }
                }
            }
        }

        return $acctTransactions;
    }

    private function getAccountBalances(array $xmlTables, array $accountNumbers) : array
    {
        $acctBalances = [];

        foreach($xmlTables as $xmlTable) {
            foreach($this->accountIdentifiers as $acctIdentifier) {
                $results = $this->getAccountBalance($xmlTable, $acctIdentifier, $accountNumbers); 
                switch(true) {
                    case count($results) == 1:  
                        $resultKeys = array_keys($results);
                        $acctBalances[$resultKeys[0]] = $results[$resultKeys[0]]; 
                        break; 
                    case count($results) > 1: 
                        foreach($results as $key => $result) { 
                            $acctBalances[$key] = $result;
                        }
                        break; 
                } 
            }
        } 

        return $acctBalances;
    }

    private function getAccountBalance(SimpleXMLElement $xmlTable, string $identifier, array $accountNumbers) 
    {
        $accountTotals = [];

        foreach($accountNumbers as $accountNumber) { 
            $result = $this->isContentInXml(
                $xmlTable, 
                $identifier . $accountNumber
            );   

            if($identifier === '360 Performance Savings...') {
                $acctType = 'savings';
            } 

            if($identifier === '360 Checking...') {
                $acctType = 'checking';
            } 

            if($result && $result->TD[2] !== null) {
                $accountTotals[$accountNumber] = array(
                    'balance'  => (float)str_replace('$', '', $result->TD[2]), 
                    'acctType' => $acctType
                ); // Column with Monthly Ending Balance
            }
        }

        return $accountTotals;
    } 

    private function reformatDate($date) : string | bool
    {  
        // Create a DateTime object from the given date string with the format "M d" (e.g., "Nov 18")
        $dateTime = DateTime::createFromFormat('M d', $date);
    
        // Format the date as "m/d" (e.g., "11/18") 
        if($dateTime) {
            return $dateTime->format('m/d');
        }

        return false;
    }
} 