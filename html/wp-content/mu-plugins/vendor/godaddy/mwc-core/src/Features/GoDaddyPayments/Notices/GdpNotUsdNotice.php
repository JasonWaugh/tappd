<?php

namespace GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Notices;

use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notice;

class GdpNotUsdNotice extends Notice
{
    use CanGetNewInstanceTrait;

    /** {@inheritdoc} */
    protected $dismissible = false;

    /** {@inheritdoc} */
    protected $type = self::TYPE_WARNING;

    /** {@inheritdoc} */
    protected $id = 'mwc-payments-godaddy-non-usd';

    /**
     * Constructor for GdpNotUsdNotice notice.
     */
    public function __construct()
    {
        $this->setButtonUrl(esc_url(admin_url('admin.php?page=wc-settings')));
        $this->setButtonText(__('Change Currency', 'mwc-core'));
        $this->setContent(__('GoDaddy Payments requires U.S. dollar transactions. Please change your Currency in order to use the payment method.', 'mwc-core'));
    }
}
