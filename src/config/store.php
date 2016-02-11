<?php

return [

    // public path

    'public_path' => public_path(),

    // model class

    'model' => 'App\\File',

    // prevent physically delete files

    'prevent_deletions' => false,

    // path pattern to store file

    'path_pattern' => '/content/files/{contentHash|subpath}/{contentHash}.{ext}',

];
