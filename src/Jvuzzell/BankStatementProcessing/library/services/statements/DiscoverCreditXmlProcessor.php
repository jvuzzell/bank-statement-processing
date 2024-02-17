<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\statements;

use SimpleXMLElement;

class DiscoverCreditXmlProcessor extends StatementProcessorParentClass { 
    private $xmlDocument;
    private string $debugOutputFilename = 'discover_credit_log.json';

    public function __construct(array $statementMeta, string $filePath) 
    {
        parent::__construct($statementMeta);
        $this->xmlDocument = simplexml_load_file($filePath); 
        $this->extractTransactions();
    }

    public function extractTransactions() 
    {
        $xmlContent = $this->xmlDocument->children();
        $xmlTables = []; 
        $xmlTables = $this->findTableElements(
            $xmlContent
        );

        $acctBalance = $this->getAccountBalance($xmlContent); 
        $this->interestCharge = $this->getAccountInterest($xmlContent); 
        $acctTransactions[$this->accountNumbers[0]] = $this->getAcctTransactions($xmlTables, []); 
        $acctTransactions[$this->accountNumbers[0]]['balance'] =  $acctBalance; 
        $acctTransactions[$this->accountNumbers[0]]['accountType'] = 'credit';

        $this->transactions = $acctTransactions;

        file_put_contents(TMP_DIR . $this->debugOutputFilename, json_encode($acctTransactions, JSON_PRETTY_PRINT));
    }  

    private function getAcctTransactions(array $xmlTables, array $accountBalances) : array
    {
        $acctTransactions = [ 
            'transactions' => [], 
            'balance' => 0.00
        ];  

        $count = 0; 
        foreach($xmlTables as $xmlTable) {  
            $result = $this->isContentInXml(
                $xmlTable, 
                'TRANS.'
            );    

            if($result) {
                $transactions = [];
                $balance = 0.00;
                $skip = false; 
                $offset = false; 
                $credit_or_debit = false;
                
                $xmlTableContent = $xmlTable->TR;
                $length = count($xmlTableContent); 

                for ($i = 0; $i < $length; $i++) {
                    $columns = $xmlTableContent[$i]; 
                    // Sometimes we skip rows because Wells Fargo may split the description between
                    // this row and the previous row. The lookahead helps determine which rows have 
                    // been split into two rows
                    if($skip) {
                        $skip = false;
                        continue;
                    }
                    
                    if((string)$columns->TD[0] === 'DATE ') { 
                        if ((string)$columns->TD[1] === 'PAYMENTS AND CREDITS ') { 
                            $credit_or_debit = 'credit';
                        } else if ((string)$columns->TD[1] === 'PURCHASES ') { 
                            $credit_or_debit = 'debit';
                        } 
                    } 

                    // This is data we don't want
                    if(
                        (string)$columns->TD[0] === 'TRANS. ' ||
                        (string)$columns->TD[0] === 'DATE ' ||
                        (string)$columns->TD[0] === '' ||
                        (string)$columns->TD[0] === 'AMOUNT '
                    ) {
                        continue;
                    }

                    $lookAhead = $xmlTableContent[$i + 1];

                    if($lookAhead !== null && $lookAhead->TD[0] == '') {
                        $description = trim((string)$columns->TD[1]) . ' ' . trim((string)$lookAhead->TD[1]); 
                        $skip = true;
                    } else { 
                        $description = trim((string)$columns->TD[1]);
                    } 

                    // As an exception, there are occasions where the description and transaction amount 
                    // are not where we expected
                    if(trim($description) == '') {
                        $description = (string)$columns->TD[1];  
                        $offset = true;
                    }

                    $transactions[] = [
                        'trans_date'      => trim((string)$columns->TD[0]), 
                        'description'     => $description,
                        'credit_or_debit' => $credit_or_debit,
                        'amount'          => str_replace(['$', '-'], '', trim((string)$columns->TD[3]))
                    ];
                }

                $acctTransactions['transactions'] = array_merge($acctTransactions['transactions'] , $transactions);
            }
        }

        return $acctTransactions;
    }

    private function getAccountInterest(SimpleXMLElement $xmlContent) : string | bool
    { 
        foreach($xmlContent->xpath('//Table') as $xmlTable) { 
            $result = $this->isContentInXml(
                $xmlTable, 
                'TOTAL INTEREST FOR THIS PERIOD '
            );   

            
            if($result) {
                if(preg_match('/\$([\d]+\.[\d]{2})/', (string)$result->TD[1], $match)){
                    return $match[1];
                }
            }
        } 

        return false;
    }

    private function getAccountBalance(SimpleXMLElement $xmlContent) : string | bool
    {
        foreach($xmlContent->xpath('//H4') as $xmlTable) { 
            $result = $this->isContentInXml(
                $xmlTable, 
                'New Balance:'
            );   

            
            if($result) {
                if(preg_match('/\$([\d]+\.[\d]{2})/', (string)$result, $match)){
                    return $match[1];
                }
            }
        } 

        return false;
    }
}