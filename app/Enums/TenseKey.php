<?php

namespace App\Enums;

enum TenseKey: string
{
    case PresentSimple = 'present_simple';
    case PresentContinuous = 'present_continuous';
    case PresentPerfect = 'present_perfect';
    case PresentPerfectContinuous = 'present_perfect_continuous';
    case PastSimple = 'past_simple';
    case PastContinuous = 'past_continuous';
    case PastPerfect = 'past_perfect';
    case PastPerfectContinuous = 'past_perfect_continuous';
    case FutureSimple = 'future_simple';
    case FutureContinuous = 'future_continuous';
    case FuturePerfect = 'future_perfect';
    case FuturePerfectContinuous = 'future_perfect_continuous';
}
