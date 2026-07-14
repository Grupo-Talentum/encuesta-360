<?php

namespace App\Enums;

enum SurveyStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
}
