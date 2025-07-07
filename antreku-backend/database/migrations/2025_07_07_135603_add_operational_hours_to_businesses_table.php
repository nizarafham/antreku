<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->time('open_time')->nullable()->after('address');
            $table->time('close_time')->nullable()->after('open_time');
            $table->integer('slot_duration_minutes')->default(30)->after('close_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['open_time', 'close_time', 'slot_duration_minutes']);
        });
    }
};
