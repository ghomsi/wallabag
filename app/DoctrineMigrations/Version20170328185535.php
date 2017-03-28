<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Migrations\SkipMigrationException;

/**
 * Creates the Change table.
 */
class Version20170328185535 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix').$tableName;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        try {
            $changeTable = $schema->getTable($this->getTable('change'));
        } catch (SchemaException $e) {
            // The Change table doesn't exist, we need to create it
            if (10 == $e->getCode()) {
                $changeTable = $schema->createTable($this->getTable('change'));
                $changeTable->addColumn(
                    'id',
                    'integer',
                    ['autoincrement' => true]
                );
                $changeTable->addColumn(
                    'type',
                    'integer',
                    ['notnull' => false]
                );
                $changeTable->addColumn(
                    'entry_id',
                    'integer',
                    ['notnull' => false]
                );

                $changeTable->setPrimaryKey(['id']);

                $changeTable->addForeignKeyConstraint(
                    $this->getTable('entry'),
                    ['entry_id'],
                    ['id'],
                    ['onDelete' => 'CASCADE'],
                    'IDX_change_entry'
                );

                return true;
            }
        }

        throw new SkipMigrationException('It seems that you already played this migration.');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        try {
            $changeTable = $schema->getTable($this->getTable('change'));
            $schema->dropTable($this->getTable('change'));
        } catch (SchemaException $e) {
            throw new SkipMigrationException('It seems that you already played this migration.');
        }
    }
}