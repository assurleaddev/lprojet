<?php

namespace Modules\Chat\Enums;

enum OfferStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case AwaitingBuyer = 'awaiting_buyer'; // Counter offer from seller
    case Expired = 'expired';
    case Withdrawn = 'withdrawn';
}