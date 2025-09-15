<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->index();
            $table->string('priority')->index();
            $table->date('due_date')->nullable()->index();
            $table->foreignIdFor(User::class, 'assignee_id')->nullable()->constrained();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedBigInteger('version')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
