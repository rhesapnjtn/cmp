<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_items', function (Blueprint $table) {
            $table->id();
            $table->string('requirement');
            $table->string('regulation', 160)->nullable();
            $table->string('category', 40)->default('regulatory')->comment('legal|regulatory|internal|audit');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('deadline')->nullable();
            $table->string('status', 20)->default('in_progress')->comment('compliant|partial|non_compliant|in_progress|na');
            $table->unsignedSmallInteger('progress')->default(0);
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('compliance_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_item_id')->constrained('compliance_items')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_evidence');
        Schema::dropIfExists('compliance_items');
    }
};