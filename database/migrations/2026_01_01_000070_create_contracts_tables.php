<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 80)->unique();
            $table->string('name');
            $table->string('type', 20)->default('general')->comment('ilj|maintenance|general');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('vendor')->nullable();
            $table->decimal('contract_value', 18, 2)->default(0);
            $table->decimal('used_value', 18, 2)->default(0);
            $table->string('currency', 10)->default('IDR');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active')->comment('active|expiring|expired|completed|terminated');
            $table->string('maintenance_type', 60)->nullable();
            $table->unsignedSmallInteger('maintenance_progress')->default(0);
            $table->text('remaining_work')->nullable();
            $table->unsignedSmallInteger('work_progress')->default(0);
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('year')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('name');
            $table->date('planned_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->string('status', 20)->default('pending')->comment('pending|reached|missed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_milestones');
        Schema::dropIfExists('contracts');
    }
};