<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Models;

class GuestCustomer extends AbstractCustomer
{
    /** @var int|null order ID to bind to this customer */
    protected ?int $orderId = null;

    /**
     * Gets an order ID to bind to this customer.
     *
     * @return int|null
     */
    public function getOrderId() : ?int
    {
        return $this->orderId;
    }

    /**
     * Sets the guest order ID that serves as the source of data for this customer.
     *
     * @param int $value
     * @return $this
     */
    public function setOrderId(?int $value) : GuestCustomer
    {
        $this->orderId = $value;

        return $this;
    }
}
