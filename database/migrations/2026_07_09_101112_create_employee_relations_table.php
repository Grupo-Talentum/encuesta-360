<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type');
            $table->timestamps();

            $table->unique(['employee_id', 'related_employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_relations');
    }
};
