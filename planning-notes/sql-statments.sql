-- Expense Analysis --
-- List expense categories
SELECT DISTINCT recurring_transaction_type
FROM recurring_expenses
WHERE transaction_type = 'debit'
ORDER BY recurring_transaction_type;

-- How much I spent in each category
SELECT recurring_transaction_type,
       SUM(transaction_amount) AS total_amount
FROM recurring_expenses
WHERE transaction_type = 'debit'
GROUP BY recurring_transaction_type
ORDER BY total_amount DESC;

-- How much I spent in each category over time
SELECT recurring_transaction_type,
       statement_period,
       SUM(transaction_amount) AS total_spent
FROM recurring_expenses
WHERE transaction_type = 'debit'
GROUP BY recurring_transaction_type, statement_period
ORDER BY recurring_transaction_type, statement_period;

-- Report non-recurring expense (Miscellaneous)
SELECT a.id, a.transaction_date, a.transaction_type, a.transaction_desc, a.transaction_amount 
FROM all_transactions a
LEFT JOIN recurring_expenses r ON a.id = r.transaction_report_id
WHERE r.transaction_report_id IS NULL
AND a.transaction_desc NOT LIKE '%TRUIST ONLINE TRANSFER%'
AND a.transaction_type = "debit"
ORDER BY a.transaction_desc;

-- Miscellaneous Expense Total 
SELECT SUM(t.transaction_amount) AS total_miscellaneous_expenses
FROM (
    SELECT a.id, a.transaction_date, a.transaction_type, a.transaction_desc, a.transaction_amount 
    FROM all_transactions a
    LEFT JOIN recurring_expenses r ON a.id = r.transaction_report_id
    WHERE r.transaction_report_id IS NULL
    AND a.transaction_type = "debit"
) AS t
WHERE t.transaction_desc NOT LIKE '%TRUIST ONLINE TRANSFER%';

-- Total expenses per category
SELECT tt.taxonomy,
       SUM(re.transaction_amount) AS total_spent
FROM recurring_expenses re
JOIN tags_taxonomy tt ON re.recurring_transaction_type = tt.tag
WHERE re.transaction_type = "debit"
GROUP BY tt.taxonomy
ORDER BY total_spent DESC;

-- Expenses per category
SELECT tt.taxonomy, re.recurring_transaction_type, re.transaction_desc
FROM recurring_expenses re
JOIN tags_taxonomy tt ON re.recurring_transaction_type = tt.tag
WHERE re.transaction_type = "debit"
GROUP BY tt.taxonomy,re.transaction_desc
ORDER BY tt.taxonomy DESC;

-- High-value expenses
SELECT *
FROM all_transactions
WHERE transaction_amount > (SELECT AVG(transaction_amount) * 2 FROM all_transactions)
AND transaction_type = 'debit' 
AND transaction_desc NOT LIKE '%TRUIST ONLINE TRANSFER%'
ORDER BY transaction_amount DESC;

-- Top 20 frequent expenses
SELECT recurring_transaction_type, COUNT(*) AS transaction_count
FROM recurring_expenses
WHERE transaction_type = 'debit'
GROUP BY transaction_desc
ORDER BY transaction_count DESC
LIMIT 20;

-- Total monthly transactions
SELECT statement_period,
       SUM(CASE WHEN transaction_type = 'debit' THEN transaction_amount ELSE 0 END) AS total_spending,
       SUM(CASE WHEN transaction_type = 'credit' THEN transaction_amount ELSE 0 END) AS total_income
FROM all_transactions
WHERE transaction_desc NOT LIKE '%TRUIST ONLINE TRANSFER%'
GROUP BY statement_period
ORDER BY statement_period;

-- Monthly income to expense ratio
WITH summary AS (
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
ORDER BY statement_period;

-- Income Analysis --

-- Total income per category
SELECT tt.taxonomy,
       SUM(re.transaction_amount) AS total_spent
FROM recurring_expenses re
JOIN tags_taxonomy tt ON re.recurring_transaction_type = tt.tag
WHERE re.transaction_type = 'credit'
GROUP BY tt.taxonomy
ORDER BY total_spent DESC;

-- Income per category
SELECT tt.taxonomy, re.recurring_transaction_type, re.transaction_desc
FROM recurring_expenses re
JOIN tags_taxonomy tt ON re.recurring_transaction_type = tt.tag
WHERE re.transaction_type = 'credit'
GROUP BY tt.taxonomy,re.transaction_desc
ORDER BY tt.taxonomy DESC;

-- How much I made in each category over time
SELECT recurring_transaction_type,
       statement_period,
       SUM(transaction_amount) AS total_spent
FROM recurring_expenses
WHERE transaction_type = 'credit'
GROUP BY recurring_transaction_type, statement_period
ORDER BY recurring_transaction_type, statement_period; 
