<?php 

<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\statements;

use Exception;

class SuntrustCheckingXmlProcessor extends StatementProcessorParentClass { 
    private $textfile; 
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
    
    public function getTransactions() : array 
    {
        return $this->transactions;
    } 
}
