<?php

namespace GoDaddy\WordPress\MWC\Common\Plugin;

use Exception;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentClassesNotDefinedException;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentLoadFailedException;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Plugin\Contracts\PlatformPluginContract;

/**
 * Base system plugin.
 */
class BaseSystemPlugin extends BasePlatformPlugin
{
    /**
     * List of platform plugin instances loaded in this plugin.
     *
     * @var PlatformPluginContract[]
     */
    protected $platformPlugins = [];

    /**
     * Initializes the system plugin.
     *
     * @return void
     *
     * @throws Exception
     */
    public function load() : void
    {
        $this->initializePlatformPlugins();

        $this->initializeConfiguration();

        $this->onConfigurationLoaded();
    }

    /**
     * Loads components, and stores the platform plugins in the object.
     *
     * @return void
     * @throws ComponentClassesNotDefinedException|ComponentLoadFailedException
     */
    protected function initializePlatformPlugins()
    {
        $this->platformPlugins = ArrayHelper::where($this->loadComponents(), function ($plugin) {
            return $this->isPlatformPlugin($plugin);
        }, false);
    }

    /**
     * Tests if the specified value is a platform plugin.
     *
     * @param object $plugin The plugin to test.
     *
     * @return bool true if this is a platform plugin, otherwise false.
     */
    protected function isPlatformPlugin($plugin) : bool
    {
        return $plugin instanceof PlatformPluginContract;
    }

    /**
     * Initializes the {@see Configuration} class using the configuration directories for all the components.
     *
     * @return void
     * @throws BaseException
     */
    protected function initializeConfiguration() : void
    {
        Configuration::initialize($this->getCombinedConfigurationDirectories());
        Configuration::reload();
    }

    /**
     * Gets a list of absolute configuration directory paths for all the platform plugins.
     *
     * The paths for the configuration directories of the system plugin are added last so that
     * the system plugin values can override values defined by platform plugins.
     *
     * @return string[]
     * @throws BaseException
     */
    protected function getCombinedConfigurationDirectories() : array
    {
        /* @phpstan-ignore-next-line string[] and not array<mixed> is expected here */
        return ArrayHelper::combine(
            ArrayHelper::combine(...array_map(
                static function (PlatformPluginContract $plugin) {
                    return $plugin->getAbsolutePathOfConfigurationDirectories();
                },
                $this->platformPlugins
            )),
            $this->getAbsolutePathOfConfigurationDirectories()
        );
    }

    /**
     * Performs actions that the platform plugins should do just after configuration is loaded.
     *
     * @return void
     */
    public function onConfigurationLoaded()
    {
        foreach ($this->platformPlugins as $plugin) {
            $plugin->onConfigurationLoaded();
        }
    }
}
