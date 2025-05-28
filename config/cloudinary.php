<?php

/*
 * This file is part of the Laravel Cloudinary package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Cloudinary settings. Cloudinary is a cloud
    | service that offers a solution to a web application's entire image
    | management pipeline.
    |
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'dhtgcccax'),
    'api_key' => env('CLOUDINARY_API_KEY', '163561997864559'),
    'api_secret' => env('CLOUDINARY_API_SECRET', 'PG3yyAHUdTWtq4EFDA7-3HZuJEs'),
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),
    'folder' => env('CLOUDINARY_FOLDER'),
    'archive' => env('CLOUDINARY_ARCHIVE'),
    'secure' => env('CLOUDINARY_SECURE', true),
];
