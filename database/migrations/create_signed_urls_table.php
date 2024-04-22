<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Masterei\Signer\Config;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Config::get('table_name'), function (Blueprint $table) {
            $table->id();
            $table->string('path')->index();
            $table->string('signature')->unique();
            $table->integer('expired_at')->nullable();
            $table->json('parameters');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('table_name'));
    }
};
