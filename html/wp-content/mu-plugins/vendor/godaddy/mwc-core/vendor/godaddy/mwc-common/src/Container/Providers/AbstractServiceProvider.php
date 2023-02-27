<?php

namespace GoDaddy\WordPress\MWC\Common\Container\Providers;

use GoDaddy\WordPress\MWC\Common\Container\Contracts\ContainerAwareContract;
use GoDaddy\WordPress\MWC\Common\Container\Contracts\ContainerContract;
use GoDaddy\WordPress\MWC\Common\Container\Contracts\ServiceProviderContract;

abstract class AbstractServiceProvider implements ServiceProviderContract
{
    /** @var ContainerContract */
    protected ContainerContract $container;

    /**
     * Sets the container instance.
     *
     * @return $this
     */
    public function setContainer(ContainerContract $container) : ContainerAwareContract
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Gets the container instance.
     *
     * @return ContainerContract
     */
    public function getContainer() : ContainerContract
    {
        return $this->container;
    }
}
