<?php

namespace Tests\Feature;

use App\Models\NpsResponse;
use App\Models\NpsSurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NpsResponseControllerTest extends TestCase
{
    use RefreshDatabase;

    private function response(): NpsResponse
    {
        $survey = NpsSurvey::create(['title' => 'NPS', 'question' => '¿Nos recomendarías?']);

        return $survey->responses()->create(['name' => 'Juan', 'email' => 'juan@test.com']);
    }

    public function test_it_records_score_from_link(): void
    {
        $response = $this->response();

        $this->get("/nps/{$response->token}/responder?score=9")->assertOk();

        $response->refresh();
        $this->assertSame(9, $response->score);
        $this->assertNotNull($response->answered_at);
    }

    public function test_it_rejects_invalid_score(): void
    {
        $response = $this->response();

        $this->get("/nps/{$response->token}/responder?score=11")->assertNotFound();
    }

    public function test_revisiting_the_link_does_not_overwrite_the_score(): void
    {
        $response = $this->response();

        $this->get("/nps/{$response->token}/responder?score=9");
        $this->get("/nps/{$response->token}/responder?score=2");

        $this->assertSame(9, $response->refresh()->score);
    }

    public function test_it_stores_an_optional_comment(): void
    {
        $response = $this->response();
        $this->get("/nps/{$response->token}/responder?score=9");

        $this->post("/nps/{$response->token}/comentario", ['comment' => 'Muy buena atención.'])->assertOk();

        $this->assertSame('Muy buena atención.', $response->refresh()->comment);
    }

    public function test_it_rejects_comment_before_scoring(): void
    {
        $response = $this->response();

        $this->post("/nps/{$response->token}/comentario", ['comment' => 'Hola'])->assertNotFound();
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get('/nps/token-invalido/responder?score=9')->assertNotFound();
    }

    public function test_show_lets_you_choose_a_score_without_preselecting_one(): void
    {
        $response = $this->response();

        $this->get("/nps/{$response->token}")
            ->assertOk()
            ->assertSee($response->npsSurvey->question)
            ->assertDontSee('Gracias por compartir');

        $this->assertNull($response->refresh()->score);
    }

    public function test_show_redirects_to_thanks_if_already_answered(): void
    {
        $response = $this->response();
        $this->get("/nps/{$response->token}/responder?score=7");

        $this->get("/nps/{$response->token}")
            ->assertOk()
            ->assertSee('Gracias por compartir');
    }
}
