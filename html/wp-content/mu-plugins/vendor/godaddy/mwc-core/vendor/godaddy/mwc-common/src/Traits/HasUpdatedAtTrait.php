<?php

namespace GoDaddy\WordPress\MWC\Common\Traits;

use DateTime;

trait HasUpdatedAtTrait
{
    /** @var DateTime|null date updated */
    protected $updatedAt;

    /**
     * Gets the date when the entity was updated.
     *
     * @return DateTime|null
     */
    public function getUpdatedAt() : ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Sets the date when the entity was updated.
     *
     * @param DateTime|null $value
     * @return $this
     */
    public function setUpdatedAt(?DateTime $value)
    {
        $this->updatedAt = $value;

        return $this;
    }
}
