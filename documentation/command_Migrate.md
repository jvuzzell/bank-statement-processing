# Migrate Command

The Migrate command is responsible for executing all outstanding database migrations using Doctrine Migrations. This document provides an overview of the purpose, configuration, and usage of the Migrate command.

## Purpose

The Migrate command ensures that your database schema is up to date by applying all pending migrations. This is crucial for maintaining the integrity and structure of the database as the application evolves.

## Prerequisites

Ensure you have the following:

- PHP 7.4 or higher
- Composer dependencies installed
- Doctrine Migrations configured
- Symfony Console component installed

## Directory Structure

- **library/migrations/**: Directory containing migration files.
- **vendor/**: Directory containing Composer-installed dependencies.

## Configuration

Ensure your config.php file and Doctrine Migrations configuration are set up correctly to point to your database and migration directory.

## Usage

To run the Migrate command, execute the following from the command line:

```bash
php console.php Migrate
```

## Process Overview

The command performs the following steps:

1. **Initialize Symfony Console Application:**
    - Sets up a Symfony Console Application with Doctrine Migrations commands.
2. **Add Migrate Command:**
    - Adds the MigrateCommand to the application to handle the migration process.
3. **Execute Migrations:**
    - Runs the migrations:migrate command to apply all outstanding migrations.

## Detailed Breakdown

**1\. Initialize Symfony Console Application**

- **Namespace Declaration:**

    Declares the namespace for the script.

    ```php
    namespace Jvuzzell\\BankStatementProcessing\\library\\migrations;
    ```

- **Imports:**

    Imports necessary classes from Doctrine Migrations and Symfony Console components.

    ```php
    use Doctrine\\Migrations\\Tools\\Console\\Command\\MigrateCommand;
    use Symfony\\Component\\Console\\Input\\ArrayInput;
    use Symfony\\Component\\Console\\Output\\ConsoleOutput;
    use Symfony\\Component\\Console\\Application;
    ```

- **Application Initialization:**

    Initializes a new Symfony Console Application for Doctrine Migrations.

    ```php
    $application = new Application('Doctrine Migrations');
    $application->setAutoExit(false);
    ```

**2\. Add Migrate Command**

- **Add Commands:**

    Adds the MigrateCommand to the application.

    ```php
    $application->addCommands([
        new MigrateCommand()
    ]);
    ```

**3\. Execute Migrations**

- **Prepare Input:**

    Prepares the input for the migrations:migrate command with the --no-interaction option to run non-interactively.

    ```php
    $input = new ArrayInput([
        'command' => 'migrations:migrate',
        '--no-interaction' => true
    ]);
    ```

- **Run Command:**

    Executes the migration command and outputs the result to the console.

    ```php
    $application->run($input, new ConsoleOutput());
    ```

- **Output Message:**

    Prints a message indicating that the migrations have been applied.

    ```php
    echo "Database migrations applied.\\n";
    ```

## Conclusion

The Migrate command is essential for keeping your database schema in sync with your application's state. By following the steps outlined in this guide, you can successfully run the command and ensure that all necessary migrations are applied to your database.