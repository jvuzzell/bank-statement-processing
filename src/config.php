<?php

// Directories

define('TMP_DIR', getcwd() . '/tmp/'); 
define('LIB_DIR', getcwd() . '/Jvuzzell/BankStatementProcessing/library/');
define('TMP_DATA', TMP_DIR . 'data/');
define('TMP_REPORTS', TMP_DIR . 'reports/'); 
define('STATEMENT_DIR', getcwd() . '/../input/statements/');

if (!file_exists(TMP_DIR)) {
    mkdir(TMP_DIR);
}

if (!file_exists(TMP_DATA)) {
    mkdir(TMP_DATA);
}

if (!file_exists(TMP_REPORTS)) {
    mkdir(TMP_REPORTS);
} 

// Functions

require_once(LIB_DIR . 'functions/createStatementManifest.php');
require_once(LIB_DIR . 'functions/parseFilename.php');
