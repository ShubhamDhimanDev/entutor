<?php

namespace App\Enums;

enum TenseAspect: string
{
    case Simple = 'simple';
    case Continuous = 'continuous';
    case Perfect = 'perfect';
    case PerfectContinuous = 'perfect_continuous';
}
