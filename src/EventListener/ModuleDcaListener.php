<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

final readonly class ModuleDcaListener
{
    public function __construct(private Connection $connection)
    {
    }

    #[AsCallback('tl_module', 'config.oncopy')]
    public function onCopy(string|int $insertId, DataContainer $dataContainer): void
    {
        $result = $this->connection->executeQuery(
            'SELECT type FROM tl_module WHERE id = :id LIMIT 0,1',
            ['id' => $dataContainer->id],
        );

        if ($result->fetchOne() !== 'hofff_content_references') {
            return;
        }

        $result = $this->connection->executeQuery(
            'SELECT * FROM tl_hofff_content WHERE pid=:pid AND ptable = :ptable ORDER BY sorting',
            ['pid' => $dataContainer->id, 'ptable' => 'tl_module'],
        );

        while ($row = $result->fetchAssociative()) {
            unset($row['id']);
            $row['pid'] = $insertId;

            $this->connection->insert('tl_hofff_content', $row);
        }
    }
}
