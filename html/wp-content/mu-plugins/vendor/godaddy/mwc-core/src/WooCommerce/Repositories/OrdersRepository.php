<?php

namespace GoDaddy\WordPress\MWC\Core\WooCommerce\Repositories;

use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerce\OrdersRepository as CommonOrdersRepository;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Adapters\OrderAdapter;
use WC_Order;

/**
 * Orders repository to handle Core specific logic.
 */
class OrdersRepository extends CommonOrdersRepository
{
    /**
     * Gets a WooCommerce order object with the Marketplaces internal order number.
     *
     * @param string $marketplacesInternalOrderNumber
     * @return WC_Order|null
     */
    public static function getByMarketplacesInternalOrderNumber(string $marketplacesInternalOrderNumber)
    {
        $results = get_posts([
            'post_type'   => 'shop_order',
            'fields'      => 'ids',
            'post_status' => 'any',
            'meta_key'    => OrderAdapter::MARKETPLACES_INTERNAL_ORDER_NUMBER_META_KEY,
            'meta_value'  => $marketplacesInternalOrderNumber,
        ]);

        if (! empty($results) && is_int($results[0])) {
            return CommonOrdersRepository::get($results[0]);
        }

        return null;
    }
}
