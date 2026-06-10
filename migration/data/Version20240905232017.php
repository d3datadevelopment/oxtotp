<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\Totp\Migrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\Migrations\AbstractMigration;

/**
 * @codeCoverageIgnore
 */
final class Version20240905232017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend Database by missing OTP tables and missing columns.';
    }

    /**
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $this->addTotpTable($schema);
        $this->addTotpBackupCodesTable($schema);
    }

    public function down(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $this->removeTotpTable($schema);
        $this->removeTotpBackupCodesTable($schema);
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws SchemaException
     */
    public function addTotpTable(Schema $schema): void
    {
        $table = !$schema->hasTable('d3totp') ?
            $schema->createTable('d3totp')->setComment('totp setting') :
            $schema->getTable('d3totp');

        // OXID
        if (!$table->hasColumn('OXID')) {
            $table->addColumn('OXID', (new StringType())->getName())
                ->setLength(32)
                ->setFixed(true)
                ->setNotnull(true);
        }

        // OXUSERID
        if (!$table->hasColumn('OXUSERID')) {
            $table->addColumn('OXUSERID', (new StringType())->getName())
                ->setLength(32)
                ->setFixed(true)
                ->setNotnull(true);
        }

        // useTotp
        if (!$table->hasColumn('USETOTP')) {
            $table->addColumn('USETOTP', (new BooleanType())->getName())
                ->setLength(1)
                ->setDefault(0)
                ->setNotnull(true);
        }

        // Seed
        if (!$table->hasColumn('SEED')) {
            $table->addColumn('SEED', (new StringType())->getName())
                ->setLength(256)
                ->setNotnull(true);
        }

        // oxtimestamp
        if (!$table->hasColumn('OXTIMESTAMP')) {
            $table->addColumn('OXTIMESTAMP', (new DateTimeType())->getName())
                ->setNotnull(true)
                ->setDefault('CURRENT_TIMESTAMP');
        }

        $table->hasPrimaryKey() ?: $table->setPrimaryKey(['oxid']);

        if ($table->hasIndex('OXUSERID') === false) {
            $table->addUniqueIndex(['OXUSERID'], 'OXUSERID');
        }
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function removeTotpTable(Schema $schema): void
    {
        if ($schema->hasTable('d3totp')) {
            $schema->dropTable('d3totp');
        }
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws SchemaException
     */
    public function addTotpBackupCodesTable(Schema $schema): void
    {
        $table = !$schema->hasTable('d3totp_backupcodes') ?
            $schema->createTable('d3totp_backupcodes')->setComment('totp backup codes') :
            $schema->getTable('d3totp_backupcodes');

        // OXID
        if (!$table->hasColumn('OXID')) {
            $table->addColumn('OXID', (new StringType())->getName())
                ->setLength(32)
                ->setFixed(true)
                ->setNotnull(true);
        }

        // OXUSERID
        if (!$table->hasColumn('OXUSERID')) {
            $table->addColumn('OXUSERID', (new StringType())->getName())
                ->setLength(32)
                ->setFixed(true)
                ->setNotnull(true)
                ->setComment('User ID');
        }

        // useTotp
        if (!$table->hasColumn('BACKUPCODE')) {
            $table->addColumn('BACKUPCODE', (new StringType())->getName())
                ->setFixed(false)
                ->setLength(64)
                ->setNotnull(true);
        }

        // oxtimestamp
        if (!$table->hasColumn('OXTIMESTAMP')) {
            $table->addColumn('OXTIMESTAMP', (new DateTimeType())->getName())
                ->setNotnull(true)
                ->setDefault('CURRENT_TIMESTAMP');
        }

        $table->hasPrimaryKey() ?: $table->setPrimaryKey(['oxid']);

        if ($table->hasIndex('OXUSERID') === false) {
            $table->addIndex(['OXUSERID'], 'OXUSERID');
        }

        if ($table->hasIndex('BACKUPCODE') === false) {
            $table->addIndex(['BACKUPCODE'], 'BACKUPCODE');
        }
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function removeTotpBackupCodesTable(Schema $schema): void
    {
        if ($schema->hasTable('d3totp_backupcodes')) {
            $schema->dropTable('d3totp_backupcodes');
        }
    }
}
