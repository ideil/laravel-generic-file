<?php

return [

    // domain to access files

    'domain' => env('APP_STATIC_URL', 'http://localhost/'),

    // inperpolate path for just uploaded file

    'path_pattern' => '/content/files/{contentHash|subpath}/{contentHash}.{ext}',

];
