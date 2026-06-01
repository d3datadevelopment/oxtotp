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

use D3\Totp\Application\Model\d3backupcode;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601232203 extends AbstractMigration
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

        $this->addSql('UPDATE d3totp_backupcodes SET codeversion = ? WHERE 1;', [d3backupcode::VERSION_MD5]);
    }
}
