<?php

namespace Jvuzzell\BankStatementProcessing\library\services\stats;

class MonthlyIncomeToExpenseRatio extends StatsParentClass {

    public function __construct($dbConnection) {
        parent::__construct($dbConnection);
        $this->report_header = ['Statement Period', 'Total Income', 'Total Spending', 'Net Savings'];
    }

    public function query(): array {
        $query = "WITH summary AS (
                    SELECT statement_period,
                           SUM(CASE WHEN transaction_type = 'debit' THEN transaction_amount ELSE 0 END) AS total_spending,
                           SUM(CASE WHEN transaction_type = 'credit' THEN transaction_amount ELSE 0 END) AS total_income
                    FROM all_transactions
                    WHERE transaction_desc NOT LIKE '%TRUIST ONLINE TRANSFER%'
                    GROUP BY statement_period
                  )
                  SELECT statement_period, total_income, total_spending,
                         (total_income - total_spending) AS net_savings
                  FROM summary
                  ORDER BY statement_period;";

        $result = $this->dbConnection->executeQuery($query);
        return $result->fetchAllAssociative();
    }

    public function formatResults(array|bool $results): array {
        $statsArray = [$this->report_header];
        foreach ($results as $row) {
            $statsArray[] = [
                $row['statement_period'],
                $row['total_income'],
                $row['total_spending'],
                $row['net_savings']
            ];
        }
        return $statsArray;
    }
}