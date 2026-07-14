<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EvaluationSession extends Model
{
    protected $fillable = ['uuid', 'survey_id', 'evaluator_id'];

    protected static function booted(): void
    {
        static::creating(function (EvaluationSession $session) {
            $session->uuid ??= (string) Str::uuid();
        });
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class)->orderBy('id');
    }
}
