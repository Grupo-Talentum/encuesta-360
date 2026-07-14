<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
}
