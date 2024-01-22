<?php 

namespace Jvuzzell\BankStatementProcessing\library\services;

use Jvuzzell\BankStatementProcessing\library\controllers\CapitalOneProcessor;
 
class BankStatementProcessorFactory {
    public static function create(string $bankFormat, string $filePath, string $fileExt, array $statementMeta) 
    {
        $className = "Jvuzzell\\BankStatementProcessing\\library\\controllers\\" . $bankFormat . ucfirst($fileExt) . 'Processor'; 
        if (!class_exists($className)) {
            throw new \Exception("Unsupported bank format: {$bankFormat}");
        }
        return new $className($statementMeta, $filePath);
    }
}