<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->json('gis_data')->nullable()->after('coordinates_lng');
            $table->json('virtual_tour_images')->nullable()->after('images');
            $table->boolean('has_virtual_tour')->default(false)->after('virtual_tour_images');
            $table->json('tour_hotspots')->nullable()->after('has_virtual_tour');
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['gis_data', 'virtual_tour_images', 'has_virtual_tour', 'tour_hotspots']);
        });
    }
};