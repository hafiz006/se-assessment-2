<?php

namespace App\Enums;

enum StorageType: string
{
    case Allocated = 'allocated';
    case Unallocated = 'unallocated';
}
