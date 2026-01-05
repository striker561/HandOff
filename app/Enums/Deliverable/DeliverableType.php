<?php

namespace App\Enums\Deliverable;

enum DeliverableType: string
{
    case FILE = 'file';
    case LINK = 'link';
    case TEXT = 'text';
    case CODE = 'code';
    case SCOPE = 'scope';
    case OTHER = 'other';
    case DESIGN = 'design';
    case RESEARCH = 'research';
}