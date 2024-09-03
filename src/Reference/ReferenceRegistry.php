<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use InvalidArgumentException;

use function array_keys;
use function sprintf;

final class ReferenceRegistry
{
    /** @var array<string,Reference> */
    private array $types = [];

    /** @param iterable<Reference> $types */
    public function __construct(iterable $types)
    {
        foreach ($types as $type) {
            $this->types[$type->name()] = $type;
        }
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_keys($this->types);
    }

    public function get(string $type): Reference
    {
        return $this->types[$type]
            ?? throw new InvalidArgumentException(sprintf('Reference type "%s" does not exist.', $type));
    }
}
