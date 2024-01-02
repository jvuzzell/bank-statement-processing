<?php 

namespace Jvuzzell\BankStatementProcessing\library\interfaces;

interface StatementProcessorInterface {
    public function extractTransactions();
    public function getTransactions() : array;
}