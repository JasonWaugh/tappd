<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects;

class CustomerBase extends AbstractDataObject
{
    public ?string $customerId;
    public string $firstName;
    public string $lastName;

    /** @var Email[] */
    public array $emails;

    /** @var Phone[] */
    public array $phones = [];

    /** @var Address[] */
    public array $addresses = [];

    /**
     * Creates a new data object.
     *
     * @param array{
     *     customerId: ?string,
     *     firstName: string,
     *     lastName: string,
     *     emails: Email[],
     *     phones?: Phone[],
     *     addresses?: Address[]
     * } $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
