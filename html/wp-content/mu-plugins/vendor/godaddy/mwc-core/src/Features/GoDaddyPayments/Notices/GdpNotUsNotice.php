<?php

namespace GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Notices;

use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notice;

class GdpNotUsNotice extends Notice
{
    use CanGetNewInstanceTrait;

    /** {@inheritdoc} */
    protected $dismissible = false;

    /** {@inheritdoc} */
    protected $type = self::TYPE_WARNING;

    /** {@inheritdoc} */
    protected $id = 'mwc-payments-godaddy-non-us';

    /**
     * Constructor for GdpNotUsNotice notice.
     */
    public function __construct()
    {
        $this->setButtonUrl(esc_url(admin_url('admin.php?page=wc-settings')));
        $this->setButtonText(__('Update Store', 'mwc-core'));
        $this->setContent(__('GoDaddy Payments is available for United States-based businesses. Please update your Store Address if you are in the U.S.', 'mwc-core'));
    }
}
