<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->json('types')->nullable()->after('type');
        });

        // Migrate existing type data to types array
        DB::table('properties')->whereNotNull('type')->update([
            'types' => DB::raw("JSON_ARRAY(type)")
        ]);
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('types');
        });
    }
};
