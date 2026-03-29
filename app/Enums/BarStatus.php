<?php

namespace App\Enums;

enum BarStatus: string
{
    case Held = 'held';
    case Withdrawn = 'withdrawn';
}
