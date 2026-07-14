<?php

namespace App\Livewire;

use App\Actions\Evaluations\SubmitEvaluationAction;
use App\Enums\EvaluationStatus;
use App\Enums\QuestionType;
use App\Enums\SurveyStatus;
use App\Models\Evaluation;
use App\Models\EvaluationSession;
use App\Models\SurveyQuestion;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SurveyResponse extends Component
{
    public ?EvaluationSession $session = null;

    public string $step = 'invalid';

    public ?int $currentEvaluationId = null;

    /** @var array<int, mixed> */
    public array $answers = [];

    public function mount(string $uuid): void
    {
        $this->session = EvaluationSession::with(['survey.sections.questions', 'evaluations.evaluatee'])
            ->where('uuid', $uuid)
            ->first();

        if (! $this->session) {
            $this->step = 'invalid';

            return;
        }

        if ($this->session->survey->status !== SurveyStatus::Published) {
            $this->step = 'invalid';

            return;
        }

        $this->resolveStep();
    }

    public function start(): void
    {
        $this->step = 'form';
        $this->dispatch('survey-scroll-top');
    }

    public function submit(): void
    {
        $this->validate($this->rules());

        $evaluation = $this->session->evaluations->firstWhere('id', $this->currentEvaluationId);

        app(SubmitEvaluationAction::class)->execute($evaluation, $this->answers);

        $this->answers = [];
        $this->session->refresh();
        $this->session->load('evaluations');
        $this->resolveStep();
        $this->dispatch('survey-scroll-top');
    }

    #[Computed]
    public function currentEvaluation(): ?Evaluation
    {
        return $this->session->evaluations->firstWhere('id', $this->currentEvaluationId);
    }

    #[Computed]
    public function completedCount(): int
    {
        return $this->session->evaluations->where('status', EvaluationStatus::Completed)->count();
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->session->evaluations->count();
    }

    private function resolveStep(): void
    {
        $pending = $this->session->evaluations->firstWhere('status', EvaluationStatus::Pending);

        if (! $pending) {
            $this->step = 'done';
            $this->currentEvaluationId = null;

            return;
        }

        $this->currentEvaluationId = $pending->id;
        $this->step = $this->completedCount > 0 ? 'form' : 'intro';
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $rules = [];

        foreach ($this->session->survey->sections as $section) {
            foreach ($section->questions as $question) {
                foreach ($this->rulesForQuestion($question) as $key => $rule) {
                    $rules[$key] = $rule;
                }
            }
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForQuestion(SurveyQuestion $question): array
    {
        $key = "answers.{$question->id}";
        $required = $question->is_required ? 'required' : 'nullable';

        return match ($question->type) {
            QuestionType::Rating5 => [$key => [$required, 'integer', 'min:1', 'max:5']],
            QuestionType::Rating10 => [$key => [$required, 'integer', 'min:1', 'max:10']],
            QuestionType::Nps => [$key => [$required, 'integer', 'min:0', 'max:10']],
            QuestionType::ShortText, QuestionType::LongText => [$key => [$required, 'string', 'max:2000']],
            QuestionType::YesNo => [$key => [$required, 'in:0,1']],
            QuestionType::SingleChoice => [$key => [$required, Rule::in($question->options ?? [])]],
            QuestionType::MultipleChoice => [
                $key => [$required, 'array'],
                "{$key}.*" => [Rule::in($question->options ?? [])],
            ],
        };
    }

    public function render()
    {
        return view('livewire.survey-response');
    }
}
