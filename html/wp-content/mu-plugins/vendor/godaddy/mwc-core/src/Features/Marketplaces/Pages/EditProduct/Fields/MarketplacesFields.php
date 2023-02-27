<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Marketplaces\Pages\EditProduct\Fields;

use Exception;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\SanitizationHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerce\ProductsRepository;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Adapters\ProductAdapter;
use WC_Product;
use WP_Post;

/**
 * This class is responsible for outputting and handling Marketplaces fields displayed in the Edit Product page.
 */
class MarketplacesFields implements ComponentContract
{
    /**
     * Loads the component.
     *
     * @return void
     * @throws Exception
     */
    public function load() : void
    {
        Register::action()
            ->setGroup('woocommerce_product_options_general_product_data')
            ->setHandler([$this, 'renderMarketplacesGeneralFieldsSimpleProduct'])
            ->execute();

        Register::action()
            ->setGroup('woocommerce_product_options_inventory_product_data')
            ->setHandler([$this, 'renderMarketplacesInventoryFieldsSimpleProduct'])
            ->execute();

        Register::action()
            ->setGroup('woocommerce_product_after_variable_attributes')
            ->setHandler([$this, 'renderMarketplacesFieldsProductVariation'])
            ->setArgumentsCount(3)
            ->setPriority(20)
            ->execute();

        Register::action()
            ->setGroup('woocommerce_process_product_meta')
            ->setHandler([$this, 'saveSimpleProductFields'])
            ->execute();

        Register::action()
            ->setGroup('woocommerce_save_product_variation')
            ->setHandler([$this, 'saveProductVariationFields'])
            ->execute();
    }

    /**
     * Outputs the Marketplaces general fields for simple products.
     *
     * @internal
     *
     * @return void
     */
    public function renderMarketplacesGeneralFieldsSimpleProduct() : void
    {
        echo '<div id="gd-marketplaces-simple-product-general-fields" class="options_group show_if_simple marketplaces-product-fields">';

        woocommerce_wp_text_input([
            'id'    => ProductAdapter::MARKETPLACES_BRAND_META_KEY,
            'label' => esc_html__('Product Brand', 'mwc-core'),
        ]);

        woocommerce_wp_text_input([
            'id'    => ProductAdapter::MARKETPLACES_CONDITION_META_KEY,
            'label' => esc_html__('Product Condition', 'mwc-core'),
        ]);

        echo '</div>';
    }

    /**
     * Outputs the Marketplaces inventory fields for simple products.
     *
     * @internal
     *
     * @return void
     */
    public function renderMarketplacesInventoryFieldsSimpleProduct() : void
    {
        echo '<div id="gd-marketplaces-simple-product-inventory-fields" class="options_group show_if_simple marketplaces-product-fields">';

        woocommerce_wp_text_input([
            'id'          => ProductAdapter::MARKETPLACES_GTIN_META_KEY,
            'label'       => '<abbr title="'.esc_attr__('Global Trade Item Number', 'mwc-core').'">'.esc_html_x('GTIN', 'Global Trade Item Number (GTIN)', 'mwc-core').'</abbr>',
            'description' => __('A Global Trade Item Number is used to uniquely identify your product on Marketplaces & Social product listings. It can be found next to the barcode.', 'mwc-core'),
            'desc_tip'    => true,
        ]);

        woocommerce_wp_text_input([
            'id'          => ProductAdapter::MARKETPLACES_MPN_META_KEY,
            'label'       => '<abbr title="'.esc_attr__('Manufacturer Part Number', 'mwc-core').'">'.esc_html_x('MPN', 'Manufacturer Part Number (MPN)', 'mwc-core').'</abbr>',
            'description' => __('A Manufacturer Part Number is used to uniquely identify your product on Marketplaces & Social product listings. Only use the MPN assigned by the manufacturer.', 'mwc-core'),
            'desc_tip'    => true,
        ]);

        echo '</div>';
    }

