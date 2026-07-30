<?php

namespace App\Models;

use App\Enums\NpsSurveyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NpsSurvey extends Model
{
    protected $attributes = [
        'status' => NpsSurveyStatus::Draft->value,
    ];

    protected $fillable = ['title', 'question', 'status', 'sent_at'];

    protected function casts(): array
    {
        return [
            'status' => NpsSurveyStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function responses(): HasMany
    {
        return $this->hasMany(NpsResponse::class);
    }

    public function npsScore(): ?int
    {
        $answered = $this->responses()->whereNotNull('score');
        $total = $answered->count();

        if ($total === 0) {
            return null;
        }

        $promoters = (clone $answered)->where('score', '>=', 9)->count();
        $detractors = (clone $answered)->where('score', '<=', 6)->count();

        return (int) round((($promoters - $detractors) / $total) * 100);
    }
}
