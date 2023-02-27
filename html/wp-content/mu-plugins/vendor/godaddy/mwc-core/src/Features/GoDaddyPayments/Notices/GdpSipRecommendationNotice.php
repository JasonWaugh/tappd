<?php

namespace GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Notices;

use GoDaddy\WordPress\MWC\Common\Repositories\WordPressRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notice;

class GdpSipRecommendationNotice extends Notice
{
    use CanGetNewInstanceTrait;

    /** {@inheritdoc} */
    protected $dismissible = true;

    /** {@inheritdoc} */
    protected $type = self::TYPE_INFO;

    /** {@inheritdoc} */
    protected $id = 'mwc-godaddy-payments-sip-recommendation';

    /**
     * Constructor for GdpSipRecommendationNotice notice.
     */
    public function __construct()
    {
        $this->setButtonUrl(esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=godaddy-payments-payinperson')));
        $this->setButtonText(__('Get Started', 'mwc-core'));
        $this->setContent(sprintf(
            '<img src="%1$s" alt="'.esc_attr__('Provided by GoDaddy', 'mwc-core').'"/>
                <p>'.__('Use GoDaddy Payments Selling in Person to sync local pickup and delivery orders to your Smart Terminal. Sell anything, anywhere and get paid fast with next-day deposits.', 'mwc-core').'</p>',
            esc_url(WordPressRepository::getAssetsUrl('images/branding/gd-icon.svg')),
        ));
        $this->setTitle(__('GoDaddy Selling in Person', 'mwc-core'));
        $this->setCssClasses(['mwc-godaddy-payments-recommendation']);
    }
}
