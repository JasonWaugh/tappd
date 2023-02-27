<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects;

class UpsertCustomerInput extends AbstractDataObject
{
    public string $storeId;
    public CustomerBase $customer;
    public string $customerSource = 'COMMERCE';

    /**
     * @param array{
     *     storeId: string,
     *     customer: CustomerBase,
     *     customerSource?: string,
     *  } $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
