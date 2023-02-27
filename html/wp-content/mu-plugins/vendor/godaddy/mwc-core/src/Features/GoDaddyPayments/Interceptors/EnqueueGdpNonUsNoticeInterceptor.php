<?php

namespace GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notices;
use GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Notices\GdpNotUsdNotice;
use GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Notices\GdpNotUsNotice;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt\Onboarding;

class EnqueueGdpNonUsNoticeInterceptor extends AbstractGoDaddyPaymentsNoticeInterceptor
{
    /**
     * Determines whether the component should be loaded or not.
     *
     * @throws Exception
     * @return bool
     */
    public static function shouldLoad() : bool
    {
        if (! Poynt::isEnabled() ||
            ! Onboarding::canEnablePaymentGateway(Onboarding::getStatus()) ||
            ! WooCommerceRepository::isWooCommerceActive()
        ) {
            return false;
        }

        return parent::shouldLoad();
    }

    /**
     * {@inheritDoc}
     */
    public function enqueueNotice() : void
    {
        if ('US' !== WooCommerceRepository::getBaseCountry()) {
            Notices::enqueueAdminNotice(GdpNotUsNotice::getNewInstance());
        }

        if ('USD' !== WooCommerceRepository::getCurrency()) {
            Notices::enqueueAdminNotice(GdpNotUsdNotice::getNewInstance());
        }
    }
}
