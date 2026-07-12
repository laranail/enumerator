<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('enumerator.state_machine.table_name', 'enumerator_state_history');

        Schema::create($table, function (Blueprint $table): void {
            $table->id();
            $table->morphs('subject');
            $table->string('field');
            $table->string('from')->nullable();
            $table->string('to');
            $table->string('enum_class');
            $table->json('context')->nullable();
            $table->foreignId('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->timestamps();

            $table->index(['enum_class', 'to']);
            $table->index(['causer_type', 'causer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            (string) config('enumerator.state_machine.table_name', 'enumerator_state_history'),
        );
    }
};
