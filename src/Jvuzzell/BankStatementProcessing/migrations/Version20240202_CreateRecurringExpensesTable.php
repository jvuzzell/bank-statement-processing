<?php 
namespace Jvuzzell\BankStatementProcessing\migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version202402021111113_CreateRecurringExpensesTable extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('recurring_expenses');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('recurring_transaction_type', 'string', ['length' => 255]);
        $table->addColumn('transaction_report_id', 'integer');
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
        $schema->dropTable('recurring_expenses');
    }
}
