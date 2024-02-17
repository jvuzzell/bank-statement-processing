<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class ExpenseByCategoryOverTime extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['Taxonomy', 'Statement Period', 'Total Spent'];
    }

    public function query(): array {
        $query = "SELECT tt.taxonomy, re.statement_period, SUM(re.transaction_amount) AS total_spent
                  FROM recurring_expenses re
                  JOIN tags_taxonomy tt ON re.recurring_transaction_type = tt.tag 
                  WHERE re.transaction_type = 'debit'
                  GROUP BY tt.taxonomy, re.statement_period
                  ORDER BY tt.taxonomy, re.statement_period DESC;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['taxonomy'],
                $row['statement_period'],
                $row['total_spent']
            ];
        }
        return $statsArray;
    }
}
