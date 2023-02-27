<?php

namespace GoDaddy\WordPress\MWC\Common\Traits;

use DateTime;

trait HasCreatedAtTrait
{
    /** @var DateTime|null date created */
    protected $createdAt;

    /**
     * Gets the date when the entity was created.
     *
     * @return DateTime|null
     */
    public function getCreatedAt() : ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets the date when the entity was created.
     *
     * @param DateTime|null $value
     * @return $this
     */
    public function setCreatedAt(?DateTime $value)
    {
        $this->createdAt = $value;

        return $this;
    }
}
