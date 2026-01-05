<?php

namespace App\Enums\ActivityLog;

enum LogName: string
{
    case DEFAULT = 'default';
    case AUTH = 'auth';
    case PROJECT = 'project';
    case DELIVERABLE = 'deliverable';
    case MEETING = 'meeting';
}