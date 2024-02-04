<?php 

namespace Jvuzzell\BankStatementProcessing\library\services;

use Smalot\PdfParser\Parser as PdfParser;
use Jvuzzell\BankStatementProcessing\library\interfaces\StatementProcessorInterface;
use DateTime; 

class CapitalOneCreditPdfProcessor extends StatementProcessorParentClass { 
    private $pdf; 
    private PdfParser $parser;
 
    private string $transactionPattern = '/\b(\w{3}\s+\d{1,2})\s+(\w{3}\s+\d{1,2})\s+(.*?)([-+]? ?)\$([\d,]+\.\d{2})\b/';
    private string $totalTransactionsPattern = '/Total Transactions for This Period\s*\$(\d+\.\d{2})/';

    public function __construct(array $statementMeta, string $filePath) 
    {
        parent::__construct($statementMeta);
        $this->parser = new PdfParser();
        $this->pdf = $this->parser->parseFile($filePath);
        $this->extractTransactions();
    }

    public function extractTransactions() 
    {
        $pages = $this->pdf->getPages();
        $text = '';
        $transactions = [];
        $transactionTotal = null;

        foreach ($pages as $page) {
            $text .= $page->getText();
        }
       
        // Transaction Total 
        preg_match($this->totalTransactionsPattern, $text, $totalTransactionMatch);

        if(count($totalTransactionMatch) > 1) { 
            $transactionTotal = $totalTransactionMatch[1];
        }

        // Transactions
        preg_match_all($this->transactionPattern, $text, $transactionMatches, PREG_SET_ORDER);
        foreach ($transactionMatches as $match) { 
            $credit_or_debit = ($match[4] == '- ') ? 'debit' : 'credit'; 
            $sign = ($match[4] == '- ') ? '' : '';

            $transactions[] = [
                'trans_date' => $this->reformatDate($match[1]),
                // 'post_date' => $match[2],
                'description' => trim($match[3]), 
                'credit_or_debit' => $credit_or_debit, 
                'amount' => $sign . $match[5],
            ];
        }
        
        $this->transactions[$this->accountNumbers[0]] = [
            'transactions' => $transactions, 
            'balance'      => $transactionTotal,
            'accountType'  => 'credit'
        ]; 

        file_put_contents(TMP_DIR . 'capitalone_credit_log.json', json_encode($this->transactions, JSON_PRETTY_PRINT));
    }  

    public function getTransactions() : array
    {
        return $this->transactions;
    } 

    private function reformatDate($date) { 
        // Create a DateTime object from the given date string with the format "M d" (e.g., "Nov 18")
        $dateTime = DateTime::createFromFormat('M d', $date);
    
        // Format the date as "m/d" (e.g., "11/18")
        if($dateTime) {
            return $dateTime->format('m/d');
        }
        
        return false;
    }
}