<?php

// Directories

define('TMP_DIR', getcwd() . '/tmp/'); 
define('LIB_DIR', getcwd() . '/Jvuzzell/BankStatementProcessing/library/');
define('BANK_PROCESSING_DIR', getcwd() . '/Jvuzzell/BankStatementProcessing/');
define('TMP_DATA', TMP_DIR . 'data/');
define('TMP_REPORTS', TMP_DIR . 'reports/'); 
define('STATEMENT_DIR', getcwd() . '/../input/statements/');
define('SEARCH_TERMS_DIR', getcwd() . '/../input/data-analysis/search-terms/');
define('BANK_CMD_DIR', BANK_PROCESSING_DIR . 'console/commands/');
define('STATS_DIR', TMP_DIR . 'stats/');

if (!file_exists(TMP_DIR)) {
    mkdir(TMP_DIR, 0777, true);
}

if (!file_exists(TMP_DATA)) {
    mkdir(TMP_DATA, 0777, true);
}

if (!file_exists(TMP_REPORTS)) {
    mkdir(TMP_REPORTS, 0777, true);
} 

if (!file_exists(STATS_DIR)) {
    mkdir(STATS_DIR, 0777, true);
} 

// Functions

require_once(LIB_DIR . 'functions/manifestDirContent.php');
require_once(LIB_DIR . 'functions/parseFilename.php');
