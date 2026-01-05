<?php

namespace App\Enums\Meeting;

enum MeetingLocation: string
{
    case ZOOM = 'zoom';
    case MEET = 'meet';
    case TEAMS = 'teams';
    case PHYSICAL = 'physical';
}