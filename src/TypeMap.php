<?php

declare(strict_types=1);

namespace Crell\Serde;

use Attribute;
use Crell\AttributeUtils\FromReflectionClass;
use Crell\AttributeUtils\ParseProperties;


#[Attribute(Attribute::TARGET_CLASS, Attribute::TARGET_PROPERTY)]
class TypeMap implements TypeMapper
{
    public function __construct(
        public readonly string $key,
        public readonly array $map,
    ) {}

    public function keyField(): string
    {
        return $this->key;
    }


    public function findClass(string $id): ?string
    {
        return $this->map[$id] ?? null;
    }

    public function findIdentifier(string $class): ?string
    {
        return array_search($class, $this->map, true) ?: null;
    }
}
