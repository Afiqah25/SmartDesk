
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Customer information
            $table->string('name', 100);
            $table->string('email');
            $table->string('subject');
            $table->text('message');

            // Ticket workflow
            $table->string('status')->default('new');
            $table->string('ai_status')->default('pending');

            // AI classification
            $table->string('category')->nullable();
            $table->string('priority')->nullable();
            $table->string('sentiment')->nullable();
            $table->string('summary', 200)->nullable();
            $table->string('language', 10)->nullable();

            // Human override tracking
            $table->string('category_source')->default('ai');
            $table->string('priority_source')->default('ai');

            // Agent reply
            $table->text('reply')->nullable();

            $table->timestamps();

            // Dashboard query optimization
            $table->index(['status', 'created_at']);
            $table->index('category');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
