<?php 

namespace Jvuzzell\BankStatementProcessing\library\controllers;

use Jvuzzell\BankStatementProcessing\library\services\stats\StatsParentClass;

class CacheStats {
    private $dbConnection;

    public function __construct($dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function routeToStatsService() {
        $statsDirectory = LIB_DIR . '/services/stats/';
        $statsClasses = glob($statsDirectory . '*.php');

        foreach ($statsClasses as $classFile) {
            require_once $classFile;
            $className = basename($classFile, '.php');
            $fullClassName = "Jvuzzell\\BankStatementProcessing\\library\\services\\stats\\$className";
            if (class_exists($fullClassName) && is_subclass_of($fullClassName, StatsParentClass::class)) {
                $statsInstance = new $fullClassName($this->dbConnection);
                $statData = $statsInstance->generateStat();
                $this->saveStatAsCsv($className, $statData);
            }
        }
    }

    private function saveStatAsCsv($className, $data) { 
        $csvPath = TMP_DIR . 'stats/' . $className . '.csv';
        $fileHandle = fopen($csvPath, 'w');
        foreach ($data as $row) {
            fputcsv($fileHandle, $row);
        }
        fclose($fileHandle);
        echo "Saved $csvPath\n";
    }
}
