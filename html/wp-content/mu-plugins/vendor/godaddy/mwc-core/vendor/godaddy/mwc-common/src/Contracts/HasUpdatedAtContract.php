<?php

namespace GoDaddy\WordPress\MWC\Common\Contracts;

use DateTime;

interface HasUpdatedAtContract
{
    /**
     * Gets the date when the entity was updated.
     *
     * @return DateTime|null
     */
    public function getUpdatedAt() : ?DateTime;

    /**
     * Sets the date when the entity was updated.
     *
     * @param DateTime|null $value
     * @return $this
     */
    public function setUpdatedAt(?DateTime $value);
}
