<?php

declare(strict_types=1);

namespace Crell\Serde;

use Crell\Serde\Records\FlattenSingleProperty;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FlattenSinglePropertyTest extends TestCase
{
    /**
     * @link https://github.com/Crell/Serde/compare/master...agustingomes:Crell-Serde:proposal/single-property-flattening
     */
    #[Test]
    public function flattenSingleProperties(): void
    {
        $jsonInput = <<<'JSON'
        {
          "id": "identity",
          "name": "Testing Purposes"
        }
        JSON;

        $deserializer = new SerdeCommon();
        $result = $deserializer->deserialize(
            serialized: $jsonInput,
            from: 'json',
            to: FlattenSingleProperty::class,
        );

        self::assertInstanceOf(FlattenSingleProperty::class, $result);
        self::assertSame('identity', $result->id);
        self::assertSame('Testing Purposes', $result->name);
    }
}
