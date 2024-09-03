<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Reference\CreatesRenderer;
use Hofff\Contao\Content\Reference\CreatesSelect;
use Hofff\Contao\Content\Reference\ReferenceRegistry;

use function array_map;
use function array_merge;
use function call_user_func_array;
use function implode;
use function ksort;

use const SORT_NUMERIC;

/** @SuppressWarnings(PHPMD.LongVariable) */
final readonly class RendererFactory
{
    public function __construct(
        private Connection $connection,
        private ReferenceRegistry $referenceRegistry,
    ) {
    }

    /**
     * @param list<array<string,mixed>> $configs
     *
     * @return Renderer[]
     */
    public function createAll(array $configs, string $column): array
    {
        $allSelects = [];

        foreach ($configs as $i => $config) {
            $type      = $config['type'];
            $reference = $this->referenceRegistry->get($type);

            if (! $reference instanceof CreatesSelect) {
                continue;
            }

            $select                      = $reference->createSelect($config, $i, $column);
            $allSelects[$select->type][] = $select;
        }

        $renderers = [];
        foreach ($allSelects as $type => $selects) {
            $reference = $this->referenceRegistry->get($type);
            if (! $reference instanceof CreatesRenderer) {
                continue;
            }

            $queries = array_map(static fn (Select $select) => $select->sql, $selects);
            $params  = array_merge(...array_map(static fn (Select $select) => $select->params, $selects));
            $sql     = '(' . implode(') UNION ALL (', $queries) . ')';
            $result  = $this->connection->executeQuery($sql, $params);

            while ($row = $result->fetchAssociative()) {
                $i = $row['hofff_content_index'];

                $renderer = $reference->createRenderer($row, $configs[$i]);
                $renderer->setColumn($column);

                if (! $renderer->isValid()) {
                    continue;
                }

                $renderers[$i][] = $renderer;
            }
        }

        if (! $renderers) {
            return [];
        }

        ksort($renderers, SORT_NUMERIC);

        return call_user_func_array('array_merge', $renderers);
    }
}
