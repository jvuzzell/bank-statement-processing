<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class ExampleStat extends StatsParentClass {
    
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

        // Execute the query and get the Result object
        $result = $this->dbConnection->executeQuery($query);
        
        // Fetch all results as an associative array
        $results = $result->fetchAllAssociative();

        return $results;
    }

    public function formatResults(array|bool $results) : array
    {
        // Preparing the result in the expected format
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
