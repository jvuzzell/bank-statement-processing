# RunStatementsReports Command

The RunStatementsReports command processes bank statements and generates two CSV reports: a transaction report and a recurring expenses trend report. This document provides an overview of the purpose, configuration, and usage of the RunStatementsReports command.

## Purpose

The RunStatementsReports command exists to create a singular CSV file describing transactions from every file in the (project_directory)/input/statements/ folder. It also generates a report on recurring expenses based on the processed transactions.

## Prerequisites

Ensure you have the following:

- PHP 7.4 or higher
- Composer dependencies installed
- Directory structure with necessary input files

## Directory Structure

- **input/statements/**: Directory containing bank statement files to be processed.
- **tmp/**: Directory where the generated reports will be saved.
- **search_terms/**: Directory containing search terms used for trend detection.

## Configuration

Ensure your config.php file defines the following constants:

- STATEMENT_DIR: Path to the directory containing statement files.
- SEARCH_TERMS_DIR: Path to the directory containing search terms.
- TMP_DIR: Path to the temporary directory where reports will be saved.

## Usage

To run the RunStatementsReports command, execute the following from the command line:

```bash
php console.php RunStatementsReports
```

## Process Overview

The command performs the following steps:

1. **Compile Transaction Report:**
    - Reads all files in the STATEMENT_DIR.
    - Parses each file to extract transactions.
    - Aggregates all transactions into a single report.
    - Saves the report as transactions_report.csv in the TMP_DIR.
1. **Compile Trend Report:**
    - Analyzes the transactions to detect recurring expenses.
    - Generates a report on recurring expenses.
    - Saves the report as recurring-expenses_report.csv in the TMP_DIR.

## Detailed Breakdown

1. Compile Transaction Report

    - **Manifest Directory Content:**
    
        Reads the content of the STATEMENT_DIR and SEARCH_TERMS_DIR to get the list of files.

    - **Transaction Report File:**
    
        Creates and opens the transactions_report.csv file in the TMP_DIR.

    - **Report Header:**
    
        Uses the StatementProcessorParentClass to get the header for the transaction report and writes it to the CSV file.

    - **Processing Statements:**
    
        For each statement file:

        - Parses the filename to extract metadata.
            - Creates an appropriate statement processor using BankStatementProcessorFactory.
            - Aggregates transactions from each statement processor.
            - Writes transactions to the transactions_report.csv.

**2\. Compile Trend Report**

- **Trend Report File:**

    Creates and opens the recurring-expenses_report.csv file in the TMP_DIR.

- **Trend Detection:**

    Initializes TrendDetection with transactions and search terms.

- **Recurring Expense Report:**

    Generates a recurring expense report and writes it to the recurring-expenses_report.csv.

**Classes and Methods**

- **StatementProcessorParentClass**:
  - getReportHeader(): Returns the header for the transaction report.
- **BankStatementProcessorFactory**:
  - create($bankName, $filePath, $fileExt, $meta): Creates a statement processor based on the bank name and file type.
- **TrendDetection**:
  - \__construct($transactions, $searchTermsManifest, $searchTermsDir): Initializes with transactions and search terms.
  - reportRecurringExpenses(): Generates a report on recurring expenses.
  - getReportHeader(): Returns the header for the trend report.

**Output Files**

- **transactions_report.csv**: Contains aggregated transaction data.
- **recurring-expenses_report.csv**: Contains data on recurring expenses.

**Conclusion**

The RunStatementsReports command is a crucial part of processing bank statements and generating insightful reports. By following the steps outlined in this guide, you can successfully run the command and utilize the generated reports for further analysis.