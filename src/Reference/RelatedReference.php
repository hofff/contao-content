<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Doctrine\DBAL\Connection;

use function array_key_exists;
use function sprintf;

abstract class RelatedReference implements Reference
{
    /** @var array<string, array<string, mixed>|null> */
    private array $references = [];

    public function __construct(protected readonly Connection $connection)
    {
    }

    /** {@inheritDoc} */
    public function backendLabel(array $row): string
    {
        $reference = $this->loadReference($row);

        if ($reference === null) {
            return sprintf('ID %s <span class="tl_gray">%s</span>', $row['id'], $row[$row['type']]);
        }

        $extra = $this->backendLabelExtra($row, $reference);
        if ($extra !== null) {
            return sprintf(
                '%s <span class="hofff-content-label">[%s] (ID %s)</span>',
                $reference[$this->labelColumn()],
                $extra,
                $reference['id'],
            );
        }

        return sprintf('%s <span class="tl_gray">(ID %s)</span>', $reference[$this->labelColumn()], $reference['id']);
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $reference
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function backendLabelExtra(array $row, array $reference): string|null
    {
        return null;
    }

    abstract protected function labelColumn(): string;

    abstract protected function referenceTable(): string;

    /** @param array<string, mixed> $row */
    protected function loadReference(array $row): array|null
    {
        if (array_key_exists($row['id'], $this->references)) {
            return $this->references[$row['id']];
        }

        $query = $this->connection->createQueryBuilder();
        $query->select('*');
        $query->from($this->referenceTable());
        $query->where('id=:id');
        $query->setParameter('id', $row[$row['type']]);

        $result = $query->executeQuery();

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->references[$row['id']] = $result->rowCount() === 0 ? null : (array) $result->fetchAssociative();

        return $this->references[$row['id']];
    }
}
