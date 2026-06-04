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
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601231242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add backupcode version column';
    }

    /**
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $table = $schema->getTable('d3totp_backupcodes');

        $this->skipIf($table->hasColumn('CODEVERSION'), 'Column already exists.');

        $table->addColumn('CODEVERSION', (new IntegerType())->getName())
            ->setLength(1)
            ->setNotnull(true);
    }
}
