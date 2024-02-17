<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class HighValueExpenses extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['ID', 'Bank', 'Account Number', 'Account Type', 'Statement Period', 'Account Owner', 'Transaction Date', 'Transaction Type', 'Transaction Desc', 'Transaction Amount'];
    }

    public function query(): array {
        $query = "SELECT *
                  FROM all_transactions
                  WHERE transaction_amount > (SELECT AVG(transaction_amount) * 2 FROM all_transactions)
                  AND transaction_type = 'debit' 
                  AND transaction_desc NOT LIKE '%TRUIST ONLINE TRANSFER%'
                  ORDER BY transaction_amount DESC;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = array_values($row);
        }
        return $statsArray;
    }
}
