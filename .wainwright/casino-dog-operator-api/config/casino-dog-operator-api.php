<?php

// config for Wainwright/CasinoDogOperatorApi
return [

    'access' => [
      'key' => env('WAINWRIGHT_CASINODOG_OPERATOR_KEY'),
      'secret' => env('WAINWRIGHT_CASINODOG_OPERATOR_SECRET'),
  ],
    'test_settings' => [
        'start_balance' => env('WAINWRIGHT_CASINODOG_OPERATOR_STARTING_BALANCE', 22500), // enter starting balance (integer in cents)
    ],

    // You can customize endpoints as you like, and split/scale however you feel fit. 
    // Please do note if you change a single endpoint to go to different host that the "casino-dog-operator-api:connect-to-api" will not update custom endpoints
    'api_url' => env('WAINWRIGHT_CASINODOG_OPERATOR_API_BASEURL', env('APP_URL')), /* api_url is the base url to contact, it should not end with slash */
    'endpoints' => [
      'create_session' => env(
          'WAINWRIGHT_CASINODOG_OPERATOR_API_CREATESESSION',
          rtrim(env('WAINWRIGHT_CASINODOG_OPERATOR_API_BASEURL', env('APP_URL')), '/').'/api/createSession'
      ),
      'gameslist' => env(
          'WAINWRIGHT_CASINODOG_OPERATOR_API_GAMESLIST',
          rtrim(env('WAINWRIGHT_CASINODOG_OPERATOR_API_BASEURL', env('APP_URL')), '/').'/api/gameslist/all'
      ),
      'access_ping' => env(
          'WAINWRIGHT_CASINODOG_OPERATOR_API_ACCESSPING',
          rtrim(env('WAINWRIGHT_CASINODOG_OPERATOR_API_BASEURL', env('APP_URL')), '/').'/api/accessPing'
      ),
    ],
];