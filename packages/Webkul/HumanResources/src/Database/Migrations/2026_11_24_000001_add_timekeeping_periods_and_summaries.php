<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_timekeeping_batches', function (Blueprint $table) {
            $table->dateTime('first_mark_at')->nullable();
            $table->dateTime('last_mark_at')->nullable();
        });

        Schema::create('hr_timekeeping_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->restrictOnDelete();
            $table->date('work_date')->index();
            $table->unsignedInteger('worked_minutes')->nullable();
            $table->unsignedInteger('overtime_minutes')->nullable();
            $table->unsignedInteger('late_minutes')->nullable();
            $table->unsignedInteger('early_departure_minutes')->nullable();
            $table->unsignedInteger('credited_minutes')->nullable();
            $table->boolean('unexcused_absence')->nullable();
            $table->integer('balance_minutes')->nullable();
            $table->string('source', 32);
            $table->timestamps();
            $table->unique(['employee_id', 'work_date', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_timekeeping_summaries');
        Schema::table('hr_timekeeping_batches', function (Blueprint $table) {
            $table->dropColumn(['first_mark_at', 'last_mark_at']);
        });
    }
};
