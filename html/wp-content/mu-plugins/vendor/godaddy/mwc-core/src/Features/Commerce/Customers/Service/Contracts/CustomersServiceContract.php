<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\Contracts;

use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\Contracts\CommerceExceptionContract;
use GoDaddy\WordPress\MWC\Payments\Models\Customer;

interface CustomersServiceContract
{
    /**
     * Creates or updates the customer.
     *
     * Intended to use a CustomersProviderContract instance to create or update a customer entity in a remote Customers
     * service
     *
     * @param Customer $customer
     * @return Customer
     * @throws CommerceExceptionContract
     */
    public function createOrUpdateCustomer(Customer $customer) : Customer;
}
