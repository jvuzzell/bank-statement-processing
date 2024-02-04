<?php

use Doctrine\DBAL\DriverManager;

class ImportCsvToDatabase {
    private $connection;

    public function __construct() {
        $this->initializeDatabaseConnection();
    }

    private function initializeDatabaseConnection() {
        // Path to your SQLite database
        $pathToDatabase = TMP_DATA . 'analytics-database.db';

        $connectionParams = [
            'driver' => 'pdo_sqlite', 
            'path'   => $pathToDatabase
        ];
        $this->connection = DriverManager::getConnection($connectionParams);
    }

    public function importCsv($filePath, $tableName) {
        if (!file_exists($filePath)) {
            echo "File not found: $filePath\n";
            return;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            echo "Failed to open file: $filePath\n";
            return;
        }

        $header = fgetcsv($handle); // Assuming first row is header

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $this->insertRow($tableName, $data);
        }

        fclose($handle);
        echo "Imported $filePath into $tableName\n";
    }

    private function insertRow($tableName, $data) {
        // Convert keys to field names suitable for SQL, quoting them to handle spaces and reserved words
        $fields = array_map(function($field) {
            return str_replace(' ','_',strtolower($field));
        }, array_keys($data));

        $values = array_values($data);
        $placeholders = array_fill(0, count($fields), '?');
    
        $sql = sprintf(
            'INSERT INTO "%s" (%s) VALUES (%s)',
            $tableName,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );
    
        try {
            $this->connection->executeStatement($sql, $values);
        } catch (\Doctrine\DBAL\Exception $e) {
            echo "SQL Error: " . $e->getMessage() . "\n";
            echo "Failed to insert data into $tableName\n";
            // Optionally, log the full SQL query and data for debugging
            echo "SQL Query: " . $sql . "\n";
            echo "Data: " . json_encode($data) . "\n";
        }
    }
}

// Instantiate and run the import
$importer = new ImportCsvToDatabase();
$importer->importCsv(TMP_REPORTS . 'recurring-expenses_report.csv', 'recurring_expenses');
$importer->importCsv(TMP_REPORTS . 'transactions_report.csv', 'all_transactions');
