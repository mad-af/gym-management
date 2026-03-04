<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    // Other roles are managed dynamically in the database
}
