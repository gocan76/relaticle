<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->json('fornitura_items')->nullable()->after('fornitura_amount');

            $table->dropColumn('produzione_amount');
            $table->dropColumn('fornitura_amount');
        });
    }
};
