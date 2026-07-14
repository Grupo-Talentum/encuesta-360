<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Employees\RelationManagers\RelationsRelationManager;
use App\Filament\Admin\Resources\SurveySections\RelationManagers\QuestionsRelationManager;
use App\Filament\Admin\Resources\Surveys\RelationManagers\SectionsRelationManager;
use App\Models\Employee;
use App\Models\Survey;
use App\Models\SurveySection;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_resource_pages_load(): void
    {
        $team = Team::create(['name' => 'Marketing']);
        $employee = Employee::create(['name' => 'Juan', 'email' => 'juan@test.com', 'team_id' => $team->id]);
        $survey = Survey::create(['title' => 'Encuesta 360']);
        $section = SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboracion', 'order' => 1]);

        $this->get('/admin/teams')->assertOk();
        $this->get('/admin/teams/create')->assertOk();
        $this->get("/admin/teams/{$team->id}/edit")->assertOk();

        $this->get('/admin/employees')->assertOk();
        $this->get('/admin/employees/create')->assertOk();
        $this->get("/admin/employees/{$employee->id}/edit")->assertOk();

        $this->get('/admin/surveys')->assertOk();
        $this->get('/admin/surveys/create')->assertOk();
        $this->get("/admin/surveys/{$survey->id}/edit")->assertOk();

        $this->get("/admin/survey-sections/{$section->id}/edit")->assertOk();
    }

    public function test_can_create_employee_relation_via_relation_manager(): void
    {
        $juan = Employee::create(['name' => 'Juan', 'email' => 'juan@test.com']);
        $carlos = Employee::create(['name' => 'Carlos', 'email' => 'carlos@test.com']);

        Livewire::test(RelationsRelationManager::class, [
            'ownerRecord' => $juan,
            'pageClass' => \App\Filament\Admin\Resources\Employees\Pages\EditEmployee::class,
        ])
            ->callTableAction('create', data: [
                'related_employee_id' => $carlos->id,
                'type' => 'superior',
            ]);

        $this->assertDatabaseHas('employee_relations', [
            'employee_id' => $juan->id,
            'related_employee_id' => $carlos->id,
            'type' => 'superior',
        ]);
    }

    public function test_can_create_section_and_question_via_relation_managers(): void
    {
        $survey = Survey::create(['title' => 'Encuesta 360']);

        Livewire::test(SectionsRelationManager::class, [
            'ownerRecord' => $survey,
            'pageClass' => \App\Filament\Admin\Resources\Surveys\Pages\EditSurvey::class,
        ])
            ->callTableAction('create', data: [
                'title' => 'Colaboracion',
                'order' => 1,
            ]);

        $this->assertDatabaseHas('survey_sections', [
            'survey_id' => $survey->id,
            'title' => 'Colaboracion',
        ]);

        $section = SurveySection::where('survey_id', $survey->id)->first();

        Livewire::test(QuestionsRelationManager::class, [
            'ownerRecord' => $section,
            'pageClass' => \App\Filament\Admin\Resources\SurveySections\Pages\EditSurveySection::class,
        ])
            ->callTableAction('create', data: [
                'title' => 'Comunica bien?',
                'type' => 'rating_10',
                'order' => 1,
                'is_required' => true,
            ]);

        $this->assertDatabaseHas('survey_questions', [
            'survey_section_id' => $section->id,
            'title' => 'Comunica bien?',
            'type' => 'rating_10',
        ]);
    }

    public function test_can_create_choice_question_with_options(): void
    {
        $survey = Survey::create(['title' => 'Encuesta 360']);
        $section = SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboracion', 'order' => 1]);

        Livewire::test(QuestionsRelationManager::class, [
            'ownerRecord' => $section,
            'pageClass' => \App\Filament\Admin\Resources\SurveySections\Pages\EditSurveySection::class,
        ])
            ->callTableAction('create', data: [
                'title' => '¿Con qué frecuencia colabora?',
                'type' => 'single_choice',
                'options' => ['Nunca', 'A veces', 'Siempre'],
                'order' => 1,
                'is_required' => true,
            ]);

        $question = \App\Models\SurveyQuestion::where('survey_section_id', $section->id)->first();

        $this->assertNotNull($question);
        $this->assertSame('single_choice', $question->type->value);
        $this->assertSame(['Nunca', 'A veces', 'Siempre'], $question->options);
    }
}
