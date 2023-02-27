<?php

namespace GoDaddy\WordPress\MWC\Core\WooCommerce\Models\Products;

use GoDaddy\WordPress\MWC\Common\Events\Events;
use GoDaddy\WordPress\MWC\Common\Events\Exceptions\EventTransformFailedException;
use GoDaddy\WordPress\MWC\Common\Models\Image;
use GoDaddy\WordPress\MWC\Common\Models\Products\Product as CommonProduct;
use GoDaddy\WordPress\MWC\Common\Models\Term;
use GoDaddy\WordPress\MWC\Common\Traits\HasDimensionsTrait;
use GoDaddy\WordPress\MWC\Core\Features\Marketplaces\Models\Listing;

/**
 * Core product object.
 */
class Product extends CommonProduct
{
    use HasDimensionsTrait;

    /** @var Term[] */
    protected $categories = [];

    /** @var bool whether the product is virtual */
    protected $isVirtual = false;

    /** @var bool whether the product is downloadable */
    protected $isDownloadable = false;

    /** @var bool whether stock is managed for the product */
    protected $stockManagementEnabled = false;

    /** @var float|null current stock quantity (if managed) */
    protected $currentStock;

    /** @var string|null whether backorders are allowed -- one of: `no`, `notify`, or `yes` */
    protected $backordersAllowed;

    /** @var Product[]|null variations of this product */
    protected $variants;

    /** @var Listing[] */
    protected $marketplacesListings = [];

    /** @var string|null */
    protected $marketplacesBrand;

    /** @var string|null */
    protected $marketplacesCondition;

    /** @var string|null Global Trade Item Number (GTIN) */
    protected ?string $marketplacesGtin = null;

    /** @var string|null product Manufacturer Part Number (MPN) */
    protected ?string $marketplacesMpn = null;

    /** @var string|null ID of the product in Google */
    protected ?string $marketplacesGoogleProductId = null;

    /** @var string|null */
    protected $url;

    /** @var int|null */
    protected $mainImageId;

    /** @var int[] */
    protected $imageIds = [];

    /**
     * Gets the product categories.
     *
     * @return Term[]
     */
    public function getCategories() : array
    {
        return $this->categories;
    }

    /**
     * Gets the product's virtual status.
     *
     * @return bool
     */
    public function getIsVirtual() : bool
    {
        return $this->isVirtual;
    }

    /**
     * Determines if the product is a virtual product.
     *
     * @return bool
     */
    public function isVirtual() : bool
    {
        return $this->getIsVirtual();
    }

    /**
     * Gets the product's downloadable status.
     *
     * @return bool
     */
    public function getIsDownloadable() : bool
    {
        return $this->isDownloadable;
    }

    /**
     * Determines if the product is downloadable.
     *
     * @return bool
     */
    public function isDownloadable() : bool
    {
        return $this->getIsDownloadable();
    }

    /**
     * Gets the product stock management enabled value.
     *
     * @return bool
     */
    public function getStockManagementEnabled() : bool
    {
        return $this->stockManagementEnabled;
    }

    /**
     * Determines if the stock management is enabled for the product.
     *
     * @return bool
     */
    public function hasStockManagementEnabled() : bool
    {
        return $this->getStockManagementEnabled();
    }

    /**
     * Gets the product current stock level.
     *
     * @return float|null
     */
    public function getCurrentStock() : ?float
    {
        return $this->currentStock;
    }

    /**
     * Gets the backorders allowed setting.
     *
     * @return string|null
     */
    public function getBackordersAllowed() : ?string
    {
        return $this->backordersAllowed;
    }

    /**
     * Gets the product variants.
     *
     * @return Product[]|null
     */
    public function getVariants() : ?array
    {
        return $this->variants;
    }

    /**
     * Gets the product Marketplaces listings.
     *
     * @return Listing[]
     */
    public function getMarketplacesListings() : array
    {
        return $this->marketplacesListings;
    }

    /**
     * Gets the product brand, used in Marketplaces listings.
     *
     * @return string|null
     */
    public function getMarketplacesBrand() : ?string
    {
        return $this->marketplacesBrand;
    }

    /**
     * Gets the product condition, used in Marketplaces listings.
     *
     * @return string|null
     */
    public function getMarketplacesCondition() : ?string
    {
        return $this->marketplacesCondition;
    }

    /**
     * Gets the product's Global Trade Item Number (GTIN), used in Marketplaces listings.
     *
     * @return string|null
     */
    public function getMarketplacesGtin() : ?string
    {
        return $this->marketplacesGtin;
    }

    /**
     * Gets the product's Manufacturer Part Number (MPN), used in Marketplaces listings.
     *
     * @return string|null
     */
    public function getMarketplacesMpn() : ?string
    {
        return $this->marketplacesMpn;
    }

    /**
     * Gets the ID of the product in Google. This will exist if the Google sales channel is connected and a Google listing has been created.
     *
     * @return string|null
     */
    public function getMarketplacesGoogleProductId() : ?string
    {
        return $this->marketplacesGoogleProductId;
    }

    /**
     * Gets the product URL.
     *
     * @return string|null
     */
    public function getUrl() : ?string
    {
        return $this->url;
    }

    /**
     * Gets the identifier of the main product image.
     *
     * @return int|null
     */
    public function getMainImageId() : ?int
    {
        return $this->mainImageId;
    }

