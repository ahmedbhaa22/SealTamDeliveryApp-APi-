<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Notrefactor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mini_dashboards', function (Blueprint $table) {
            $table->dateTime('last_requested_receipt')->nullable();
        });
        Schema::table('resturants', function (Blueprint $table) {
            $table->enum('shop_type', ['fixedPrice','Normal'])->default('Normal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            //
        });
    }
}
