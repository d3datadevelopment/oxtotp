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
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\Migrations\AbstractMigration;

/**
 * @codeCoverageIgnore
 */
final class Version20260603082417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add last accepted time slice column';
    }

    public function preUp(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        parent::preUp($schema);

        $this->abortIf(!$schema->hasTable('d3totp'), 'Totp table does not exist.');

        $table = $schema->getTable('d3totp');

        $this->skipIf($table->hasColumn('lastacceptedtimeslice'), 'Column already exists.');
    }

    /**
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $table = $schema->getTable('d3totp');

        $table->addColumn('lastacceptedtimeslice', (new BigIntType())->getName())
            ->setNotnull(false)
            ->setDefault(null);
    }
}
