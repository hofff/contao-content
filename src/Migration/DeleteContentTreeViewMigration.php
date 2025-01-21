<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

final class DeleteContentTreeViewMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $result = $this->connection->executeQuery('SHOW TABLES LIKE ?', ['hofff_content_tree']);
        $data   = $result->fetchAllAssociative();

        return $data !== [];
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement('DROP VIEW IF EXISTS hofff_content_tree');

        return $this->createResult(true, 'View hofff_content_tree removed successfully.');
    }
}
