<?php

declare(strict_types=1);

namespace Crell\Serde\PropertyHandler;

use Crell\AttributeUtils\Analyzer;
use Crell\AttributeUtils\ClassAnalyzer;
use Crell\AttributeUtils\MemoryCacheAnalyzer;
use Crell\Serde\ClassDef;
use Crell\Serde\Field;
use Crell\Serde\Formatter\Deformatter;
use Crell\Serde\Formatter\Formatter;
use Crell\Serde\SerdeError;
use Crell\Serde\TypeCategory;
use Crell\Serde\TypeMapper;
use function Crell\fp\afilter;
use function Crell\fp\pipe;
use function Crell\fp\reduce;

class ObjectPropertyReader implements PropertyWriter, PropertyReader
{
    public function __construct(
        protected readonly ClassAnalyzer $analyzer = new MemoryCacheAnalyzer(new Analyzer()),
    ) {}

    /**
     * @param Formatter $formatter
     * @param callable $recursor
     * @param Field $field
     * @param object $value
     * @param mixed $runningValue
     * @return mixed
     */
    public function readValue(Formatter $formatter, callable $recursor, Field $field, mixed $value, mixed $runningValue): mixed
    {
        /** @var ClassDef $objectMetadata */
        $objectMetadata = $this->analyzer->analyze($value, ClassDef::class);

        // This lets us read private values without messing with the Reflection API.
        $propReader = (fn (string $prop): mixed => $this->$prop ?? null)->bindTo($value, $value);

        /** @var \Crell\Serde\Dict $dict */
        $dict = pipe(
            $objectMetadata->properties,
            reduce(new \Crell\Serde\Dict, fn(\Crell\Serde\Dict $dict, Field $f) => $this->flattenValue($dict, $f, $propReader)),
        );

        if ($map = $this->typeMap($field)) {
            $f = Field::create(serializedName: $map->keyField(), phpType: 'string');
            $dict->items[] = new \Crell\Serde\CollectionItem(field: $f, value: $map->findIdentifier($value::class));
        }

        return $formatter->serializeDictionary($runningValue, $field, $dict, $recursor);
    }

    protected function flattenValue(\Crell\Serde\Dict $dict, Field $field, callable $propReader): \Crell\Serde\Dict
    {
        $value = $propReader($field->phpName);
        if ($value === null) {
            return $dict;
        }

        // @todo Figure out if we care about flattening/collecting objects.
        if ($field->flatten && $field->phpType === 'array') {
            foreach ($value as $k => $v) {
                $f = Field::create(serializedName: $k, phpType: \get_debug_type($v));
                $dict->items[] = new \Crell\Serde\CollectionItem(field: $f, value: $v);
            }
        } else {
            $dict->items[] = new \Crell\Serde\CollectionItem(field: $field, value: $value);
        }

        return $dict;
    }

    protected function typeMap(Field $field): ?TypeMapper
    {
        return $field->typeMap;
    }

    public function canRead(Field $field, mixed $value, string $format): bool
    {
        return $field->typeCategory === TypeCategory::Object;
    }

    public function writeValue(Deformatter $formatter, callable $recursor, Field $field, mixed $source): mixed
    {
        $dict = $formatter->deserializeDictionary($source, $field, $recursor);

        if ($dict === SerdeError::Missing) {
            return null;
        }

        $class = $field->phpType;
        if ($map = $this->typeMap($field)) {
            $keyField = $map->keyField();
            $class = $map->findClass($dict[$keyField]);
            unset($dict[$keyField]);
        }

        return $recursor($dict, $class);
    }

    public function canWrite(Field $field, string $format): bool
    {
        return $field->typeCategory === TypeCategory::Object;
    }
}
