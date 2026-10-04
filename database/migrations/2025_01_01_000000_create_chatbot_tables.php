<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void {
        Schema::table('users', fn(Blueprint $t) => $t->boolean('is_admin')->default(false));
        Schema::create('categories', function (Blueprint $t) {
            $t->id(); $t->string('name')->unique(); $t->timestamps();
        });
        Schema::create('documents', function (Blueprint $t) {
            $t->id(); $t->string('title'); $t->string('path');
            $t->string('status', 20)->default('processed'); $t->unsignedInteger('entries_count')->default(0); $t->timestamps();
        });
        Schema::create('knowledge_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title'); $t->text('question'); $t->text('answer'); $t->text('keywords')->nullable();
            $t->boolean('is_approved')->default(true)->index();
            $t->unsignedInteger('hits')->default(0)->index();
            $t->timestamps();
            if (DB::getDriverName() !== 'pgsql') $t->fullText(['title', 'question', 'keywords']);
        });
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE INDEX knowledge_entries_fts ON knowledge_entries USING GIN (to_tsvector('simple', title || ' ' || question || ' ' || coalesce(keywords, '')))");
        }
        Schema::create('synonyms', function (Blueprint $t) {
            $t->id(); $t->string('word')->unique(); $t->text('alternatives'); $t->timestamps();
        });
        Schema::create('chat_logs', function (Blueprint $t) {
            $t->id(); $t->text('question'); $t->string('normalized', 191)->index();
            $t->text('response')->nullable();
            $t->foreignId('entry_id')->nullable()->constrained('knowledge_entries')->nullOnDelete();
            $t->decimal('score', 6, 2)->default(0);
            $t->boolean('answered')->default(false); $t->boolean('resolved')->default(false);
            $t->timestamps(); $t->index(['answered', 'resolved']); $t->index('created_at');
        });
    }
    public function down(): void {
        Schema::dropIfExists('chat_logs'); Schema::dropIfExists('synonyms'); Schema::dropIfExists('knowledge_entries');
        Schema::dropIfExists('documents'); Schema::dropIfExists('categories');
        Schema::table('users', fn(Blueprint $t) => $t->dropColumn('is_admin'));
    }
};
