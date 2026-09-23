<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hsse_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level')->comment('1|2|3');
            $table->string('title');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->unsignedTinyInteger('quarter');
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedSmallInteger('target_count')->default(1);
            $table->date('planned_date')->nullable();
            $table->date('realized_date')->nullable();
            $table->string('status', 20)->default('scheduled')->comment('scheduled|done|missed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hsse_meetings');
    }
};