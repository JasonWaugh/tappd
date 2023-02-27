<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service;

use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\Operations\Contracts\CreateOrUpdateCustomerOperationContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\Responses\Contracts\CreateOrUpdateCustomerResponseContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\Responses\CreateOrUpdateCustomerResponse;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\MissingCustomerLocalIdException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\MissingCustomerRemoteIdException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Repositories\GuestCustomerMapRepository;

class GuestCustomersService extends AbstractCustomersService
{
    /**
     * {@inheritDoc}
     */
    public function createOrUpdateCustomer(CreateOrUpdateCustomerOperationContract $operation) : CreateOrUpdateCustomerResponseContract
    {
        $localId = $operation->getLocalId();

        if (! $localId) {
            throw new MissingCustomerLocalIdException('The customer has no local ID.');
        }

        $customer = $this->createOrUpdateCustomerInRemoteService($operation->getCustomer());

        if (! $customer->customerId) {
            throw MissingCustomerRemoteIdException::withDefaultMessage();
        }

        $this->saveCommerceIdMapping($localId, $customer->customerId);

        return new CreateOrUpdateCustomerResponse($customer->customerId);
    }

    /**
     * Gets an instance of the customer map repository.
     *
     * @return GuestCustomerMapRepository
     */
    protected function getCustomerMapRepository() : GuestCustomerMapRepository
    {
        return GuestCustomerMapRepository::fromStoreId($this->storeId);
    }
}
