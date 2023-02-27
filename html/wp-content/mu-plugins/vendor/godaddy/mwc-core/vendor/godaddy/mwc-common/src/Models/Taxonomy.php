<?php

namespace GoDaddy\WordPress\MWC\Common\Models;

use GoDaddy\WordPress\MWC\Common\Models\Contracts\TaxonomyContract;
use GoDaddy\WordPress\MWC\Common\Traits\CanBulkAssignPropertiesTrait;
use GoDaddy\WordPress\MWC\Common\Traits\CanConvertToArrayTrait;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Common\Traits\HasLabelTrait;

/**
 * Object representation of a taxonomy.
 *
 *
 * @method static static getNewInstance(array $properties = [])
 */
class Taxonomy implements TaxonomyContract
{
    use CanBulkAssignPropertiesTrait;
    use CanConvertToArrayTrait;
    use CanGetNewInstanceTrait;
    use HasLabelTrait;

    /**
     * Taxonomy constructor.
     *
     * @param array<string, mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        $this->setProperties($properties);
    }
}
