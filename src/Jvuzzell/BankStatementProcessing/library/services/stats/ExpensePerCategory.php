<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class ExpensePerCategory extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['Taxonomy', 'Recurring Transaction Type', 'Transaction Description','Transaction Amount'];
    }

    public function query(): array {
        $query = "SELECT tt.taxonomy, re.recurring_transaction_type, re.transaction_desc, re.transaction_amount
        FROM recurring_expenses re
        JOIN tags_taxonomy tt ON re.recurring_transaction_type = tt.tag 
        WHERE re.transaction_type = 'debit'
        GROUP BY tt.taxonomy, re.transaction_desc
        ORDER BY tt.taxonomy DESC";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['taxonomy'],
                $row['recurring_transaction_type'],
                $row['transaction_desc'], 
                $row['transaction_amount']
            ];
        }
        return $statsArray;
    }
}
