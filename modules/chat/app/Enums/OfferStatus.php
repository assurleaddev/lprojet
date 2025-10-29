<?php

namespace Modules\Chat\Enums;

enum OfferStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    // Optional: Add Countered, Expired etc. later if needed
}