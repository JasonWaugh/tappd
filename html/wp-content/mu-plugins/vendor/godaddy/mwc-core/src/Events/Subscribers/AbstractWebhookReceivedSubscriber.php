<?php

namespace GoDaddy\WordPress\MWC\Core\Events\Subscribers;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventContract;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\SubscriberContract;
use GoDaddy\WordPress\MWC\Core\Events\AbstractWebhookReceivedEvent;

/**
 * Base class for webhook received subscribers.
 */
abstract class AbstractWebhookReceivedSubscriber implements SubscriberContract
{
    /**
     * Validates an event.
     *
     * @param AbstractWebhookReceivedEvent $event
     * @return bool
     * @throws Exception
     */
    abstract public function validate(AbstractWebhookReceivedEvent $event) : bool;

    /**
     * Determines whether the event should be handled.
     *
     * @param EventContract $event
     * @return bool
     */
    public function shouldHandle(EventContract $event) : bool
    {
        return $event instanceof AbstractWebhookReceivedEvent;
    }

    /**
     * Handles the event.
     *
     * @param AbstractWebhookReceivedEvent|EventContract $event
     * @throws Exception
     */
    public function handle(EventContract $event)
    {
        if (! $this->shouldHandle($event)) {
            return;
        }

        if (Configuration::get('mwc.debug')) {
            do_action('mwc_before_validating_webhook_received_event', $event);
        }

        if (! $this->validate($event)) {
            return;
        }

        do_action('mwc_before_handling_webhook_received_event', $event);

        $this->handlePayload($event);

        do_action('mwc_after_handling_webhook_received_event', $event);
    }

    /**
     * Handles the event payload.
     *
     * @param AbstractWebhookReceivedEvent $event
     */
    abstract public function handlePayload(AbstractWebhookReceivedEvent $event);
}
