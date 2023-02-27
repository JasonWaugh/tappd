<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects;

use GoDaddy\WordPress\MWC\Common\Traits\CanConvertToArrayTrait;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects\Contracts\DataObjectContract;
use ReflectionClass;

/**
 * An abstract data object.
 *
 * Subclasses must declare public properties for all the fields of the data object.
 *
 * Additionally, subclasses must override __construct() and use PHPStan array shapes to enforce required
 * properties and their types.
 */
abstract class AbstractDataObject implements DataObjectContract
{
    use CanConvertToArrayTrait;

    /**
     * Creates a new data object.
     *
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        foreach ((new ReflectionClass(static::class))->getProperties() as $property) {
            $propertyName = $property->getName();

            if (! array_key_exists($propertyName, $data)) {
                continue;
            }

            $this->{$propertyName} = $data[$propertyName];
        }
    }
}
