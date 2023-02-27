<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service;

use GoDaddy\WordPress\MWC\Common\Exceptions\WordPressDatabaseException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Commerce;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\Contracts\CustomersProviderContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects\CustomerBase;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects\UpsertCustomerInput;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataSources\Adapters\CustomerBaseAdapter;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\GoDaddy\CustomersProvider;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\Contracts\CustomersServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\CommerceException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\Contracts\CommerceExceptionContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\MissingCustomerLocalIdException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\MissingCustomerRemoteIdException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Repositories\CustomerMapRepository;
use GoDaddy\WordPress\MWC\Payments\Models\Customer;

class CustomersService implements CustomersServiceContract
{
    /** @var string */
    protected string $storeId;

    /** @var CustomersProviderContract */
    protected CustomersProviderContract $customersProvider;

    /**
     * Constructor.
     *
     * @param string $storeId
     * @param CustomersProviderContract $customersProvider
     */
    final public function __construct(string $storeId, CustomersProviderContract $customersProvider)
    {
        $this->storeId = $storeId;
        $this->customersProvider = $customersProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function createOrUpdateCustomer(Customer $customer) : Customer
    {
        if (! $customer->getId()) {
            throw new MissingCustomerLocalIdException('The customer has no local ID.');
        }

        $customerData = $this->customersProvider->customers()->createOrUpdate(
            $this->getCreateOrUpdateCustomerInput($customer)
        );

        if (! $customerData->customerId) {
            throw MissingCustomerRemoteIdException::withDefaultMessage();
        }

        $this->saveCommerceIdMapping($customer->getId(), $customerData->customerId);

        return $customer;
    }

    /**
     * Creates an instance of {@see UpsertCustomerInput} using the information from the given customer.
     *
     * @param Customer $customer
     *
     * @return UpsertCustomerInput
     * @throws CommerceExceptionContract
     */
    protected function getCreateOrUpdateCustomerInput(Customer $customer) : UpsertCustomerInput
    {
        $customerData = $this->getCustomerData($customer);

        if (! $customerData) {
            throw new CommerceException('Unable to prepare customer input data.');
        }

        return new UpsertCustomerInput([
            'storeId'  => $this->storeId,
            'customer' => $customerData,
        ]);
    }

    /**
     * Attempts to create a customer data object for the given customer.
     *
     * @param Customer $customer
     *
     * @return ?CustomerBase
     */
    protected function getCustomerData(Customer $customer) : ?CustomerBase
    {
        $adapter = CustomerBaseAdapter::getNewInstance();

        if ($remoteId = $this->getCustomerRemoteId($customer)) {
            $adapter->setRemoteId($remoteId);
        }

        return $adapter->convertToSource($customer);
    }

    /**
     * Attempts to get the remote ID for the given customer.
     *
     * @param Customer $customer
     *
     * @return string|null
     */
    protected function getCustomerRemoteId(Customer $customer) : ?string
    {
        $customerId = $customer->getId();

        if (! $customerId) {
            return null;
        }

        return $this->getCustomerMapRepository()->getRemoteId($customerId);
    }

    /**
     * Creates an association between the given local customer ID and the given remote UUID.
     *
     * @param int $localId
     * @param string $remoteId
     *
     * @return void
     * @throws CommerceExceptionContract
     */
    protected function saveCommerceIdMapping(int $localId, string $remoteId) : void
    {
        try {
            $this->getCustomerMapRepository()->add($localId, $remoteId);
        } catch (WordPressDatabaseException $exception) {
            throw CommerceException::getNewInstance("A database error occurred trying to save the customer UUID: {$exception->getMessage()}", $exception);
        }
    }

    /**
     * Gets an instance of the customer map repository.
     *
     * @return CustomerMapRepository
     */
    protected function getCustomerMapRepository() : CustomerMapRepository
    {
        return CustomerMapRepository::fromStoreId($this->storeId);
    }

    /**
     * Gets an instance of the service with the default store ID and customer provider.
     *
     * @param string|null $storeId
     * @param CustomersProviderContract|null $customersProvider
     *
     * @return CustomersService
     */
    public static function getNewInstance(string $storeId = null, CustomersProviderContract $customersProvider = null) : CustomersService
    {
        return new static(
            $storeId ?? (string) Commerce::getStoreId(),
            $customersProvider ?? CustomersProvider::getNewInstance()
        );
    }
}
