<?php 

namespace Jvuzzell\BankStatementProcessing\library\services\commands;

use Doctrine\DBAL\DriverManager;

class ImportCsvToDatabase {
    private $connection;

    public function __construct() 
    {
        $this->initializeDatabaseConnection();
    }

    private function initializeDatabaseConnection() : void
    {
        // Path to your SQLite database
        $pathToDatabase = TMP_DATA . 'analytics-database.db';

        $connectionParams = [
            'driver' => 'pdo_sqlite', 
            'path'   => $pathToDatabase
        ];
        $this->connection = DriverManager::getConnection($connectionParams);
    }

    public function importCsv(string $filePath, string $tableName) : void
    {
        $handle = $this->getCsvHandle($filePath);
        $header = fgetcsv($handle); // Assuming first row is header

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $this->insertRow($tableName, $data);
        }

        fclose($handle);
        echo "Imported $filePath into $tableName\n";
    }

    private function getCsvHandle(string $filePath)
    {
        if (!file_exists($filePath)) {
            echo "File not found: $filePath\n";
            return false;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            echo "Failed to open file: $filePath\n";
            return false;
        }

        return $handle;
    }

    private function insertRow(string $tableName, array $data) : void
    {
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

    public function importTagsFromCsv(string $filePath, string $tableName) : void
    {
        $handle = $this->getCsvHandle($filePath);

        while (($row = fgetcsv($handle)) !== false) {
            $tag = $row[0];
            $taxonomy = array_slice($row, 1); // Convert the taxonomy data to JSON

            foreach($taxonomy as $category) {
                $values = [$tag,$category];
                $placeholders = array_fill(0, count(['tag','taxonomy']), '?');

                $sql = sprintf(
                    'INSERT INTO "%s" (%s) VALUES (%s)', 
                    $tableName, 
                    implode(', ', ['tag','taxonomy']), 
                    implode(', ', $placeholders)
                ); 

                try {
                    $this->connection->executeStatement($sql, $values);
                } catch (\Doctrine\DBAL\Exception $e) {
                    echo "SQL Error: " . $e->getMessage() . "\n";
                    echo "Failed to insert data into $tableName\n";
                    // Optionally, log the full SQL query and data for debugging
                    echo "SQL Query: " . $sql . "\n";
                    echo "Data: " . json_encode([$tag, $taxonomy]) . "\n";
                }
            }

        }

        fclose($handle);
    }
}
