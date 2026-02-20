<?php

return [

      /*
    |--------------------------------------------------------------------------
    | Console Commands
    |--------------------------------------------------------------------------
    |
    | This option allows you to add additional Artisan commands that should
    | be available within the Tinker environment. Once the command is in
    | this array you may execute the command in Tinker using its name.
    |
    */

    'commands' => [
        App\Console\Commands\ExportContacts::class,
        App\Console\Commands\SeedSampleData::class,
        App\Console\Commands\ServeAndDev::class,
    ],

      /*
    |--------------------------------------------------------------------------
    | Auto Aliased Classes
    |--------------------------------------------------------------------------
    |
    | Tinker will not automatically alias classes in your vendor namespaces
    | but you may explicitly allow a subset of classes to get aliased by
    | adding the names of each of those classes to the following list.
    |
    */

    'alias' => [
        'Link'                        => 'App\Models\Link',
        'Option'                      => 'App\Models\Option',
        'Post'                        => 'App\Models\Post',
        'PostFile'                    => 'App\Models\PostFile',
        'PostFileMeta'                => 'App\Models\PostFileMeta',
        'PostFileRelationship'        => 'App\Models\PostFileRelationship',
        'PostImage'                   => 'App\Models\PostImage',
        'PostImageAlbum'              => 'App\Models\PostImageAlbum',
        'PostImageAlbumRelationship'  => 'App\Models\PostImageAlbumRelationship',
        'PostImageMeta'               => 'App\Models\PostImageMeta',
        'PostImageRelationship'       => 'App\Models\PostImageRelationship',
        'PostImageSize'               => 'App\Models\PostImageSize',
        'PostMeta'                    => 'App\Models\PostMeta',
        'PostTaxonomy'                => 'App\Models\PostTaxonomy',
        'PostTerm'                    => 'App\Models\PostTer',
        'PostTermMeta'                => 'App\Models\PostTermMeta',
        'PostTermRelationship'        => 'App\Models\PostTermRelationship',
        'PostType'                    => 'App\Models\PostType',
        'User'                        => 'App\Models\User',
        'UserMeta'                    => 'App\Models\UserMeta',
        'UserSession'                 => 'App\Models\UserSession',
    ],

      /*
    |--------------------------------------------------------------------------
    | Classes That Should Not Be Aliased
    |--------------------------------------------------------------------------
    |
    | Typically, Tinker automatically aliases classes as you require them in
    | Tinker. However, you may wish to never alias certain classes, which
    | you may accomplish by listing the classes in the following array.
    |
    */

    'dont_alias' => [
        'App\Nova',
    ],

];
