<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Enums\SurveyType;
use App\Models\Employee;
use App\Models\Survey;
use App\Models\SurveySection;
use App\Models\Team;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        $equipos = collect(['Producto', 'Ventas', 'Soporte', 'Marketing'])
            ->map(fn (string $nombre) => Team::create(['name' => $nombre]));

        $miembros = [
            ['Carlos Gómez', 'Juan Pérez', 'Pedro Ruiz'],
            ['Ana Fernández', 'Luis Torres', 'Sofía Ramos'],
            ['Diego Molina', 'Lucía Vargas', 'Marcos Díaz'],
            ['Elena Castro', 'Iván Rojas', 'Paula Mora'],
        ];

        foreach ($equipos as $index => $equipo) {
            $nombres = $miembros[$index];
            $superiorNombre = array_shift($nombres);

            $superior = Employee::create([
                'name' => $superiorNombre,
                'email' => str($superiorNombre)->slug('.').'@test.com',
                'team_id' => $equipo->id,
            ]);

            foreach ($nombres as $nombre) {
                Employee::create([
                    'name' => $nombre,
                    'email' => str($nombre)->slug('.').'@test.com',
                    'team_id' => $equipo->id,
                    'superior_id' => $superior->id,
                ]);
            }
        }

        $equipoEvaluado = $equipos->first();
        $equiposEvaluadores = $equipos->slice(1);

        $survey = Survey::create([
            'title' => 'Encuesta 360 de Colaboración',
            'type' => SurveyType::TeamsToTeam,
            'team_id' => $equipoEvaluado->id,
            'description' => "En esta ocasión queremos conocer tu experiencia de colaboración con la persona indicada.\n\nTu opinión nos ayudará a comprender mejor cómo colaboramos, identificar fortalezas y descubrir oportunidades para seguir evolucionando juntos.",
            'instructions' => "¿Cómo responder?\nValora cada aspecto utilizando una escala del 1 (mínimo) al 10 (máximo).\n\n1-3: Muy por debajo de lo esperado.\n4-6: Adecuado, con oportunidades de mejora.\n7-8: Buen desempeño.\n9-10: Desempeño excelente.\n\nAlgunas recomendaciones:\nResponde con la mayor sinceridad posible.\nBasa tus respuestas en experiencias y situaciones concretas vividas durante los últimos meses.\nUtiliza toda la escala cuando sea necesario; no todas las valoraciones tienen por qué ser iguales.\n\nTu feedback es confidencial y contribuirá a seguir fortaleciendo la colaboración, impulsar el aprendizaje y convertir cada experiencia en una oportunidad de mejora.",
            'start_message' => 'Gracias por dedicar unos minutos a compartir tu experiencia.',
            'end_message' => 'Gracias por completar la evaluación. Tu respuesta fue registrada correctamente.',
        ]);

        $survey->evaluatorTeams()->sync($equiposEvaluadores->pluck('id'));

        $colaboracion = SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboración', 'order' => 1]);

        $colaboracionPreguntas = [
            'Mantiene una comunicación clara, completa, coherente y oportuna contigo y/o con tu área.',
            'Coordina de forma eficaz su trabajo con tu área.',
            'Traslada las necesidades del proyecto con suficiente antelación y facilita una adecuada planificación del trabajo.',
            'La información, briefings e instrucciones que proporciona son claros y suficientes para realizar correctamente el trabajo.',
            'Mantiene una actitud colaborativa y facilita el trabajo conjunto.',
            'Gestiona adecuadamente las incidencias, prioriza cuando es necesario y facilita la búsqueda de soluciones.',
            'Se anticipa a posibles riesgos o necesidades y propone mejoras cuando identifica oportunidades.',
            'Gestiona los proyectos con una visión global teniendo en cuenta el impacto de sus decisiones.',
            'Trabaja de forma coordinada y cumple los compromisos adquiridos.',
            'En conjunto, su forma de trabajar aporta valor al desarrollo de los proyectos y a la colaboración contigo y/o con tu área.',
        ];

        foreach ($colaboracionPreguntas as $index => $titulo) {
            $colaboracion->questions()->create([
                'title' => $titulo,
                'type' => QuestionType::Rating10,
                'order' => $index + 1,
                'is_required' => true,
            ]);
        }

        $comentarios = SurveySection::create(['survey_id' => $survey->id, 'title' => 'Comentarios', 'order' => 2]);

        $comentarios->questions()->create([
            'title' => '¿Qué destacarías especialmente de su forma de trabajar?',
            'type' => QuestionType::LongText,
            'order' => 1,
            'is_required' => false,
        ]);

        $comentarios->questions()->create([
            'title' => '¿Qué aspecto consideras que tendría mayor impacto si mejorara?',
            'type' => QuestionType::LongText,
            'order' => 2,
            'is_required' => false,
        ]);

        $nps = SurveySection::create(['survey_id' => $survey->id, 'title' => 'NPS', 'order' => 3]);

        $nps->questions()->create([
            'title' => 'Si mañana comenzara un nuevo proyecto transversal, ¿qué probabilidad habría de que eligieras trabajar con esta persona?',
            'type' => QuestionType::Nps,
            'order' => 1,
            'is_required' => true,
        ]);
    }
}
