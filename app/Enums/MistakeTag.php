<?php

namespace App\Enums;

enum MistakeTag: string
{
    case Tense = 'tense';
    case Articles = 'articles';
    case Prepositions = 'prepositions';
    case DirectTranslation = 'direct_translation';
    case GrammarBasics = 'grammar_basics';
    case Vocabulary = 'vocabulary';
    case Pronunciation = 'pronunciation';
}
