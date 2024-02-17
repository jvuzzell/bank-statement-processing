<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class MiscellaneousExpense extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['ID', 'Transaction Date', 'Transaction Type', 'Transaction Description', 'Transaction Amount'];
    }

    public function query(): array {
        $query = "SELECT a.id, a.transaction_date, a.transaction_type, a.transaction_desc, a.transaction_amount
                  FROM all_transactions a
                  LEFT JOIN recurring_expenses r ON a.id = r.transaction_report_id
                  WHERE r.transaction_report_id IS NULL
                  AND a.transaction_desc NOT LIKE '%TRUIST ONLINE TRANSFER%'
                  AND a.transaction_type = 'debit'
                  ORDER BY a.transaction_desc;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['id'],
                $row['transaction_date'],
                $row['transaction_type'],
                $row['transaction_desc'],
                $row['transaction_amount']
            ];
        }
        return $statsArray;
    }
}
