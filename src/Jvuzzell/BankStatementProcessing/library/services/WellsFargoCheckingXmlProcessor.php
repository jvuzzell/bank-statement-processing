<?php 

namespace Jvuzzell\BankStatementProcessing\library\services;

use Jvuzzell\BankStatementProcessing\library\interfaces\StatementProcessorInterface;
use SimpleXMLElement;

class WellsFargoCheckingXmlProcessor extends StatementProcessorParentClass { 
    private $xmlDocument;

    private string $debugOutputFilename = 'wellfargo_checking_log.json';

    public function __construct(array $statementMeta, string $filePath) 
    {
        parent::__construct($statementMeta);
        $this->xmlDocument = simplexml_load_file($filePath); 
        $this->extractTransactions(); 
    }

    public function extractTransactions() 
    {  
        $xmlContent = $this->xmlDocument->children()->Part;
        $xmlTables = []; 
        $xmlTables = $this->findTableElements(
            $xmlContent
        );
        
        $acctBalance = $this->getAccountBalance($xmlContent); 
        $acctTransactions[$this->accountNumbers[0]] = $this->getAcctTransactions($xmlTables, []); 
        $acctTransactions[$this->accountNumbers[0]]['balance'] =  $acctBalance;
        $acctTransactions[$this->accountNumbers[0]]['accountType'] = 'checking';

        $this->transactions = $acctTransactions;
        
        file_put_contents(TMP_DIR . $this->debugOutputFilename, json_encode($acctTransactions, JSON_PRETTY_PRINT));
    } 

    private function getAcctTransactions(array $xmlTables, array $accountBalances) : array
    {
        $acctTransactions = [ 
            'transactions' => [], 
            'balance' => 0.00
        ];  

        foreach($xmlTables as $xmlTable) {
            $result = $this->isContentInXml(
                $xmlTable, 
                'Transaction history'
            );    

            if($result) { 
                $transactions = [];
                $balance = 0.00;
                $skip = false; 
                $offset = false;
                
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

                    // This is data we don't want
                    if(
                        (string)$columns->TH[0] === 'Transaction history ' ||
                        (string)$columns->TH[0] === 'Transaction history (continued) ' ||
                        (string)$columns->TH[0] === 'Date ' ||
                        (string)$columns->TH[0] === '' ||
                        (string)$columns->TH[0] === 'Check ' || 
                        (string)$columns->TH[0] === 'Date Number '
                    ) {
                        continue;
                    }

                    $lookAhead = $xmlTableContent[$i + 1];

                    if($lookAhead !== null && $lookAhead->TD[0] == '') {
                        $description = trim((string)$columns->TD[0]) . ' ' . trim((string)$lookAhead->TD[0]); 
                        $skip = true;
                    } else { 
                        $description = trim((string)$columns->TD[0]);
                    } 

                    // As an exception, there are occasions where the description and transaction amount 
                    // are not where we expected
                    if(trim($description) == '') {
                        $description = (string)$columns->TD[1];  
                        $offset = true;
                    }

                    if($offset) {
                        $transactionAmount = ((string)$columns->TD[2] !== '') ? (string)$columns->TD[2] : (string)$columns->TD[3]; 
                        $credit_or_debit = ((string)$columns->TD[2] !== '') ? 'credit' : 'debit'; 
                        $offset = false; 
                    } else {
                        $transactionAmount = ((string)$columns->TD[1] !== '') ? (string)$columns->TD[1] : (string)$columns->TD[2]; 
                        $credit_or_debit = ((string)$columns->TD[1] !== '') ? 'credit' : 'debit'; 
                    }

                    $transactions[] = [
                        'trans_date'      => trim((string)$columns->TH[0]), 
                        'description'     => trim($description),
                        'credit_or_debit' => trim($credit_or_debit),
                        'amount'          => trim($transactionAmount)
                    ];
                } 

                $acctTransactions['transactions'] = array_merge($acctTransactions['transactions'] , $transactions);
            }
        }

        return $acctTransactions;
    }

    private function getAccountBalance(SimpleXMLElement $xmlContent) : string | bool
    {
        $indicator = '/Ending balance on \$([\d]+\.[\d]{2})';

        foreach($xmlContent->xpath('//P') as $pTag) { 
            $result = $this->isContentInXml(
                $pTag, 
                'Ending balance'
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