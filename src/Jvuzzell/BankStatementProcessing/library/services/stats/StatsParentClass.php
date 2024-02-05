<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

abstract class StatsParentClass {
    protected $dbConnection;

    public function __construct($dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    abstract public function generateStat(): array;
}
