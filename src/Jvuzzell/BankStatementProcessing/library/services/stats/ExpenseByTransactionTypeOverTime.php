<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class ExpenseByTransactionTypeOverTime extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['Recurring Transaction Type', 'Statement Period', 'Total Spent'];
    }

    public function query(): array {
        $query = "SELECT recurring_transaction_type, statement_period, SUM(transaction_amount) AS total_spent
                  FROM recurring_expenses
                  WHERE transaction_type = 'debit'
                  GROUP BY recurring_transaction_type, statement_period
                  ORDER BY recurring_transaction_type, statement_period;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['recurring_transaction_type'],
                $row['statement_period'],
                $row['total_spent']
            ];
        }
        return $statsArray;
    }
}
