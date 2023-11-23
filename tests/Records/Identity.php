<?php

declare(strict_types=1);

namespace Crell\Serde\Records;

/**
 * Identity Object, which can have its custom behavior within a domain.
 */
final class Identity
{
    public function __construct(
        #[\Crell\Serde\Attributes\Field(flatten: true)]
        public readonly string $value,
    ) {
    }
}
