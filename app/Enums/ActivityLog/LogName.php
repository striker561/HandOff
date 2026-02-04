<?php

namespace App\Enums\ActivityLog;

enum LogName: string
{
    case DEFAULT = 'default';
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case AUTH = 'auth';
    case PROJECT = 'project';
    case DELIVERABLE = 'deliverable';
    case MEETING = 'meeting';
    case FILE = 'files';
    case COMMENT = 'comment';
}