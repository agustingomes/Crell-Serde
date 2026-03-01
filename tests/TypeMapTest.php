<?php

declare(strict_types=1);

namespace Crell\Serde;

use Crell\Serde\Records\TypeMappedEnum;
use Crell\Serde\Records\TypeMappedMixedElements;
use Crell\Serde\Records\TypeMappedObject;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TypeMapTest extends TestCase
{
    #[Test, Group('typemap')]
    public function interfaceTypeMapForEnum(): void
    {
        $serde = new SerdeCommon();

        $element = new TypeMappedMixedElements([TypeMappedEnum::A]);
        $result  = $serde->serialize($element, 'json');
        $deserialized = $serde->deserialize($result, from: 'json', to: TypeMappedMixedElements::class);

        self::assertEquals('{"elements":[{"type":"enum","value":1}]}', $result);
        self::assertEquals($element, $deserialized);
    }

    #[Test, Group('typemap')]
    public function interfaceTypeMapForObject(): void
    {
        $serde = new SerdeCommon();

        $element = new TypeMappedMixedElements([new TypeMappedObject(2)]);
        $result  = $serde->serialize($element, 'json');
        $deserialized = $serde->deserialize($result, from: 'json', to: TypeMappedMixedElements::class);

        self::assertEquals('{"elements":[{"type":"object","id":2}]}', $result);
        self::assertEquals($element, $deserialized);
    }

    #[Test, Group('typemap')]
    public function interfaceTypeMapForAllMappedClasses(): void
    {
        $serde = new SerdeCommon();

        $element = new TypeMappedMixedElements([TypeMappedEnum::A, new TypeMappedObject(2)]);
        $result  = $serde->serialize($element, 'json');
        $deserialized = $serde->deserialize($result, from: 'json', to: TypeMappedMixedElements::class);

        self::assertEquals('{"elements":[{"type":"enum","value":1},{"type":"object","id":2}]}', $result);
        self::assertEquals($element, $deserialized);
    }
}
