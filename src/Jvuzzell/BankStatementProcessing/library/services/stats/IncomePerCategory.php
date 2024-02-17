<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class IncomePerCategory extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['Taxonomy', 'Recurring Transaction Type', 'Transaction Description'];
    }

    public function query(): array {
        $query = "SELECT tt.taxonomy, re.recurring_transaction_type, re.transaction_desc
                  FROM recurring_expenses re
                  JOIN tags_taxonomy tt ON re.recurring_transaction_type = tt.tag 
                  WHERE re.transaction_type = 'credit'
                  GROUP BY tt.taxonomy, re.transaction_desc
                  ORDER BY tt.taxonomy DESC;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['taxonomy'],
                $row['recurring_transaction_type'],
                $row['transaction_desc']
            ];
        }
        return $statsArray;
    }
}
