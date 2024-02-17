<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class ListExpenseTransactionTypes extends StatsParentClass {

    public function __construct($dbConnection) 
    {
        parent::__construct($dbConnection);
        $this->report_header = ['Recurring Transaction Type'];
    }

    public function query(): array 
    {
        $query = "SELECT DISTINCT recurring_transaction_type
                  FROM recurring_expenses
                  WHERE transaction_type = 'debit'
                  ORDER BY recurring_transaction_type;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array 
    {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [$row['recurring_transaction_type']];
        }
        return $statsArray;
    }
}
