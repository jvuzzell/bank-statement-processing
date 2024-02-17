<?php 
namespace Jvuzzell\BankStatementProcessing\migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version202402021111112_CreateTableAllTransactions extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('all_transactions');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('bank', 'string', ['length' => 255]);
        $table->addColumn('account_number', 'string', ['length' => 4]);
        $table->addColumn('account_type', 'string', ['length' => 255]);
        $table->addColumn('statement_period', 'string', ['length' => 255]);
        $table->addColumn('account_owner', 'string', ['length' => 255]);
        $table->addColumn('transaction_date', 'date');
        $table->addColumn('transaction_type', 'string', ['length' => 255]);
        $table->addColumn('transaction_desc', 'text');
        $table->addColumn('transaction_amount', 'decimal', ['scale' => 2]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('all_transactions');
    }
}
