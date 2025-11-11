<?php

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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('subject')->after('description');
            $table->string('topic')->nullable()->after('subject');
            $table->boolean('is_featured')->default(false)->after('is_published');
            $table->integer('pass_threshold')->default(60)->after('time_limit_minutes');
            $table->integer('max_attempts')->nullable()->after('pass_threshold');

            $table->index('subject');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['subject']);
            $table->dropIndex(['is_featured']);
            $table->dropColumn(['subject', 'topic', 'is_featured', 'pass_threshold', 'max_attempts']);
        });
    }
};
