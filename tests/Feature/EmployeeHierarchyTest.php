<?php

namespace Tests\Feature;

use App\Enums\RelationType;
use App\Models\Employee;
use App\Models\EmployeeRelation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_superior_creates_bidirectional_relation(): void
    {
        $rogelio = Employee::create(['name' => 'Rogelio', 'email' => 'rogelio@test.com']);
        $ruben = Employee::create(['name' => 'Ruben', 'email' => 'ruben@test.com', 'superior_id' => $rogelio->id]);

        $this->assertDatabaseHas('employee_relations', [
            'employee_id' => $ruben->id,
            'related_employee_id' => $rogelio->id,
            'type' => RelationType::Superior->value,
        ]);
        $this->assertDatabaseHas('employee_relations', [
            'employee_id' => $rogelio->id,
            'related_employee_id' => $ruben->id,
            'type' => RelationType::Subordinate->value,
        ]);
    }

    public function test_employees_sharing_the_same_superior_become_peers_automatically(): void
    {
        $rogelio = Employee::create(['name' => 'Rogelio', 'email' => 'rogelio@test.com']);
        $ruben = Employee::create(['name' => 'Ruben', 'email' => 'ruben@test.com', 'superior_id' => $rogelio->id]);
        $pepe = Employee::create(['name' => 'Pepe', 'email' => 'pepe@test.com', 'superior_id' => $rogelio->id]);

        $this->assertDatabaseHas('employee_relations', [
            'employee_id' => $ruben->id,
            'related_employee_id' => $pepe->id,
            'type' => RelationType::Peer->value,
        ]);
        $this->assertDatabaseHas('employee_relations', [
            'employee_id' => $pepe->id,
            'related_employee_id' => $ruben->id,
            'type' => RelationType::Peer->value,
        ]);

        // Rogelio (el superior) no es peer de sus propios subordinados.
        $this->assertDatabaseMissing('employee_relations', [
            'employee_id' => $rogelio->id,
            'related_employee_id' => $ruben->id,
            'type' => RelationType::Peer->value,
        ]);
    }

    public function test_changing_superior_removes_old_relations_and_peers(): void
    {
        $rogelio = Employee::create(['name' => 'Rogelio', 'email' => 'rogelio@test.com']);
        $carlos = Employee::create(['name' => 'Carlos', 'email' => 'carlos@test.com']);
        $ruben = Employee::create(['name' => 'Ruben', 'email' => 'ruben@test.com', 'superior_id' => $rogelio->id]);
        $pepe = Employee::create(['name' => 'Pepe', 'email' => 'pepe@test.com', 'superior_id' => $rogelio->id]);

        // Ruben se muda de equipo: ahora reporta a Carlos, ya no a Rogelio.
        $ruben->update(['superior_id' => $carlos->id]);

        $this->assertDatabaseMissing('employee_relations', [
            'employee_id' => $ruben->id,
            'related_employee_id' => $rogelio->id,
        ]);
        $this->assertDatabaseHas('employee_relations', [
            'employee_id' => $ruben->id,
            'related_employee_id' => $carlos->id,
            'type' => RelationType::Superior->value,
        ]);

        // Ya no es peer de Pepe (compañero anterior bajo Rogelio).
        $this->assertDatabaseMissing('employee_relations', [
            'employee_id' => $ruben->id,
            'related_employee_id' => $pepe->id,
        ]);
    }

    public function test_removing_superior_clears_relation(): void
    {
        $rogelio = Employee::create(['name' => 'Rogelio', 'email' => 'rogelio@test.com']);
        $ruben = Employee::create(['name' => 'Ruben', 'email' => 'ruben@test.com', 'superior_id' => $rogelio->id]);

        $ruben->update(['superior_id' => null]);

        $this->assertDatabaseCount('employee_relations', 0);
    }

    public function test_manual_relations_are_not_affected_by_unrelated_saves(): void
    {
        $juan = Employee::create(['name' => 'Juan', 'email' => 'juan@test.com']);
        $carlos = Employee::create(['name' => 'Carlos', 'email' => 'carlos@test.com']);

        EmployeeRelation::create(['employee_id' => $juan->id, 'related_employee_id' => $carlos->id, 'type' => RelationType::Peer]);

        // Guardar a Juan sin tocar superior_id no debe alterar la relacion manual.
        $juan->update(['name' => 'Juan Actualizado']);

        $this->assertDatabaseHas('employee_relations', [
            'employee_id' => $juan->id,
            'related_employee_id' => $carlos->id,
            'type' => RelationType::Peer->value,
        ]);
    }
}
