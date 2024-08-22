<?php

return [
    'validation-rules' => [
        'logo-image' => 'file|mimes:jpg,png',
        'cover-image' => 'file|mimes:jpg,png',
        'cover-image-with-svg' => 'file|mimes:jpg,png,svg',
        'photos' => 'file|mimes:jpg,png',
        'pdf' => 'file|mimes:pdf',
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
