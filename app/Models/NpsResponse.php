<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NpsResponse extends Model
{
    protected $fillable = ['nps_survey_id', 'name', 'email', 'token', 'invited_at', 'score', 'comment', 'answered_at'];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'answered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (NpsResponse $response) {
            $response->token ??= (string) Str::uuid();
        });
    }

    public function npsSurvey(): BelongsTo
    {
        return $this->belongsTo(NpsSurvey::class);
    }
}
