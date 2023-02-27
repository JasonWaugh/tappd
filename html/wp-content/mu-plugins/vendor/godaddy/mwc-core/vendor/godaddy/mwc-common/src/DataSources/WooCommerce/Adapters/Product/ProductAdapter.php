<?php

namespace GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\Product;

use GoDaddy\WordPress\MWC\Common\DataSources\Contracts\DataSourceAdapterContract;
use GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\CurrencyAmountAdapter;
use GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\WeightAdapter;
use GoDaddy\WordPress\MWC\Common\Models\CurrencyAmount;
use GoDaddy\WordPress\MWC\Common\Models\Products\Product;
use GoDaddy\WordPress\MWC\Common\Models\Weight;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use WC_Data_Exception;
use WC_Product;
use WC_Product_External;
use WC_Product_Grouped;
use WC_Product_Variable;
use WC_Product_Variation;

/**
 * Product adapter.
 *
 * Converts between a native product object and a WooCommerce product object.
 *
 * @method static static getNewInstance(WC_Product $product)
 */
class ProductAdapter implements DataSourceAdapterContract
{
    use CanGetNewInstanceTrait;

    /** @var WC_Product WooCommerce product object */
    protected $source;

    /** @var class-string<Product> the product class name */
    protected $productClass = Product::class;

    /**
     * Product adapter constructor.
     *
     * @param WC_Product $product WooCommerce product object.
     */
    public function __construct(WC_Product $product)
    {
        $this->source = $product;
    }

    /**
     * Converts a WooCommerce product object into a native product object.
     *
     * @return Product
     */
    public function convertFromSource() : Product
    {
        /** @var Product $product @phpstan-ignore-next-line */
        $product = $this->productClass::getNewInstance();

        $this->convertProductPropertiesFromSource($product);
        $this->maybeConvertProductWeightFromSource($product);

        return $product;
    }

    /**
     * Converts basic product properties from source.
     *
     * @param Product $product
     * @return void
     */
    protected function convertProductPropertiesFromSource(Product $product) : void
    {
        $product
            ->setId($this->source->get_id())
            ->setName($this->source->get_name())
            ->setRegularPrice($this->convertCurrencyAmountFromSource((float) $this->source->get_regular_price('edit')))
            ->setSalePrice($this->convertCurrencyAmountFromSource((float) $this->source->get_sale_price('edit')))
            ->setType($this->source->get_type())
            ->setStatus($this->source->get_status())
            ->setSku($this->source->get_sku())
            ->setShortDescription($this->source->get_short_description());
    }

    /**
     * Sets the weight of the given product if the source product has a weight defined.
     *
     * @param Product $product
     * @return void
     */
    protected function maybeConvertProductWeightFromSource(Product $product) : void
    {
        if ($this->source->has_weight()) {
            $product->setWeight($this->convertWeightFromSource($this->source));
        }
    }

    /**
     * Converts a native product object into a WooCommerce product object.
     *
     * @param Product|null $product native product object to convert
     * @param bool $getNewInstance whether to get a fresh instance of a WC_Product
     * @return WC_Product WooCommerce product object
     * @throws WC_Data_Exception
     */
    public function convertToSource(?Product $product = null, bool $getNewInstance = true) : WC_Product
    {
        if (! $product instanceof Product) {
            return $this->source;
        }

        if ($getNewInstance) {
            $this->instantiateSourceProduct($product);
        }

        $this->source->set_id($product->getId());
        $this->source->set_name($product->getName());
        $this->source->set_regular_price($this->convertCurrencyAmountToSource($product->getRegularPrice()));
        $this->source->set_sale_price($this->convertCurrencyAmountToSource($product->getSalePrice()));
        $this->source->set_status($product->getStatus());
        $this->source->set_sku($product->getSku());
        $this->source->set_short_description((string) $product->getShortDescription());

        $this->maybeSetSourceProductWeight($product);

        return $this->source;
    }

    /**
     * Instantiates the proper product according to its type.
     *
     * @param Product|null $product native product object
     */
    protected function instantiateSourceProduct($product = null)
    {
        switch ($product ? $product->getType() : '') {
            case 'external':
                $this->source = new WC_Product_External();
                break;

            case 'grouped':
                $this->source = new WC_Product_Grouped();
                break;

            case 'variable':
                $this->source = new WC_Product_Variable();
                break;

            case 'variation':
                $this->source = new WC_Product_Variation();
                break;

            case 'simple':
            default:
                $this->source = new WC_Product();
        }
    }

    /**
     * Converts a product amount from source.
     *
     * @param float $amount
     * @return CurrencyAmount
     */
    protected function convertCurrencyAmountFromSource(float $amount) : CurrencyAmount
    {
        return (new CurrencyAmountAdapter($amount, WooCommerceRepository::getCurrency()))->convertFromSource();
    }

    /**
     * Converts a product amount to source.
     *
     * @param CurrencyAmount|null $amount
     * @return string
     */
    protected function convertCurrencyAmountToSource($amount) : string
    {
        if (! isset($amount) || 0 === $amount->getAmount()) {
            return '';
        }

        return (new CurrencyAmountAdapter($amount->getAmount(), WooCommerceRepository::getCurrency()))->convertToSource($amount);
    }

    /**
     * Converts the weight of the given product.
     *
     * @param WC_Product $product
     * @return Weight
     */
    protected function convertWeightFromSource(WC_Product $product) : Weight
    {
        return WeightAdapter::getNewInstance($product->get_weight('edit'))->convertFromSource();
    }

    /**
     * Sets the weight of the source product if the given product has a weight defined.
     *
     * @param Product $product
     * @return void
     */
    protected function maybeSetSourceProductWeight(Product $product) : void
    {
        if ($product->getWeight()) {
            $this->source->set_weight($this->convertWeightToSource($product->getWeight()));
        }
    }

    /**
     * Converts the given weight into a float value.
     *
     * @param Weight|null $weight
     * @return float
     */
    protected function convertWeightToSource(?Weight $weight) : float
    {
        return WeightAdapter::getNewInstance(0.0)->convertToSource($weight);
    }
}
