<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_registers', function (Blueprint $table) {
            $table->id();
            $table->string('risk_code', 50)->unique();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 40)->default('operational');
            $table->unsignedTinyInteger('likelihood')->default(1)->comment('1-5');
            $table->unsignedTinyInteger('impact')->default(1)->comment('1-5');
            $table->unsignedTinyInteger('risk_score')->default(1);
            $table->string('risk_level', 20)->default('low')->comment('low|medium|high|critical');
            $table->text('mitigation')->nullable();
            $table->text('contingency')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('identified');
            $table->date('due_date')->nullable();
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->text('evidence')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_registers');
    }
};