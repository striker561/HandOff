<?php

namespace App\Enums\Deliverable;

enum DeliverableStatus: string
{
    case DRAFT = 'draft';
    case FINAL = 'final';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_REVIEW = 'in_review';
}