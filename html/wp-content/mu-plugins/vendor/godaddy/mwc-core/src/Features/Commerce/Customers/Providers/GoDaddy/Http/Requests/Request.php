<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\GoDaddy\Http\Requests;

use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Providers\Http\Requests\AbstractRequest;

/**
 * Commerce Customers Request class.
 */
class Request extends AbstractRequest
{
    /**
     * {@inheritDoc}
     */
    protected function getBaseUrl() : string
    {
        return TypeHelper::string(Configuration::get('commerce.customers.api.url'), '');
    }
}
