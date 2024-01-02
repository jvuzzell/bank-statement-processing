<?php 

namespace Jvuzzell\BankStatementProcessing\library\controllers;

use Smalot\PdfParser\Parser as PdfParser;
use Jvuzzell\BankStatementProcessing\library\interfaces\StatementProcessorInterface;

class CapitalOneProcessor implements StatementProcessorInterface { 
    private $pdf; 
    private PdfParser $parser;
    private array $transactions;

    public function __construct(string $filePath) 
    {
        $this->parser = new PdfParser();
        $this->pdf = $this->parser->parseFile($filePath);
        $this->extractTransactions();
    }

    public function extractTransactions() 
    {
        $pages = $this->pdf->getPages();
        $text = '';

        foreach ($pages as $page) {
            $text .= $page->getText();
        }

        // Use regex to find patterns that match transactions
        preg_match_all('/\b(\w{3}\s+\d{1,2})\s+(\w{3}\s+\d{1,2})\s+(.*?)([-+]? ?)\$([\d,]+\.\d{2})\b/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) { 
            $credit_or_debit = ($match[4] == '- ') ? 'debit' : 'credit'; 
            $sign = ($match[4] == '- ') ? '-' : '';

            $this->transactions[] = [
                'trans_date' => $match[1],
                // 'post_date' => $match[2],
                'description' => trim($match[3]), 
                'credit_or_debit' => $credit_or_debit, 
                'amount' => $sign . $match[5],
            ];
        }

        file_put_contents(TMP_DIR . 'capitalone_example.txt',$text);
    }  

    public function getTransactions() : array
    {
        return $this->transactions;
    } 
}