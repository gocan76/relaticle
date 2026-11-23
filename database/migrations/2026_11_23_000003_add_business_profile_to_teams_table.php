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
            $table->string('partita_iva', 20)->nullable()->after('name');
            $table->string('address', 255)->nullable()->after('partita_iva');
            $table->string('city', 255)->nullable()->after('address');
            $table->string('postal_code', 10)->nullable()->after('city');
            $table->string('province', 2)->nullable()->after('postal_code');
            $table->string('country', 2)->nullable()->after('province');
            $table->string('phone', 30)->nullable()->after('country');
            $table->string('email', 255)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table): void {
            $table->dropColumn([
                'partita_iva',
                'address',
                'city',
                'postal_code',
                'province',
                'country',
                'phone',
                'email',
            ]);
        });
    }
};
