<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MiniDashboardRefactorDrivers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->unsignedBigInteger('mini_dashboard_id')->nullable();
            $table->foreign('mini_dashboard_id')->references('id')->on('mini_dashboards')->onDelete('set null');
        });
        Schema::table('resturants', function (Blueprint $table) {
            $table->unsignedBigInteger('mini_dashboard_id')->nullable();
            $table->foreign('mini_dashboard_id')->references('id')->on('mini_dashboards')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropForeign('mini_dashboard_id');
            $table->dropColumn('mini_dashboard_id');
        });
        Schema::table('resturants', function (Blueprint $table) {
            $table->dropForeign('mini_dashboard_id');
            $table->dropColumn('mini_dashboard_id');
        });
    }
}