    /**
     * Outputs the Marketplaces fields for product variations.
     *
     * @param int|mixed $loop
     * @param array|mixed $variationData
     * @param WP_Post|mixed $variation
     * @return void
     */
    public function renderMarketplacesFieldsProductVariation($loop, $variationData, $variation) : void
    {
        $loop = TypeHelper::int($loop, 0);
        $product = $variation instanceof WP_Post ? ProductsRepository::get($variation->ID) : null;
        $brand = $condition = $gtin = $mpn = ''; // default values

        if ($product) {
            $brand = TypeHelper::string($product->get_meta(ProductAdapter::MARKETPLACES_BRAND_META_KEY), '');
            $condition = TypeHelper::string($product->get_meta(ProductAdapter::MARKETPLACES_CONDITION_META_KEY), '');
            $gtin = TypeHelper::string($product->get_meta(ProductAdapter::MARKETPLACES_GTIN_META_KEY), '');
            $mpn = TypeHelper::string($product->get_meta(ProductAdapter::MARKETPLACES_MPN_META_KEY), '');
        }

        echo '<div id="gd-marketplaces-product-variation-fields-'.esc_attr((string) $loop).'" class="marketplaces-product-fields">';

        woocommerce_wp_text_input([
            'id'            => sprintf('%s_%d', ProductAdapter::MARKETPLACES_BRAND_META_KEY, $loop),
            'name'          => sprintf('%s[%d]', ProductAdapter::MARKETPLACES_BRAND_META_KEY, $loop),
            'value'         => $brand,
            'label'         => esc_html__('Product Brand', 'mwc-core'),
            'wrapper_class' => 'form-row form-row-first',
        ]);

        woocommerce_wp_text_input([
            'id'            => sprintf('%s_%d', ProductAdapter::MARKETPLACES_CONDITION_META_KEY, $loop),
            'name'          => sprintf('%s[%d]', ProductAdapter::MARKETPLACES_CONDITION_META_KEY, $loop),
            'value'         => $condition,
            'label'         => esc_html__('Product Condition', 'mwc-core'),
            'wrapper_class' => 'form-row form-row-last',
        ]);

        woocommerce_wp_text_input([
            'id'            => sprintf('%s_%d', ProductAdapter::MARKETPLACES_GTIN_META_KEY, $loop),
            'name'          => sprintf('%s[%d]', ProductAdapter::MARKETPLACES_GTIN_META_KEY, $loop),
            'value'         => $gtin,
            'label'         => '<abbr title="'.esc_attr__('Global Trade Item Number', 'mwc-core').'">'.esc_html_x('GTIN', 'Global Trade Item Number (GTIN)', 'mwc-core').'</abbr>',
            'description'   => __('A Global Trade Item Number is used to uniquely identify your product on Marketplaces & Social product listings. It can be found next to the barcode.', 'mwc-core'),
            'desc_tip'      => true,
            'wrapper_class' => 'form-row form-row-first',
        ]);

        woocommerce_wp_text_input([
            'id'            => sprintf('%s_%d', ProductAdapter::MARKETPLACES_MPN_META_KEY, $loop),
            'name'          => sprintf('%s[%d]', ProductAdapter::MARKETPLACES_MPN_META_KEY, $loop),
            'value'         => $mpn,
            'label'         => '<abbr title="'.esc_attr__('Manufacturer Part Number', 'mwc-core').'">'.esc_html_x('MPN', 'Manufacturer Part Number (MPN)', 'mwc-core').'</abbr>',
            'description'   => __('A Manufacturer Part Number is used to uniquely identify your product on Marketplaces & Social product listings. Only use the MPN assigned by the manufacturer.', 'mwc-core'),
            'desc_tip'      => true,
            'wrapper_class' => 'form-row form-row-last',
        ]);

        echo '</div>';
    }

    /**
     * Saves the simple product fields.
     *
     * @internal
     *
     * @param int|mixed $productId
     * @return void
     */
    public function saveSimpleProductFields($productId) : void
    {
        if ('simple' !== ArrayHelper::get($_POST, 'product-type')) {
            return;
        }

        $product = ProductsRepository::get(TypeHelper::int($productId, 0));

        if (! $product) {
            return;
        }

        $this->updateProductMetadata(
            $product,
            TypeHelper::string(ArrayHelper::get($_POST, ProductAdapter::MARKETPLACES_BRAND_META_KEY), ''),
            TypeHelper::string(ArrayHelper::get($_POST, ProductAdapter::MARKETPLACES_CONDITION_META_KEY), ''),
            TypeHelper::string(ArrayHelper::get($_POST, ProductAdapter::MARKETPLACES_GTIN_META_KEY), ''),
            TypeHelper::string(ArrayHelper::get($_POST, ProductAdapter::MARKETPLACES_MPN_META_KEY), '')
        );
    }

    /**
     * Saves the variable product fields.
     *
     * @internal
     *
     * @param int|mixed $variationId
     * @return void
     */
    public function saveProductVariationFields($variationId) : void
    {
        $product = ProductsRepository::get(TypeHelper::int($variationId, 0));

        if (! $product) {
            return;
        }

        // find the index for the given variation ID and save the associated cost
        $variationIndex = array_search($variationId, TypeHelper::array(ArrayHelper::get($_POST, 'variable_post_id'), []));
        $variationBrand = $_POST[ProductAdapter::MARKETPLACES_BRAND_META_KEY][$variationIndex] ?? null;
        $variationCondition = $_POST[ProductAdapter::MARKETPLACES_CONDITION_META_KEY][$variationIndex] ?? null;
        $variationGtin = $_POST[ProductAdapter::MARKETPLACES_GTIN_META_KEY][$variationIndex] ?? null;
        $variationMpn = $_POST[ProductAdapter::MARKETPLACES_MPN_META_KEY][$variationIndex] ?? null;

        $this->updateProductMetadata($product, $variationBrand, $variationCondition, $variationGtin, $variationMpn);
    }

    /**
     * Updates the product Marketplaces metadata.
     *
     * @param WC_Product $product
     * @param string|null $brand
     * @param string|null $condition
     * @param string|null $gtin
     * @param string|null $mpn
     * @return void
     */
    protected function updateProductMetadata(WC_Product $product, ?string $brand, ?string $condition, ?string $gtin, ?string $mpn) : void
    {
        $metaData = [
            ProductAdapter::MARKETPLACES_BRAND_META_KEY     => $brand,
            ProductAdapter::MARKETPLACES_CONDITION_META_KEY => $condition,
            ProductAdapter::MARKETPLACES_GTIN_META_KEY      => $gtin,
            ProductAdapter::MARKETPLACES_MPN_META_KEY       => $mpn,
        ];

        foreach ($metaData as $key => $value) {
            if (! empty($value)) {
                $product->update_meta_data($key, SanitizationHelper::input(StringHelper::unslash($value)));
            } else {
                $product->delete_meta_data($key);
            }
        }

        $product->save_meta_data();
    }
}
