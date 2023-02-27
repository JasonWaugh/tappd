<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Sync\Strategies;

use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\Contracts\CustomersServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Sync\Strategies\Contracts\CustomerPushStrategyContract;
use GoDaddy\WordPress\MWC\Payments\Models\Customer;

class CustomerPushStrategy implements CustomerPushStrategyContract
{
    /** @var Customer */
    protected Customer $customer;

    /** @var CustomersServiceContract */
    protected CustomersServiceContract $customerService;

    /**
     * Constructor.
     *
     * @param Customer $customer
     * @param CustomersServiceContract $customerService
     */
    public function __construct(Customer $customer, CustomersServiceContract $customerService)
    {
        $this->customer = $customer;
        $this->customerService = $customerService;
    }

    /**
     * {@inheritDoc}
     */
    public function sync() : void
    {
        $this->customerService->createOrUpdateCustomer($this->customer);
    }
}
