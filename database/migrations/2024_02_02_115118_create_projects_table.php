<?php

use App\Enums\ProjectPriority;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('author_id')->nullable()->references('id')->on('users');
            $table->string('key', 20)->index();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('archived')->default(false);
            $table->enum('priority', ProjectPriority::values())->default(ProjectPriority::Medium->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
