<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MiniDashboardRefactor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mini_dashboards', function (Blueprint $table) {
            $table->string('monthly_cost');
            $table->string('earning_ratio');
            $table->integer('number_of_drivers');
        });

        Schema::table('admins', function (Blueprint $table) {
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
        Schema::table('mini_dashboards', function (Blueprint $table) {
            $table->dropColumn('number_of_drivers');
            $table->dropColumn('earning_ratio');
            $table->dropColumn('monthly_cost');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign('mini_dashboard_id');
            $table->dropColumn('mini_dashboard_id');
        });
    }
}
