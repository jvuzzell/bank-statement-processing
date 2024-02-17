<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\statements;

use Exception;

class TruistConsolidatedTxtProcessor extends StatementProcessorParentClass { 
    private $textfile; 
    private array $accountIdentifiers = [
        'TRUIST DIMENSION CHECKING\s+\d{9}', 
        'PERSONAL MONEY RATE SAVINGS\s+\d{9}'
    ];
 
    private string $withdrawalStartHeading = 'Other withdrawals, debits and service charges'; 
    private string $withdrawalEndHeading = 'Total other withdrawals, debits and service charges\s*=\s*\$(\d{1,6}(?:,\d{3})*\.\d{2})';
    private string $depositStartHeading = 'Deposits, credits and interest\s+DATE\s+DESCRIPTION\s+';
    private string $depositEndHeading = 'Total deposits, credits and interest\s*=\s*\$(\d{1,6}(?:,\d{3})*\.\d{2})';
    private string $transactionPattern = '/(\d{2}\/\d{2}\s+.*?\s+[\d,]+\.\d{2})/';  
    private string $transactionColumnPattern = '/^(\d{2}\/\d{2})\s+(.*?)\s+([\d,]+\.\d{2})\s*$/';
    private string $transactionColumnPatternAlt = '/.*\r(\d{2}\/\d{2})\s+(.*?)\s+([\d,]+\.\d{2})\s*$/';
    private string $withdrawalBalancePattern = '/Total other withdrawals, debits and service charges\s*=\s*\$(\d{1,6}(?:,\d{3})*\.\d{2})/';
    private string $depositBalancePattern = '/Total deposits, credits and interest\s*=\s*\$(\d{1,6}(?:,\d{3})*\.\d{2})/';
    private string $filePath;

    public function __construct(array $statementMeta, string $filePath) 
    {
        parent::__construct($statementMeta);
        $this->textfile = file_get_contents($filePath);
        $this->extractTransactions();
    }

    public function extractTransactions() : void
    { 
        $transactions = array();
        $accountPattern = '/Checking and money market savings accounts\s+(.*?)\s+Questions, comments or errors?/s';
        preg_match($accountPattern, $this->textfile, $accountMatches);
        $this->transactions = $this->extractAccounts($accountMatches[0]);
    }

    private function extractAccounts(string $accountData) : array
    {        
        $transactions = [];
        foreach($this->accountNumbers as $index => $accountNumber) {   
            $transactions[$accountNumber]['transactions'] = [];

            foreach($this->accountIdentifiers as $accountIdentifier) { 
                $accountTransactionsPattern = '/' . $accountIdentifier . $accountNumber . '\s+(.*?)\s+' . $this->depositEndHeading . '/s'; 
                preg_match($accountTransactionsPattern, $accountData, $transactionMatches);

                // Attempt an alternate match (for savings)
                if(!$transactionMatches) {
                    $accountTransactionsPattern = '/' . $accountIdentifier . $accountNumber . '\s+(.*?)\s+Questions, comments or errors/s'; 
                    preg_match($accountTransactionsPattern, $accountData, $transactionMatches);
                }

                if($transactionMatches) {  
                    
                    if(preg_match('/SAVINGS/', $accountIdentifier)) { 
                        $transactions[$accountNumber]['accountType'] = 'savings';
                    }
    
                    if(preg_match('/CHECKING/', $accountIdentifier)) { 
                        $transactions[$accountNumber]['accountType'] = 'checking';
                    }

                    // Calculate Balance
                    preg_match($this->depositBalancePattern, $transactionMatches[0], $depositBalanceMatch);
                    $depositBalance = ($depositBalanceMatch) ? $depositBalanceMatch[1] : 0;
                    
                    preg_match($this->withdrawalBalancePattern, $transactionMatches[0], $withdrawalBalanceMatch);
                    $withdrawalBalance = ($withdrawalBalanceMatch) ? $withdrawalBalanceMatch[1] : 0;
          
                    // Remove commas
                    $number1 = str_replace(',', '', $depositBalance);
                    $number2 = str_replace(',', '', $withdrawalBalance);

                    // Convert to float
                    $float1 = (float)$number1;
                    $float2 = (float)$number2;

                    $transactions[$accountNumber]['balance'] = number_format(($float1 - $float2), 2, '.', '');

                    // Withdrawals 
                    $withdrawalPattern = '/' . $this->withdrawalStartHeading . '\s+(.*?)\s+' . $this->withdrawalEndHeading . '/s';
                    preg_match($withdrawalPattern, $transactionMatches[0], $debitRegexResults); 
 
                    $debitLineItems = $this->extractAcctTransactions($debitRegexResults, 'debit');
                    
                    if($debitLineItems) {
                        $transactions[$accountNumber]['transactions'] = array_merge($transactions[$accountNumber]['transactions'], $debitLineItems);
                    }

                    // Deposits
                    $depositPattern = '/' . $this->depositStartHeading . '\s+(.*?)\s+' . $this->depositEndHeading . '/s';
                    preg_match($depositPattern, $transactionMatches[0], $creditRegexResults); 

                    $creditLineItems = $this->extractAcctTransactions($creditRegexResults, 'credit');

                    if($creditLineItems) { 
                        $transactions[$accountNumber]['transactions'] = array_merge($transactions[$accountNumber]['transactions'], $creditLineItems);
                    }

                }
            }
        }

        return $transactions;
    }
    
    private function extractAcctTransactions(array $regexResults, string $credit_or_debit) : array | bool
    {
        if($regexResults) {
            preg_match_all($this->transactionPattern, $regexResults[0], $transactionResults); 
            $transactions = $this->extractLineItems($transactionResults, $credit_or_debit); 
            
            return (count($transactions)) ? $transactions : false;
        } 

        return false;
    }

    private function extractLineItems(array $regex_results, string $credit_or_debit) : array
    {
        $lineItems = [];
        foreach($regex_results[1] as $transaction) { 
            preg_match($this->transactionColumnPattern, $transaction, $transactionMeta);  
            if($transactionMeta) {
                if(strpos($transactionMeta[0], 'continued')) {
                    preg_match($this->transactionColumnPatternAlt, $transactionMeta[0], $transactionMeta);
                }
                $lineItems[] = [
                    'trans_date' => $transactionMeta[1],
                    'description' => trim($transactionMeta[2]),
                    'credit_or_debit' => $credit_or_debit, 
                    'amount' => $transactionMeta[3],
                ];
            }
        }  

        return $lineItems;
    }

    public function getTransactions() : array 
    {
        return $this->transactions;
    } 
}
