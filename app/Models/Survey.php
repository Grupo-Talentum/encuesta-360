<?php

namespace App\Models;

use App\Enums\SurveyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Survey extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => SurveyStatus::Draft->value,
    ];

    protected $fillable = [
        'title',
        'team_id',
        'description',
        'instructions',
        'start_message',
        'end_message',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SurveyStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(SurveySection::class)->orderBy('order');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(SurveyQuestion::class, SurveySection::class)->orderBy('survey_sections.order')->orderBy('survey_questions.order');
    }
}
