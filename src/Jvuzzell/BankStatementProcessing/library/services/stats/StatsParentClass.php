<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

abstract class StatsParentClass {
    protected $dbConnection;

    public array $report_header = array();

    public function __construct($dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    abstract public function query() : array;

    abstract public function formatResults(array|bool $results) : array;

    public function generateStat() : array 
    {
        $results = $this->query();
        return $this->formatResults($results);
    }

    public function getReportHeader() : array
    {
        return $this->report_header;
    }

}
