<?php 

namespace Jvuzzell\BankStatementProcessing\library\services;

use Jvuzzell\BankStatementProcessing\library\controllers\CapitalOneProcessor;
 
class BankStatementProcessorFactory {
    public static function create($bankFormat, $pdfFilePath) {
        $className = "Jvuzzell\\BankStatementProcessing\\library\\controllers\\" . $bankFormat . 'Processor'; 
        if (!class_exists($className)) {
            throw new \Exception("Unsupported bank format: {$bankFormat}");
        }
        return new $className($pdfFilePath);
    }
}