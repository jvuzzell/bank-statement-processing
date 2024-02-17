<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class Top20FrequentExpenses extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['Transaction Type', 'Transaction Count'];
    }

    public function query(): array {
        $query = "SELECT recurring_transaction_type, COUNT(*) AS transaction_count
                  FROM recurring_expenses
                  WHERE transaction_type = 'debit'
                  GROUP BY transaction_desc
                  ORDER BY transaction_count DESC
                  LIMIT 20;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['recurring_transaction_type'],
                $row['transaction_count']
            ];
        }
        return $statsArray;
    }
}
