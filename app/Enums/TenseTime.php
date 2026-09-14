<?php

namespace App\Enums;

enum TenseTime: string
{
    case Present = 'present';
    case Past = 'past';
    case Future = 'future';
}
