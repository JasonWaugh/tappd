<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce;

use Exception;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Components\Traits\HasComponentsTrait;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Common\Exceptions\WordPressDatabaseException;
use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;
use GoDaddy\WordPress\MWC\Common\Platforms\Exceptions\PlatformRepositoryException;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Traits\CanHandleWordPressDatabaseExceptionTrait;

class Commerce extends AbstractFeature
{
    use HasComponentsTrait;
    use CanHandleWordPressDatabaseExceptionTrait;

    /** @var string transient that disables the feature */
    public const TRANSIENT_DISABLE_FEATURE = 'godaddy_mwc_commerce_disabled';

    /** @var class-string<ComponentContract>[] alphabetically ordered list of components to load */
    protected array $componentClasses = [
        CreateCommerceContextsTableAction::class,
        CreateCommerceMapResourceTypesTableAction::class,
        CreateCommerceMapUuidsTableAction::class,
    ];

    /**
     * {@inheritDoc}
     */
    public static function getName() : string
    {
        return 'commerce';
    }

    /**
     * {@inheritDoc}
     */
    public static function shouldLoad() : bool
    {
        if (get_transient(static::TRANSIENT_DISABLE_FEATURE)) {
            return false;
        }

        return parent::shouldLoad();
    }

    /**
     * Initializes the component.
     *
     * @throws Exception
     */
    public function load() : void
    {
        try {
            /** @throws WordPressDatabaseException|BaseException|Exception */
            $this->loadComponents();
        } catch (WordPressDatabaseException $exception) {
            $this->handleWordPressDatabaseException($exception, static::getName(), static::TRANSIENT_DISABLE_FEATURE);
        }
    }

    /**
     * Gets the store's ID.
     *
     * @return string|null
     */
    public static function getStoreId() : ?string
    {
        try {
            return PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getStoreRepository()->getStoreId();
        } catch (PlatformRepositoryException $exception) {
            return null;
        }
    }
}
