<?php

return [
    /*
     * Implementations of the AuthProviderContract for various services.
     */
    'auth' => [
        'godaddy' => [
            'mwc' => [
                /*
                 * Implementation of the authentication provider for the MWC API.
                 */
                'api' => GoDaddy\WordPress\MWC\Core\Auth\Providers\Platform\AuthProvider::class,

                /*
                 * Implementation of the authentication provider for the Events API.
                 */
                'events_api' => GoDaddy\WordPress\MWC\Core\Auth\Providers\Platform\AuthProvider::class,

                /*
                 * Implementation of the authentication provider for the Emails Service.
                 */
                'emails_service' => GoDaddy\WordPress\MWC\Core\Auth\Providers\EmailsService\JwtAuthProvider::class,

                /*
                 * Implementation of the authentication provider for the Marketplaces API.
                 */
                'marketplaces' => GoDaddy\WordPress\MWC\Core\Auth\Providers\Marketplaces\API\AuthProvider::class,
            ],
        ],
    ],
];
