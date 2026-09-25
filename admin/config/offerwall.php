<?php

return [
    'enabled' => env('OFFERWALL_ENABLED', false),
    'sdk_key' => env('OFFERWALL_SDK_KEY', ''),
    'placement' => env('OFFERWALL_PLACEMENT', '#WebOfferwall'),
    'callback_secret' => env('OFFERWALL_CALLBACK_SECRET', ''),
    'callback_mode' => env('OFFERWALL_CALLBACK_MODE', 'hmac_sha256'),
];
