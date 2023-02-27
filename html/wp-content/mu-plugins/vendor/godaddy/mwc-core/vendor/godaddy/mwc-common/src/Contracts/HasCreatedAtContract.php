<?php

namespace GoDaddy\WordPress\MWC\Common\Contracts;

use DateTime;

interface HasCreatedAtContract
{
    /**
     * Gets the date when the entity was created.
     *
     * @return DateTime|null
     */
    public function getCreatedAt() : ?DateTime;

    /**
     * Sets the date when the entity was created.
     *
     * @param DateTime|null $value
     * @return $this
     */
    public function setCreatedAt(?DateTime $value);
}
