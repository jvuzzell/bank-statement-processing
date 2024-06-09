# GenerateStats Command

The GenerateStats command is responsible for generating and caching statistical data from the database. This document provides an overview of the purpose, configuration, and usage of the GenerateStats command.

## Purpose

The GenerateStats command connects to the database, generates various statistics, and caches the results for efficient retrieval. This process helps in quickly accessing precomputed statistics without querying the database repeatedly.

## Prerequisites

Ensure you have the following:

- PHP 7.4 or higher
- Composer dependencies installed
- SQLite (or another database supported by your application)
- Doctrine DBAL installed and configured

## Directory Structure

- **tmp/data/**: Directory containing the SQLite database file.

## Configuration

Ensure your config.php file and database connection settings are properly configured.

## Usage

To run the GenerateStats command, execute the following from the command line:

```bash
php console.php GenerateStats
```

## Process Overview

The command performs the following steps:

1. **Set Up Database Connection:**
    - Defines the connection parameters for the SQLite database.
    - Establishes a connection using Doctrine DBAL.
2. **Generate and Cache Statistics:**
    - Instantiates the CacheStats service.
    - Calls the method to generate and cache the statistics.

**Detailed Breakdown**

**1\. Set Up Database Connection**

- **Namespace Declaration:**

    Declares the necessary namespaces for the script.

    ```php
    use Doctrine\\DBAL\\DriverManager;
    use Jvuzzell\\BankStatementProcessing\\library\\controllers\\CacheStats;
    ```

- **Database Path:**

    Defines the path to the SQLite database.

    ```php
    $pathToDatabase = TMP_DATA . 'analytics-database.db';
    ```

- **Connection Parameters:**

    Sets up the connection parameters for Doctrine DBAL.

    ```php
    $connectionParams = [
        'driver' => 'pdo_sqlite',
        'path' => $pathToDatabase
    ];
    ```
- **Establish Connection:**

    Establishes a connection to the database.

    ```php
    $dbConnection = DriverManager::getConnection($connectionParams);
    ```

**2\. Generate and Cache Statistics**

- **Instantiate CacheStats:**

    Creates a new instance of the CacheStats service.

    ```php
    $cacheStats = new CacheStats($dbConnection);
    ```
- **Generate and Cache Statistics:**

    Calls the method to generate and cache the statistics.

    ```php
    $cacheStats->routeToStatsService();
    ```
## Classes and Methods

- **CacheStats**:
  - \__construct($dbConnection): Initializes the service with a database connection.
  - routeToStatsService(): Generates and caches statistical data.

## Conclusion

The GenerateStats command is essential for precomputing and caching statistical data from the database. By following the steps outlined in this guide, you can successfully run the command and ensure that your statistics are generated and cached efficiently.