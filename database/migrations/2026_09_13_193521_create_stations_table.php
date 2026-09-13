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
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('element_type');
            $table->string('technology');
            $table->string('address_id')->index();
            $table->string('classification')->nullable();
            $table->string('area_holder')->nullable();
            $table->string('infra_contract_type')->nullable();
            $table->string('infra_holder')->nullable();
            $table->string('infra_type')->nullable();
            $table->string('ev_type')->nullable();
            $table->string('ev_provider')->nullable();
            $table->text('observation')->nullable();
            $table->text('justification')->nullable();
            $table->string('street_type')->nullable();
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('regional')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('status')->nullable();
            $table->string('tower_type')->nullable();
            $table->string('aev_nominal')->nullable();
            $table->string('land_area')->nullable();
            $table->string('structure_height')->nullable();
            $table->string('external_id')->nullable();
            $table->string('site_id')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
