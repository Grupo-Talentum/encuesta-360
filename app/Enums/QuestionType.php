<?php

namespace App\Enums;

enum QuestionType: string
{
    case Rating5 = 'rating_5';
    case Rating10 = 'rating_10';
    case Nps = 'nps';
    case ShortText = 'short_text';
    case LongText = 'long_text';
    case YesNo = 'yes_no';
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';

    public function label(): string
    {
        return match ($this) {
            self::Rating5 => 'Rating 1-5',
            self::Rating10 => 'Rating 1-10',
            self::Nps => 'NPS (0-10)',
            self::ShortText => 'Texto corto',
            self::LongText => 'Texto largo',
            self::YesNo => 'Sí / No',
            self::SingleChoice => 'Opción única',
            self::MultipleChoice => 'Selección múltiple',
        };
    }

    public function hasOptions(): bool
    {
        return in_array($this, [self::SingleChoice, self::MultipleChoice]);
    }
}
