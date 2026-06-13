<?php

return [
    'image_sizes' => [
        'thumbnail' => [
            'width'     =>  330,
            'height'    =>  330,
            'cropped'   =>  false,
        ],
        'medium' => [
            'width'     =>  600,
            'height'    =>  600,
            'cropped'   =>  false,
        ],
        'large' => [
            'width'     =>  1024,
            'height'    =>  1024,
            'cropped'   =>  false,
        ],
    ],

    'allowed_uploaded_files' => [
        'mimes' => explode(',', env('ALLOWED_UPLOADED_FILES_MIMES', 'jpg,jpeg,png,webp,bmp,gif,txt,pdf,doc,docx,odt,xls,xlsx,ods,csv,ppt,pptx,odp,zip,7z,rar,arj,mp3,wav,flac,aac,ogg,mp4,avi,mkv,webm,mov')),
        'mimetypes' => explode(',', env('ALLOWED_UPLOADED_FILES_MIMETYPES', 'image/jpeg,image/png,image/webp,image/bmp,image/gif,text/plain,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.oasis.opendocument.text,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.oasis.opendocument.spreadsheet,text/csv,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.oasis.opendocument.presentation,application/zip,application/x-7z-compressed,application/x-rar-compressed,application/x-arj,video/mp4,video/x-msvideo,video/x-matroska,video/webm,video/quicktime,audio/mpeg,audio/wav,audio/flac,audio/aac,audio/ogg')),
        'max_size' => (int) env('ALLOWED_UPLOADED_FILES_MAX_SIZE', 102400), // 100MB
    ],

    'allowed_uploaded_images' => [
        'mimes' => explode(',', env('ALLOWED_UPLOADED_FILES_IMAGES', 'jpg,jpeg,png,webp,bmp,gif')),
        'mimetypes' => explode(',', env('ALLOWED_UPLOADED_IMAGES_MIMETYPES', 'image/jpeg,image/png,image/webp,image/bmp,image/gif')),
        'max_size' => (int) env('ALLOWED_UPLOADED_FILES_MAX_SIZE', 102400), // 100MB
    ],
];