    /**
     * Gets the product main image.
     *
     * @return Image|null
     */
    public function getMainImage() : ?Image
    {
        return $this->mainImageId ? Image::get($this->mainImageId) : null;
    }

    /**
     * Gets the identifier for the product images.
     *
     * @return int[]
     */
    public function getImageIds() : array
    {
        return $this->imageIds;
    }

    /**
     * Gets the product images.
     *
     * @return Image[]
     */
    public function getImages() : array
    {
        $images = [];

        foreach ($this->imageIds as $imageId) {
            if ($image = Image::get($imageId)) {
                $images[] = $image;
            }
        }

        return $images;
    }

    /**
     * Sets the product categories.
     *
     * @param Term[] $value
     * @return $this
     */
    public function setCategories(array $value) : Product
    {
        $this->categories = $value;

        return $this;
    }

    /**
     * Sets the product virtual status.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsVirtual(bool $value) : Product
    {
        $this->isVirtual = $value;

        return $this;
    }

    /**
     * Sets the product downloadable status.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsDownloadable(bool $value) : Product
    {
        $this->isDownloadable = $value;

        return $this;
    }

    /**
     * Sets the stock management enabled value.
     *
     * @param bool $value
     * @return $this
     */
    public function setStockManagementEnabled(bool $value) : Product
    {
        $this->stockManagementEnabled = $value;

        return $this;
    }

    /**
     * Sets the current stock level.
     *
     * @param float|null $value
     * @return $this
     */
    public function setCurrentStock(?float $value) : Product
    {
        $this->currentStock = $value;

        return $this;
    }

    /**
     * Sets the backorders allowed setting.
     *
     * @param string|null $value
     * @return Product
     */
    public function setBackordersAllowed(?string $value) : Product
    {
        $this->backordersAllowed = $value;

        return $this;
    }

    /**
     * Sets the product variants.
     *
     * @param Product[] $value
     * @return $this
     */
    public function setVariants(array $value) : Product
    {
        $this->variants = $value;

        return $this;
    }

    /**
     * Sets the product Marketplaces listings.
     *
     * @param Listing[] $value
     * @return $this
     */
    public function setMarketplacesListings(array $value) : Product
    {
        $this->marketplacesListings = $value;

        return $this;
    }

    /**
     * Sets the product brand, used in Marketplaces listings.
     *
     * @param string|null $value
     * @return $this
     */
    public function setMarketplacesBrand(?string $value) : Product
    {
        $this->marketplacesBrand = $value;

        return $this;
    }

    /**
     * Sets the product condition, used in Marketplaces listings.
     *
     * @param string|null $value
     * @return $this
     */
    public function setMarketplacesCondition(?string $value) : Product
    {
        $this->marketplacesCondition = $value;

        return $this;
    }

    /**
     * Sets the product's Global Trade Item Number (GTIN), used in Marketplaces listings.
     *
     * @param string|null $value
     * @return $this
     */
    public function setMarketplacesGtin(?string $value) : Product
    {
        $this->marketplacesGtin = $value;

        return $this;
    }

    /**
     * Sets the product's Manufacturer Part Number (MPN), used in Marketplaces listings.
     *
     * @param string|null $value
     * @return $this
     */
    public function setMarketplacesMpn(?string $value) : Product
    {
        $this->marketplacesMpn = $value;

        return $this;
    }

    /**
     * Sets the Marketplaces Google product ID.
     *
     * @param string|null $value
     * @return $this
     */
    public function setMarketplacesGoogleProductId(?string $value) : Product
    {
        $this->marketplacesGoogleProductId = $value;

        return $this;
    }

    /**
     * Sets the product URL.
     *
     * Note: any value set on this method will NOT be persisted on the corresponding WC_Product, as it is not possible
     * to directly set a post permalink.
     *
     * If you need to persist a URL for a WC_Product, consider using the {@see setName()} method instead.
     *
     * @param string|null $value
     * @return $this
     */
    public function setUrl(?string $value) : Product
    {
        $this->url = $value;

        return $this;
    }

    /**
     * Sets the main image ID.
     *
     * @param int|null $value
     * @return $this
     */
    public function setMainImageId(?int $value) : Product
    {
        $this->mainImageId = $value;

        return $this;
    }

    /**
     * Sets the image IDs.
     *
     * @param int[] $value
     * @return $this
     */
    public function setImageIds(array $value) : Product
    {
        $this->imageIds = $value;

        return $this;
    }

    /**
     * Updates the product.
     *
     * This method also broadcast model events.
     *
     * @return self
     * @throws EventTransformFailedException
     */
    public function update() : Product
    {
        $product = parent::update();

        Events::broadcast($this->buildEvent('product', 'update'));

        return $product;
    }

    /**
     * Saves the product.
     *
     * This method also broadcast model events.
     *
     * @return self
     * @throws EventTransformFailedException
     */
    public function save() : Product
    {
        $product = parent::save();

        Events::broadcast($this->buildEvent('product', 'create'));

        return $product;
    }

    /**
     * Deletes the product.
     *
     * This method also broadcast model events.
     *
     * @throws EventTransformFailedException
     */
    public function delete()
    {
        parent::delete();

        Events::broadcast($this->buildEvent('product', 'delete'));
    }
}
