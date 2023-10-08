<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgressToSitesAndFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Добавляем поле "progress" к таблице "sites"
        Schema::table('sites', function (Blueprint $table) {
            $table->integer('progress')->default(0);
        });

        // Добавляем поле "progress" к таблице "files"
        Schema::table('files', function (Blueprint $table) {
            $table->integer('progress')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('progress');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('progress');
        });
    }
}
