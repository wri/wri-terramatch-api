<?php

return [
    'validation-rules' => [
        'logo-image' => 'file|mimes:jpg,png',
        'cover-image' => 'file|mimes:jpg,png',
        'thumbnail' => 'file|mimes:jpg,png',
        'cover-image-with-svg' => 'file|mimes:jpg,png,svg,heic,heif',
        'photos' => 'file|mimes:jpg,png,mp4,heic,heif',
        'pdf' => 'file|mimes:pdf',
        'documents' => 'file|mimes:pdf,xls,xlsx,csv,txt,doc,docx,bin',
        'general-documents' => 'file|mimes:pdf,xls,xlsx,csv,txt,png,jpg,doc,mp4,docx,bin,heic,heif',
        'spreadsheet' => 'file|mimes:pdf,xls,xlsx,csv,txt',
    ],
    'validation-file-types' => [
        'logo-image' => 'media',
        'thumbnail' => 'media',
        'cover-image' => 'media',
        'cover-image-with-svg' => 'media',
        'photos' => 'media',
        'pdf' => 'media',
        'general-documents' => 'documents',
        'spreadsheet' => 'documents',
    ],
];
