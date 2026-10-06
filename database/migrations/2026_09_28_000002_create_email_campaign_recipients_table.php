<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_recipients', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('team_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->foreignUlid('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['campaign_id', 'status']);
        });
    }
};
