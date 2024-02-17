<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class TotalIncomePerCategory extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['Taxonomy', 'Total Spent'];
    }

    public function query(): array {
        $query = "SELECT tt.taxonomy,
                        SUM(re.transaction_amount) AS total_spent
                FROM recurring_expenses re
                JOIN tags_taxonomy tt ON re.recurring_transaction_type = tt.tag
                WHERE re.transaction_type = 'credit'
                GROUP BY tt.taxonomy
                ORDER BY total_spent DESC;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['taxonomy'],
                $row['total_spent']
            ];
        }
        return $statsArray;
    }
}
