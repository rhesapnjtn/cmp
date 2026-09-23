<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 80)->nullable();
            $table->date('target_date')->nullable();
            $table->date('realized_date')->nullable();
            $table->string('status', 20)->default('plan')->comment('plan|done|cancelled');
            $table->unsignedSmallInteger('progress')->default(0);
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('evidence')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plan_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('name');
            $table->date('target_date')->nullable();
            $table->string('status', 20)->default('pending')->comment('pending|in_progress|done');
            $table->unsignedSmallInteger('progress')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_tasks');
        Schema::dropIfExists('plans');
    }
};