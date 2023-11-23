<?php

declare(strict_types=1);

namespace Crell\Serde\Records;

final class FlattenSingleProperty
{
    public function __construct(
        public readonly Identity $id,
        public readonly string $name,
    ) {
    }
}
