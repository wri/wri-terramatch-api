<?php

return [
    'validation-rules' => [
        'logo-image' => 'file|max:3000|mimes:jpg,png',
        'cover-image' => 'file|max:20000|mimes:jpg,png',
        'cover-image-with-svg' => 'file|max:20000|mimes:jpg,png,svg',
        'photos' => 'file|max:25000|mimes:jpg,png',
        'pdf' => 'file|max:5000|mimes:pdf',
        'documents' => 'file|mimes:pdf,xls,xlsx,csv,txt,doc',
        'general-documents' => 'file|mimes:pdf,xls,xlsx,csv,txt,png,jpg,doc',
        'spreadsheet' => 'file|mimes:pdf,xls,xlsx,csv,txt',
    ],
    'validation-file-types' => [
        'logo-image' => 'media',
        'cover-image' => 'media',
        'cover-image-with-svg' => 'media',
        'photos' => 'media',
        'pdf' => 'media',
        'general-documents' => 'documents',
        'spreadsheet' => 'documents',
    ],
];
