<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Google reCAPTCHA v3 integration.
    | You can get your keys from: https://www.google.com/recaptcha/admin
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    'enabled' => env('RECAPTCHA_ENABLED', true),
    
    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA v3 Score Threshold
    |--------------------------------------------------------------------------
    |
    | reCAPTCHA v3 returns a score (1.0 is very likely a good interaction, 
    | 0.0 is very likely a bot). This threshold determines what score is 
    | acceptable. Recommended: 0.5 (adjust based on your needs)
    |
    */
    'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
    
    /*
    |--------------------------------------------------------------------------
    | API Endpoint
    |--------------------------------------------------------------------------
    |
    | The Google reCAPTCHA API endpoint for verification
    |
    */
    'api_url' => 'https://www.google.com/recaptcha/api/siteverify',
];
