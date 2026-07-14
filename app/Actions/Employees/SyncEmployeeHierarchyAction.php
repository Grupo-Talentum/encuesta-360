<?php

namespace App\Actions\Employees;

use App\Enums\RelationType;
use App\Models\Employee;
use App\Models\EmployeeRelation;
use Illuminate\Support\Facades\DB;

class SyncEmployeeHierarchyAction
{
    public function execute(Employee $employee, ?int $previousSuperiorId): void
    {
        $newSuperiorId = $employee->superior_id;

        if ($previousSuperiorId === $newSuperiorId) {
            return;
        }

        DB::transaction(function () use ($employee, $previousSuperiorId, $newSuperiorId) {
            if ($previousSuperiorId) {
                $this->removePair($employee->id, $previousSuperiorId, [RelationType::Superior, RelationType::Subordinate]);
            }

            if ($newSuperiorId) {
                EmployeeRelation::updateOrCreate(
                    ['employee_id' => $employee->id, 'related_employee_id' => $newSuperiorId],
                    ['type' => RelationType::Superior]
                );
                EmployeeRelation::updateOrCreate(
                    ['employee_id' => $newSuperiorId, 'related_employee_id' => $employee->id],
                    ['type' => RelationType::Subordinate]
                );
            }

            $oldSiblingIds = $previousSuperiorId
                ? Employee::where('superior_id', $previousSuperiorId)->where('id', '!=', $employee->id)->pluck('id')
                : collect();

            $newSiblingIds = $newSuperiorId
                ? Employee::where('superior_id', $newSuperiorId)->where('id', '!=', $employee->id)->pluck('id')
                : collect();

            foreach ($oldSiblingIds->diff($newSiblingIds) as $siblingId) {
                $this->removePair($employee->id, $siblingId, [RelationType::Peer]);
            }

            foreach ($newSiblingIds as $siblingId) {
                EmployeeRelation::updateOrCreate(
                    ['employee_id' => $employee->id, 'related_employee_id' => $siblingId],
                    ['type' => RelationType::Peer]
                );
                EmployeeRelation::updateOrCreate(
                    ['employee_id' => $siblingId, 'related_employee_id' => $employee->id],
                    ['type' => RelationType::Peer]
                );
            }
        });
    }

    /**
     * @param  array<RelationType>  $types
     */
    private function removePair(int $employeeId, int $otherEmployeeId, array $types): void
    {
        EmployeeRelation::where(function ($query) use ($employeeId, $otherEmployeeId) {
            $query->where('employee_id', $employeeId)->where('related_employee_id', $otherEmployeeId);
        })->orWhere(function ($query) use ($employeeId, $otherEmployeeId) {
            $query->where('employee_id', $otherEmployeeId)->where('related_employee_id', $employeeId);
        })->whereIn('type', $types)->delete();
    }
}
