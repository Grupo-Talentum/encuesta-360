<?php

namespace App\Models;

use App\Actions\Employees\SyncEmployeeHierarchyAction;
use App\Enums\RelationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'position', 'company', 'email', 'team_id', 'superior_id'];

    private ?int $previousSuperiorId = null;

    protected static function booted(): void
    {
        static::updating(function (Employee $employee) {
            $employee->previousSuperiorId = $employee->getOriginal('superior_id');
        });

        static::saved(function (Employee $employee) {
            app(SyncEmployeeHierarchyAction::class)->execute($employee, $employee->previousSuperiorId);
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'superior_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'superior_id');
    }

    public function relations(): HasMany
    {
        return $this->hasMany(EmployeeRelation::class);
    }

    public function superiors(): HasMany
    {
        return $this->relations()->where('type', RelationType::Superior);
    }

    public function subordinates(): HasMany
    {
        return $this->relations()->where('type', RelationType::Subordinate);
    }

    public function peers(): HasMany
    {
        return $this->relations()->where('type', RelationType::Peer);
    }

    public function evaluationsAsEvaluator(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    public function evaluationsAsEvaluatee(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluatee_id');
    }
}
