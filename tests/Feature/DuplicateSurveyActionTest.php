<?php

namespace Tests\Feature;

use App\Actions\Surveys\DuplicateSurveyAction;
use App\Enums\QuestionType;
use App\Enums\SurveyStatus;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveySection;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateSurveyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_duplicates_sections_and_questions_for_a_new_team_in_draft_status(): void
    {
        $originalTeam = Team::create(['name' => 'Producto']);
        $newTeam = Team::create(['name' => 'Ventas']);

        $survey = Survey::create([
            'title' => 'Encuesta 360',
            'team_id' => $originalTeam->id,
            'status' => SurveyStatus::Published,
            'end_message' => 'Gracias.',
        ]);

        $section = SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboracion', 'order' => 1]);
        SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Comunica bien?', 'type' => QuestionType::Rating10, 'order' => 1]);
        SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Frecuencia', 'type' => QuestionType::SingleChoice, 'options' => ['Nunca', 'Siempre'], 'order' => 2]);

        $copy = app(DuplicateSurveyAction::class)->execute($survey, $newTeam->id);

        $this->assertNotSame($survey->id, $copy->id);
        $this->assertSame('Encuesta 360 (copia)', $copy->title);
        $this->assertSame($newTeam->id, $copy->team_id);
        $this->assertSame(SurveyStatus::Draft, $copy->status);
        $this->assertSame('Gracias.', $copy->end_message);

        $this->assertCount(1, $copy->sections);
        $copySection = $copy->sections->first();
        $this->assertSame('Colaboracion', $copySection->title);
        $this->assertCount(2, $copySection->questions);
        $this->assertSame(['Nunca', 'Siempre'], $copySection->questions->last()->options);

        // La encuesta original no se modifica.
        $survey->refresh();
        $this->assertSame(SurveyStatus::Published, $survey->status);
        $this->assertSame($originalTeam->id, $survey->team_id);
    }

    public function test_duplicate_without_team_leaves_team_id_null(): void
    {
        $survey = Survey::create(['title' => 'Encuesta 360']);
        SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboracion', 'order' => 1]);

        $copy = app(DuplicateSurveyAction::class)->execute($survey, null);

        $this->assertNull($copy->team_id);
    }
}
