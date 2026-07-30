<?php

use App\Enums\SurveyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('type')->default(SurveyType::Global->value)->after('team_id');
        });

        DB::table('surveys')->whereNotNull('team_id')->update(['type' => SurveyType::SingleTeam->value]);
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
