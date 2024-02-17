<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\stats;
class ExpensesByMonth extends StatsParentClass {
    
    public array $report_header = [
        'Statement Period',
        'Transaction Type',
        'Total Amount'
    ];

    public function query(): array 
    {
        $query = "SELECT statement_period, transaction_type, SUM(transaction_amount) as TotalAmount
        FROM all_transactions
        GROUP BY statement_period, transaction_type
        ORDER BY statement_period, transaction_type;";

        $result = $this->dbConnection->executeQuery($query);
        $results = $result->fetchAllAssociative();

        return $results;
    }

    public function formatResults(array|bool $results) : array
    {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['statement_period'], 
                $row['transaction_type'],
                $row['TotalAmount']
            ];
        }

        return $statsArray;
    }
}
