<?php 

namespace Jvuzzell\BankStatementProcessing\library\interfaces;

interface StatsInterface {
    public function query() : array;
    public function getReportHeader() : array;
    public function formatResults(array|bool $results) : array;
}