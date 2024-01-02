<?php 

namespace Jvuzzell\BankStatementProcessing\library\controllers;

use Jvuzzell\BankStatementProcessing\library\interfaces\StatementProcessorInterface;

class TruistProcessor implements StatementProcessorInterface { 
    private $textfile; 
    private array $transactions = [];

    public function __construct(string $filePath) {
        $this->textfile = file_get_contents($filePath);
    }

    public function extractTransactions() {
        file_put_contents(TMP_DIR . 'truist_example.txt',$this->textfile);

        $this->extractWithdrawals($this->textfile);
        $this->extractDeposits($this->textfile); 
    }

    private function extractWithdrawals($text) {
        // Define a regex pattern that captures all transactions between the two headings
        $startHeading = 'Other withdrawals, debits and service charges';
        $endHeading = '\s+Total other withdrawals, debits and service charges';
   
        // This regex uses lazy quantifiers to capture as little as possible between the headings
        $withdrawalPattern = "/$startHeading(.*?)$endHeading/s";
    
        preg_match($withdrawalPattern, $text, $matches);
        preg_match_all('/(\d{2}\/\d{2}\s+.*?\s+\d+\.\d{2})/', $matches[1], $debitMatches);

        foreach($debitMatches[1] as $transaction) { 
            preg_match('/(\d{2}\/\d{2})\s+(.*?)\s+([\d,]+\.\d{2})/', $transaction, $meta); 
            if(strpos($meta[2], 'continued') === false) {
                $this->transactions[] = [
                    'trans_date' => $meta[1],
                    'description' => trim($meta[2]),
                    'credit_or_debit' => 'debit', 
                    'amount' => $meta[3],
                ];
            }
        }    
    }
    

    private function extractDeposits($text) {
        // Define a regex pattern that captures all transactions between the two headings
        $depositPattern = "/Deposits, credits and interest\s+DATE\s+DESCRIPTION\s+(.*?)\s+Total deposits, credits and interest\s+\=/s";
    
        preg_match($depositPattern, $text, $matches);
        preg_match_all('/(\d{2}\/\d{2}\s+.*?\s+\d+\.\d{2})/', $matches[1], $creditMatches);
        foreach($creditMatches[1] as $transaction) { 
            preg_match('/(\d{2}\/\d{2})\s+(.*?)\s+([\d,]+\.\d{2})/', $transaction, $meta);
            $this->transactions[] = [
                'trans_date' => $meta[1],
                'description' => trim($meta[2]),
                'credit_or_debit' => 'credit', 
                'amount' => $meta[3],
            ];
        }    
    }

    public function getTransactions() : array {
        return $this->transactions;
    } 
}
