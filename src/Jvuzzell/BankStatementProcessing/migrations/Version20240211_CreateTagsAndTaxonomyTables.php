<?php 
namespace Jvuzzell\BankStatementProcessing\migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version202402021111115_CreateRecurringExpensesTable extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Creates the tags_taxonomy table for managing tags and their taxonomy.';
    }

    public function up(Schema $schema) : void
    {
        // Define the table structure
        $table = $schema->createTable('tags_taxonomy');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('tag', 'string', ['length' => 255]);
        $table->addColumn('taxonomy', 'text');
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        // Remove the table
        $schema->dropTable('tags_taxonomy');
    }
}
