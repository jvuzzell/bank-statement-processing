<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class ExpenseTotalByTransactionType extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['Recurring Transaction Type', 'Total Amount'];
    }

    public function query(): array {
        $query = "SELECT recurring_transaction_type, SUM(transaction_amount) AS total_amount
                  FROM recurring_expenses
                  WHERE transaction_type = 'debit'
                  GROUP BY recurring_transaction_type
                  ORDER BY total_amount DESC;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['recurring_transaction_type'],
                $row['total_amount']
            ];
        }
        return $statsArray;
    }
}
