<?php 

namespace Jvuzzell\BankStatementProcessing\library\controllers;
 
class BankStatementProcessorFactory {
    public static function create(string $bankFormat, string $filePath, string $fileExt, array $statementMeta) 
    {
        $className = "Jvuzzell\\BankStatementProcessing\\library\\services\\statements\\" . $bankFormat . ucfirst($fileExt) . 'Processor'; 
        if (!class_exists($className)) {
            throw new \Exception("Unsupported bank format: {$bankFormat}");
        }
        return new $className($statementMeta, $filePath);
    }
}