<?php

return [
    'commercial_email' => env('QUOTE_COMMERCIAL_EMAIL', 'comercial@example.com'),

    /*
    |--------------------------------------------------------------------------
    | Mínimo padrão (fallback)
    |--------------------------------------------------------------------------
    | Aplicado quando o estado selecionado não tem min_quote_value definido.
    */
    'default_minimum' => env('QUOTE_DEFAULT_MINIMUM', 2000),
];
