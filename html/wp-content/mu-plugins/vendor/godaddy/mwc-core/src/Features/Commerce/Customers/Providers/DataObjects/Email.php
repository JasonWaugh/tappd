<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Customers\Providers\DataObjects;

class Email extends AbstractDataObject
{
    public string $email;
    public bool $default = true;

    /**
     * Creates a new data object.
     *
     * @param array{
     *     email: string,
     *     default?: bool
     * } $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
