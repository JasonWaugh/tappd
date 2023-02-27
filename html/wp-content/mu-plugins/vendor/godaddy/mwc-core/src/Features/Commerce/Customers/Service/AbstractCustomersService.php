<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service;

use GoDaddy\WordPress\MWC\Common\Exceptions\WordPressDatabaseException;
use GoDaddy\WordPress\MWC\Common\Models\Contracts\CustomerContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Commerce;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\Contracts\CustomersProviderContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects\CustomerBase;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects\UpsertCustomerInput;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\GoDaddy\CustomersProvider;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\Operations\Contracts\CreateOrUpdateCustomerOperationContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Service\Responses\Contracts\CreateOrUpdateCustomerResponseContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\CommerceException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\Contracts\CommerceExceptionContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\MissingCustomerRemoteIdException;
use GoDaddy\WordPress\MWC\Core\Repositories\AbstractResourceMapRepository;

abstract class AbstractCustomersService
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
     * Creates or updates the customer.
     *
     * @param CreateOrUpdateCustomerOperationContract $operation
     * @return CreateOrUpdateCustomerResponseContract
     * @throws CommerceExceptionContract
     */
    abstract public function createOrUpdateCustomer(CreateOrUpdateCustomerOperationContract $operation) : CreateOrUpdateCustomerResponseContract;

    /**
     * Creates an instance in the remote service.
     *
     * @param CustomerContract $customer
     *
     * @return CustomerBase
     * @throws MissingCustomerRemoteIdException
     * @throws CommerceExceptionContract
     */
    protected function createOrUpdateCustomerInRemoteService(CustomerContract $customer) : CustomerBase
    {
        $customerData = $this->customersProvider->customers()->createOrUpdate(
            $this->getCreateOrUpdateCustomerInput($customer)
        );

        if (! $customerData->customerId) {
            throw MissingCustomerRemoteIdException::withDefaultMessage();
        }

        return $customerData;
    }

    /**
     * Creates an instance of {@see UpsertCustomerInput} using the information from the given customer.
     *
     * @param CustomerContract $customer
     *
     * @return UpsertCustomerInput
     * @throws CommerceExceptionContract
     */
    protected function getCreateOrUpdateCustomerInput(CustomerContract $customer) : UpsertCustomerInput
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
     * @param CustomerContract $customer
     *
     * @return ?CustomerBase
     */
    protected function getCustomerData(CustomerContract $customer) : ?CustomerBase
    {
        //TODO Implement in MWC-10402
        return null;
    }

    /**
     * Attempts to get the remote ID for the local id, either customer id or order id.
     *
     * @param int $localId
     *
     * @return string
     */
    protected function getCustomerRemoteId(int $localId) : string
    {
        return (string) $this->getCustomerMapRepository()->getRemoteId($localId);
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
     * @return AbstractResourceMapRepository
     */
    abstract protected function getCustomerMapRepository() : AbstractResourceMapRepository;

    /**
     * Gets an instance of the service with the default store ID and customer provider.
     *
     * @param string|null $storeId
     * @param CustomersProviderContract|null $customersProvider
     *
     * @return AbstractCustomersService
     */
    public static function getNewInstance(string $storeId = null, CustomersProviderContract $customersProvider = null) : AbstractCustomersService
    {
        return new static(
            $storeId ?? (string) Commerce::getStoreId(),
            $customersProvider ?? CustomersProvider::getNewInstance()
        );
    }
}
