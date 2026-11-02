<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignUlid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUlid('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->string('number');
            $table->string('status')->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();

            $table->decimal('fornitura_amount', 15, 2)->default(0);
            $table->decimal('produzione_amount', 15, 2)->default(0);
            $table->decimal('installazione_amount', 15, 2)->default(0);
            $table->decimal('trasferta_amount', 15, 2)->default(0);
            $table->decimal('trasporto_amount', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['team_id', 'number']);
            $table->index('status');
            $table->index('company_id');
        });
    }
};
