<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

use function dd;
use function explode;
use function time;

final class ConfigurationMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        return $this->shouldRunFor('tl_module') || $this->shouldRunFor('tl_content');
    }

    public function run(): MigrationResult
    {
        $this->migrate('tl_module');
        $this->migrate('tl_content');

        return $this->createResult(true);
    }

    private function shouldRunFor(string $table): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (! $schemaManager->tablesExist([$table, 'tl_hofff_content'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns($table);
        if (! isset($columns['hofff_content_references'])) {
            return false;
        }

        $result = $this->connection->executeQuery(
            'SELECT count(*) as count FROM ' .  $table . ' WHERE type=:type AND hofff_content_references IS NOT NULL',
            ['type' => 'hofff_content_references'],
        );

        return $result->fetchOne() > 0;
    }

    private function migrate(string $table): void
    {
        if (! $this->shouldRunFor($table)) {
            return;
        }

        $result = $this->connection->executeQuery(
            'SELECT * FROM ' . $table . ' WHERE type=:type AND hofff_content_references IS NOT NULL',
            ['type' => 'hofff_content_references']
        );

        foreach ($result->fetchAllAssociative() as $row) {
            $references = StringUtil::deserialize($row['hofff_content_references'], true);
            $sorting    = 0;
            foreach ($references as $reference) {
                $sorting = $this->createReference($table, $row['id'], $reference, $sorting);
            }

            $this->connection->update($table, ['hofff_content_references' => null], ['id' => $row['id']]);
        }
    }

    private function createReference(string $table, $parentId, array $reference, int $sorting): int
    {
        [$type, $referenceId] = explode('.', $reference['_key']);

        $this->connection->insert(
            'tl_hofff_content',
            [
                'pid'                   => $parentId,
                'ptable'                => $table,
                'sorting'               => $sorting,
                'tstamp'                => time(),
                'type'                  => $type,
                'exclude_from_search'   => (int) $reference['exclude_from_search'],
                'render_container'      => (int) $reference['render_container'],
                'target_section_filter' => (int) $reference['target_section_filter'],
                'translate'             => (int) $reference['translate'],
                'css_classes'           => $reference['css_classes'] ?? '',
                'css_id'                => $reference['css_id'] ?? '',
                'source_sections'       => $reference['source_sections'],
                $type                   => $referenceId,
            ],
        );

        return $sorting + 128;
    }
}
