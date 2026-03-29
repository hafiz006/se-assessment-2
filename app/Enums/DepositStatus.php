<?php

namespace App\Enums;

enum DepositStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Withdrawn = 'withdrawn';
}
