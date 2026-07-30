<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'evaluation_answers',
            'evaluations',
            'evaluation_sessions',
            'survey_evaluator_team',
            'survey_questions',
            'survey_sections',
            'surveys',
            'employee_relations',
            'employees',
            'teams',
        ] as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->call(SurveySeeder::class);
    }
}
