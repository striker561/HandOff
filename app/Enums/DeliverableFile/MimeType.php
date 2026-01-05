<?php

namespace App\Enums\DeliverableFile;

enum MimeType: string
{

    case PDF = 'application/pdf';
    case PNG = 'image/pdf';
    case JPG = 'image/jpeg';
    case ZIP = 'application/zip';
    case MD = 'text/md';
    case TEXT = 'text/plain';
}