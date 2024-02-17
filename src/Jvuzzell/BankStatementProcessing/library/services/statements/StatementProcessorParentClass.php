<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\statements;

use Smalot\PdfParser\Parser as PdfParser;
use Jvuzzell\BankStatementProcessing\library\interfaces\StatementProcessorInterface;
use SimpleXMLElement; 
use DateTime; 

class StatementProcessorParentClass implements StatementProcessorInterface { 
    protected array $transactions; 
    protected array $accountNumbers;   
    protected string | bool $interestCharge = false;

    protected array $statementMeta = array(
        'firstName'        => '', 
        'lastName'         => '',  
        'bankName'         => '', 
        'month'            => '', // MM 
        'year '            => '', // YYYY
        'accountNumbers'   => [], 
        'fileType'         => ''
    );

    const TRANSACTION_REPORT_HEADER = array(
        'Bank',               // 'CaptialOne'
        'Account Number',     // '1234'
        'Account Type',       // 'Credit', 'Checking', 'Savings'
        'Statement Period',   // 'YYYY/MM' 
        // 'Valid Statement',    // 'Yes', 'No' 
        'Account Owner',      // 'LastName, FirstName'
        'Transaction Date',   // 'MM/YY'
        'Transaction Type',   // 'Credit', 'Debit'
        'Transaction Desc',   // 'Text here'
        'Transaction Amount'  // '00.00'
    );
 
    public function __construct(array $statementMeta) 
    {  
        $this->statementMeta = array_merge($this->statementMeta, $statementMeta);
        $this->accountNumbers = $statementMeta['accountNumbers']; 

        $this->statementMeta['bankName'] = $this->setBankName($statementMeta['bankName']);
    }

    public static function getReportHeader()
    {
        return self::TRANSACTION_REPORT_HEADER;
    }

    private function setBankName(string $bankName) : string
    {
        switch($bankName) { 
            case 'CapitalOneConsolidated': 
                return 'Capital One';
            case 'CapitalOneCredit':
                return 'Capital One';
            case 'DiscoverCredit': 
                return 'Discover'; 
            case 'TruistConsolidated': 
                return 'Truist'; 
            case 'WellsFargoChecking': 
                return 'Wells Fargo';
            default:
                return $bankName;
        }
    }

    public function getTransactions() : array
    {
        return $this->transactions;
    } 

    public function getReportTransactions() : array
    { 
        $rptTransactions = []; 
        foreach($this->transactions as $acctNumber => $accountData) { 
            // 1. Validate Balance 
            // 2. Add additional columns

            foreach($accountData['transactions'] as $index => $transaction) {   
                $acctOwner = ucfirst($this->statementMeta['lastName']) . ',' . ucfirst($this->statementMeta['firstName']); 

                $rptTransactions[] = array(
                    $this->statementMeta['bankName'], 
                    $acctNumber,
                    $accountData['accountType'], // Account Type
                    $this->statementMeta['year'] . '/' . $this->statementMeta['month'], // Statement Period
                    $acctOwner,
                    $transaction['trans_date'], 
                    $transaction['credit_or_debit'], 
                    $transaction['description'], 
                    $transaction['amount']
                );
            }
        }

        return $rptTransactions;
    }

    public function extractTransactions() 
    {
        // Implemented in child classes
    }   

    public function isContentInXml(\SimpleXMLElement $element, string $contentToFind) : SimpleXMLElement | bool 
    { 
        $origElement = $element;
        
        if ($element instanceof \SimpleXMLElement) { 
            // Convert the element to a string and check if it contains the desired content 
            if (strpos((string)$element, $contentToFind) !== false) {
                return $origElement;
            }
            
            // If the content is not found, recursively search in child elements
            foreach ($origElement->children() as $child) {
                if ($this->isContentInXml($child, $contentToFind)) { 
                    return $child;
                }
            } 
        }
        
        // Return false if the content is not found in this element or its children
        return false;
    }

    public function findXmlContentElem(\SimpleXMLElement $element, string $contentToFind) : SimpleXMLElement | bool 
    {
        $origElement = $element;
        
        if ($element instanceof \SimpleXMLElement) { 
            // Convert the element to a string and check if it contains the desired content 
            if (strpos((string)$element, $contentToFind) !== false) { 
                if($origElement->getName)
                return $origElement;
            }
            
            // If the content is not found, recursively search in child elements
            foreach ($origElement->children() as $child) {
                if ($this->isContentInXml($child, $contentToFind)) { 
                    return $child;
                }
            } 
        }
        
        // Return false if the content is not found in this element or its children
        return false;
    }

    public function findTableElements($element) : array 
    {
        $tables = [];

        foreach($element->xpath('//TBody') as $tbody) {
            $tables[] = $tbody;
        } 

        foreach($element->xpath('//Table') as $tbody) {
            $tables[] = $tbody;
        } 

        return $tables;
    }
}