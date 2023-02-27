<?php

namespace GoDaddy\WordPress\MWC\Common\Models\Products;

use GoDaddy\WordPress\MWC\Common\Models\AbstractModel;
use GoDaddy\WordPress\MWC\Common\Models\CurrencyAmount;
use GoDaddy\WordPress\MWC\Common\Traits\HasNumericIdentifierTrait;
use GoDaddy\WordPress\MWC\Common\Traits\HasRemoteResourceTrait;
use GoDaddy\WordPress\MWC\Common\Traits\HasWeightTrait;

/**
 * Native product object.
 */
class Product extends AbstractModel
{
    use HasNumericIdentifierTrait;
    use HasRemoteResourceTrait;
    use HasWeightTrait;

    /** @var string|null */
    protected $name;

    /** @var string|null */
    protected $sku;

    /** @var CurrencyAmount|null */
    protected $regularPrice;

    /** @var CurrencyAmount|null */
    protected $salePrice;

    /** @var string|null */
    protected $type;

    /** @var string|null */
    protected $status;

    /** @var array<mixed>|null */
    protected $attributes;

    /** @var string|null */
    protected $shortDescription;

    /**
     * Gets the product name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the product SKU.
     *
     * @return string
     */
    public function getSku() : string
    {
        return $this->sku ?? '';
    }

    /**
     * Gets the product regular price.
     *
     * @return CurrencyAmount|null
     */
    public function getRegularPrice()
    {
        return $this->regularPrice;
    }

    /**
     * Gets the product sale price.
     *
     * @return CurrencyAmount|null
     */
    public function getSalePrice()
    {
        return $this->salePrice;
    }

    /**
     * Gets the product type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the product status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets the product attributes.
     *
     * @return array<mixed>
     */
    public function getAttributes() : array
    {
        return $this->attributes ?? [];
    }

    /**
     * Gets the product short description.
     *
     * @return string|null
     */
    public function getShortDescription() : ?string
    {
        return $this->shortDescription;
    }

    /**
     * Sets the product name.
     *
     * @param string|null $value
     * @return $this
     */
    public function setName($value) : Product
    {
        $this->name = $value;

        return $this;
    }

    /**
     * Sets the product SKU.
     *
     * @param string $value
     * @return Product
     */
    public function setSku(string $value) : Product
    {
        $this->sku = $value;

        return $this;
    }

    /**
     * Sets the product regular price.
     *
     * @param CurrencyAmount|null $value
     * @return $this
     */
    public function setRegularPrice($value) : Product
    {
        $this->regularPrice = $value;

        return $this;
    }

    /**
     * Sets the product sale price.
     *
     * @param CurrencyAmount|null $value
     * @return $this
     */
    public function setSalePrice($value) : Product
    {
        $this->salePrice = $value;

        return $this;
    }

    /**
     * Sets the product type.
     *
     * @param string $value
     * @return $this
     */
    public function setType(string $value) : Product
    {
        $this->type = $value;

        return $this;
    }

    /**
     * Sets the product status.
     *
     * @param string $value
     * @return $this
     */
    public function setStatus(string $value) : Product
    {
        $this->status = $value;

        return $this;
    }

    /**
     * Sets the product attributes.
     *
     * @param array<mixed> $value
     * @return Product
     */
    public function setAttributes(array $value) : Product
    {
        $this->attributes = $value;

        return $this;
    }

    /**
     * Sets the product short description.
     *
     * @param string $value
     * @return $this
     */
    public function setShortDescription(string $value) : Product
    {
        $this->shortDescription = $value;

        return $this;
    }
}
