<?php

return [

    /*
    |--------------------------------------------------------------------------
    | QUERY Method Demos
    |--------------------------------------------------------------------------
    |
    | Not every host accepts requests with the QUERY method yet (Laravel Cloud
    | rejects them), so the demos that make QUERY requests can be hidden.
    |
    */

    'query_method' => (bool) env('DEMO_QUERY_METHOD', true),

];
