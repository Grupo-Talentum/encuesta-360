<?php

namespace App\Models;

use App\Enums\EvaluationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Evaluation extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => EvaluationStatus::Pending->value,
    ];

    protected $fillable = ['uuid', 'survey_id', 'evaluation_session_id', 'evaluator_id', 'evaluatee_id', 'status', 'completed_at'];

    protected function casts(): array
    {
        return [
            'status' => EvaluationStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Evaluation $evaluation) {
            $evaluation->uuid ??= (string) Str::uuid();
        });
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EvaluationSession::class, 'evaluation_session_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }

    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluatee_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class);
    }
}
