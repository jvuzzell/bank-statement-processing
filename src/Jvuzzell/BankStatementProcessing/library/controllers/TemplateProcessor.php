<?php 

namespace Jvuzzell\BankStatementProcessing\library\controllers;

use Smalot\PdfParser\Parser as PdfParser;
use Jvuzzell\BankStatementProcessing\library\interfaces\StatementProcessorInterface;

class TemplateProcessor implements StatementProcessorInterface { 
    private $pdf; 
    private PdfParser $parser;
    private array $transactions; 
    
    // Fill these variables out
    private string $extractionPattern = ''; // Add regex to identify financial data within the statement 
    private string $debugOutputFilename = 'capitalone_example.txt';

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
        preg_match_all($this->extractionPattern, $text, $matches, PREG_SET_ORDER);
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

        file_put_contents(TMP_DIR . $this->debugOutputFilename, $text);
    }  

    public function getTransactions() : array
    {
        return $this->transactions;
    } 
}