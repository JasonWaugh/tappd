<?php

namespace GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments;

use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentClassesNotDefinedException;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentLoadFailedException;
use GoDaddy\WordPress\MWC\Common\Components\Traits\HasComponentsTrait;
use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;
use GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Interceptors\EnqueueBusinessStatusNoticeInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Interceptors\EnqueueCompleteProfileNoticeInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Interceptors\EnqueueOnboardingErrorNoticeInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Interceptors\EnqueuePoyntPluginNoticeInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\GoDaddyPayments\Interceptors\EnqueueWooStagingNoticeInterceptor;

/**
 * The GoDaddy Payments feature.
 */
class GoDaddyPayments extends AbstractFeature
{
    use HasComponentsTrait;

    /** @var class-string<ComponentContract>[] alphabetically ordered list of components to load */
    protected array $componentClasses = [
        EnqueueBusinessStatusNoticeInterceptor::class,
        EnqueueCompleteProfileNoticeInterceptor::class,
        EnqueueOnboardingErrorNoticeInterceptor::class,
        EnqueuePoyntPluginNoticeInterceptor::class,
        //        EnqueueWooStagingNoticeInterceptor::class,
    ];

    /**
     * {@inheritDoc}
     */
    public static function getName() : string
    {
        return 'godaddy_payments';
    }

    /**
     * {@inheritDoc}
     *
     * @throws ComponentClassesNotDefinedException|ComponentLoadFailedException
     */
    public function load() : void
    {
        $this->loadComponents();
    }
}
