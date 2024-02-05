<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

use Jvuzzell\BankStatementProcessing\library\interfaces\StatsInterface;

class GetRecurringExpensesByMonth extends StatsParentClass implements StatsInterface {
    
    const REPORT_HEADER = [

    ];

    public function query(): array 
    {
        return array();
    }

    public function getReportHeader() : array
    {
        return array();
    }

    public function formatResults() : array
    {
        return array();
    }

    public function generateStat() : array 
    {
        // Implement the logic to query the database and generate stats
        // For example, return an array of monthly expenses
        return [
            ['Month', 'Total Expenses'],
            ['January', 1000],
            ['February', 1200],
            // Add more months as needed
        ];
    }
}
