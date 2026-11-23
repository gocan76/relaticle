<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->string('rea', 50)->nullable()->after('partita_iva');
            $table->string('pec', 255)->nullable()->after('email');
            $table->string('logo', 255)->nullable()->after('pec');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropColumn(['rea', 'pec', 'logo']);
        });
    }
};
