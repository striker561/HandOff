<?php

namespace App\Enums\Deliverable;

enum DeliverableAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case STATUS_CHANGED = 'status_changed';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case FILE_UPLOADED = 'file_uploaded';
    case FILE_DOWNLOADED = 'file_downloaded';
    case FILE_DELETED = 'file_deleted';
}
