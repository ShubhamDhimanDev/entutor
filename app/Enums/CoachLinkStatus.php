<?php

namespace App\Enums;

enum CoachLinkStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Revoked = 'revoked';
}
