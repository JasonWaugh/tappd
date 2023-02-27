<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Sync\Strategies;

use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\CustomersService;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Sync\Strategies\Contracts\CustomerPushStrategyContract;
use GoDaddy\WordPress\MWC\Payments\Models\Customer;

class CustomerPushStrategyFactory
{
    use CanGetNewInstanceTrait;

    /**
     * Gets instance of push strategy class meant for the given customer.
     *
     * @param Customer $customer
     *
     * @return CustomerPushStrategyContract
     */
    public function getStrategyFor(Customer $customer) : CustomerPushStrategyContract
    {
        return new CustomerPushStrategy($customer, CustomersService::getNewInstance());
    }
}
