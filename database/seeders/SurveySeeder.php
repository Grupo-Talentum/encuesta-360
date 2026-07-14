<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Models\Employee;
use App\Models\Survey;
use App\Models\SurveySection;
use App\Models\Team;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        if (Survey::where('title', 'Encuesta 360 de Colaboración')->exists()) {
            return;
        }

        $producto = Team::create(['name' => 'Producto']);

        $carlos = Employee::create(['name' => 'Carlos Gómez', 'email' => 'carlos.gomez@test.com', 'team_id' => $producto->id]);

        // Juan, Pedro y Ana reportan a Carlos: el sistema arma automaticamente
        // la relacion superior/inferior y los marca como compañeros entre sí.
        Employee::create(['name' => 'Juan Pérez', 'email' => 'juan.perez@test.com', 'team_id' => $producto->id, 'superior_id' => $carlos->id]);
        Employee::create(['name' => 'Pedro Ruiz', 'email' => 'pedro.ruiz@test.com', 'team_id' => $producto->id, 'superior_id' => $carlos->id]);
        Employee::create(['name' => 'Ana Fernández', 'email' => 'ana.fernandez@test.com', 'team_id' => $producto->id, 'superior_id' => $carlos->id]);

        $survey = Survey::create([
            'title' => 'Encuesta 360 de Colaboración',
            'description' => 'Evaluación de colaboración entre áreas para identificar fortalezas y oportunidades de mejora.',
            'instructions' => "Objetivo: evaluar la colaboración de la persona indicada con tu área durante el último periodo.\n\nEscala: cada pregunta se responde del 1 (mínimo) al 10 (máximo).\n\n1-3 indica un desempeño muy por debajo de lo esperado. 4-6 indica un desempeño aceptable con margen de mejora. 7-8 indica buen desempeño. 9-10 indica un desempeño excelente.\n\nRecomendaciones: respondé con la mayor sinceridad posible, pensando en hechos concretos de los últimos meses.\n\nNota: usá toda la escala disponible, evitá concentrar todas las respuestas en los mismos valores.\n\n¡Gracias por tu tiempo y tu honestidad!",
            'start_message' => 'A continuación vas a evaluar a la persona indicada. La encuesta toma unos 5 minutos.',
            'end_message' => 'Gracias por completar la evaluación. Tu respuesta fue registrada correctamente.',
        ]);

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
