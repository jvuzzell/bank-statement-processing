<?php 
namespace Jvuzzell\BankStatementProcessing\library\migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version202402021111111 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Creates RecurringExpenses and Transactions tables.';
    }

    public function up(Schema $schema) : void
    {
        // Create RecurringExpenses Table
        $table = $schema->createTable('RecurringExpenses');
        $table->addColumn('ID', 'integer', ['autoincrement' => true]);
        $table->addColumn('Category', 'string');
        $table->addColumn('Description', 'string');
        $table->addColumn('Amount', 'float');
        $table->addColumn('Date', 'string');
        $table->setPrimaryKey(['ID']);

        // Create Transactions Table
        $table = $schema->createTable('Transactions');
        $table->addColumn('ID', 'integer', ['autoincrement' => true]);
        $table->addColumn('Date', 'string');
        $table->addColumn('Description', 'string');
        $table->addColumn('Amount', 'float');
        $table->addColumn('Type', 'string');
        $table->setPrimaryKey(['ID']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('RecurringExpenses');
        $schema->dropTable('Transactions');
    }
}
